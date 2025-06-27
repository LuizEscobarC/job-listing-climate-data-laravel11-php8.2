<?php

namespace App\Console\Commands;

use App\Jobs\ProcessClimateDataChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ImportClimateDataCommand extends Command
{
    protected $signature = 'climate:import 
                            {file : O arquivo CSV para importar}
                            {--chunk-size=1000 : Tamanho do chunk para processamento}
                            {--queue=default : Nome da fila para processamento}
                            {--source=csv_import : Fonte dos dados}';

    protected $description = 'Importa dados climáticos de um arquivo CSV de forma assíncrona';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $chunkSize = (int) $this->option('chunk-size');
        $queue = $this->option('queue');
        $source = $this->option('source');

        if (!file_exists($filePath)) {
            $this->error(__('messages.file_not_found', ['file' => $filePath]));
            return self::FAILURE;
        }
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'csv') {
            $this->error(__('messages.invalid_file_format'));
            return self::FAILURE;
        }

        $this->info(__('messages.starting_import', ['file' => basename($filePath)]));

        try {
            $totalRows = $this->processCSVFile($filePath, $chunkSize, $queue, $source);
            
            $this->info(__('messages.import_completed', [
                'rows' => $totalRows,
                'chunks' => ceil($totalRows / $chunkSize)
            ]));

            $this->info(__('messages.check_queue_status', ['queue' => $queue]));

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error(__('messages.import_failed', ['error' => $e->getMessage()]));
            Log::error('Climate data import failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }

    /**
     * Processa o arquivo CSV e cria jobs em chunks
     */
    private function processCSVFile(string $filePath, int $chunkSize, string $queue, string $source): int
    {
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Lê o cabeçalho
        
        // Validar cabeçalho
        if (!$this->isValidHeader($header)) {
            fclose($file);
            throw new \InvalidArgumentException(__('messages.invalid_csv_header'));
        }

        $chunk = [];
        $totalRows = 0;
        $chunkCount = 0;

        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat('verbose');

        while (($row = fgetcsv($file)) !== false) {
            if (empty($row) || count($row) < 2) {
                continue; // Pular linhas vazias ou inválidas
            }

            // Mapear dados do CSV
            $chunk[] = [
                'data' => $row[0],
                'temperatura' => $row[1]
            ];

            $totalRows++;

            // Quando o chunk estiver cheio, despachar job
            if (count($chunk) >= $chunkSize) {
                $this->dispatchChunk($chunk, $queue, $source, ++$chunkCount);
                $chunk = [];
                $progressBar->advance($chunkSize);
            }
        }

        // Processar o último chunk se houver dados restantes
        if (!empty($chunk)) {
            $this->dispatchChunk($chunk, $queue, $source, ++$chunkCount);
            $progressBar->advance(count($chunk));
        }

        $progressBar->finish();
        $this->newLine();

        fclose($file);
        return $totalRows;
    }

    /**
     * Valida se o cabeçalho do CSV está correto
     */
    private function isValidHeader(?array $header): bool
    {
        return $header !== null && 
               count($header) >= 2 && 
               in_array('data', $header) && 
               in_array('temperatura', $header);
    }

    /**
     * Despacha um chunk para processamento assíncrono
     */
    private function dispatchChunk(array $chunk, string $queue, string $source, int $chunkNumber): void
    {
        ProcessClimateDataChunk::dispatch($chunk, $source)
            ->onQueue($queue)
            ->delay(now()->addSeconds($chunkNumber * 2));

        Log::info('Climate data chunk dispatched', [
            'chunk_number' => $chunkNumber,
            'chunk_size' => count($chunk),
            'queue' => $queue,
            'source' => $source
        ]);
    }
}
