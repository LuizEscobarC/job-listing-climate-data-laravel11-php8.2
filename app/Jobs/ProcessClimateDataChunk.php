<?php

namespace App\Jobs;

use App\Models\ClimateData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProcessClimateDataChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    protected array $dataChunk;
    protected string $source;

    public function __construct(array $dataChunk, string $source = 'csv_import')
    {
        $this->dataChunk = $dataChunk;
        $this->source = $source;
    }

    public function handle(): void
    {
        $processedCount = 0;
        $importedAt = Carbon::now();

        try {
            $dataToInsert = [];

            foreach ($this->dataChunk as $row) {
                if ($this->isValidRow($row)) {
                    $dataToInsert[] = [
                        'recorded_at' => Carbon::parse($row['data']),
                        'temperature' => (float) $row['temperatura'],
                        'source' => $this->source,
                        'imported_at' => $importedAt,
                        'created_at' => $importedAt,
                        'updated_at' => $importedAt,
                    ];
                    $processedCount++;
                }
            }

            // Inserção em batch para melhor performance
            if (!empty($dataToInsert)) {
                ClimateData::insert($dataToInsert);
                
                $this->invalidateClimateDataCache($this->source);
            }

            Log::info("Climate data chunk processed successfully", [
                'processed_count' => $processedCount,
                'chunk_size' => count($this->dataChunk),
                'source' => $this->source
            ]);

        } catch (\Exception $e) {
            Log::error("Error processing climate data chunk", [
                'error' => $e->getMessage(),
                'chunk_size' => count($this->dataChunk),
                'source' => $this->source
            ]);

            throw $e;
        }
    }
    
    /**
     * Invalidar cache para dados climáticos inseridos em lote
     */
    private function invalidateClimateDataCache(string $source): void
    {
        $tags = [
            'climate_data',
            'climate_analysis',
            "climate_source:{$source}",
        ];

        Cache::tags($tags)->flush();
        
        Log::info('Cache invalidated for ClimateData bulk insert via Job', [
            'source' => $source,
            'invalidated_tags' => $tags
        ]);
    }
    
    private function isValidRow(array $row): bool
    {
        return isset($row['data']) && 
               isset($row['temperatura']) && 
               !empty($row['data']) && 
               is_numeric($row['temperatura']);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Climate data chunk job failed permanently", [
            'error' => $exception->getMessage(),
            'chunk_size' => count($this->dataChunk),
            'source' => $this->source
        ]);
    }
}
