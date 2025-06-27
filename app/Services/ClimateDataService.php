<?php

namespace App\Services;

use App\Models\ClimateData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClimateDataService
{
    public function getDailyAnalysis(array $filters = []): LengthAwarePaginator
    {
        $query = ClimateData::query();

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->forPeriod($filters['start_date'], $filters['end_date']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        // group by date
        $dailyStats = $query->select([
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('COUNT(*) as record_count'),
                DB::raw('AVG(temperature) as mean_temperature'),
                DB::raw('MIN(temperature) as min_temperature'),
                DB::raw('MAX(temperature) as max_temperature'),
                DB::raw('GROUP_CONCAT(temperature ORDER BY temperature) as temperatures')
            ])
            ->groupBy(DB::raw('DATE(recorded_at)'))
            ->orderBy('date', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $paginatedData = $dailyStats->paginate($perPage);

        // proccess the paginated data to calculate statistics
        $paginatedData->getCollection()->transform(function ($item) {
            $temperatures = array_map('floatval', explode(',', $item->temperatures));
            
            sort($temperatures);
            $count = count($temperatures);
            $median = $count > 0 ? (
                $count % 2 === 0 ? 
                ($temperatures[$count/2 - 1] + $temperatures[$count/2]) / 2 :
                $temperatures[intval($count/2)]
            ) : 0;

            $totalCount = count($temperatures);
            $above10 = count(array_filter($temperatures, fn($temp) => $temp > 10));
            $below10 = count(array_filter($temperatures, fn($temp) => $temp < -10));
            $between = count(array_filter($temperatures, fn($temp) => $temp >= -10 && $temp <= 10));

            unset($item->temperatures);

            return [
                'date' => $item->date,
                'record_count' => $item->record_count,
                'statistics' => [
                    'mean' => round($item->mean_temperature, 2),
                    'median' => round($median, 2),
                    'min' => $item->min_temperature,
                    'max' => $item->max_temperature,
                    'percentage_above_10' => $totalCount > 0 ? round(($above10 / $totalCount) * 100, 2) : 0,
                    'percentage_below_minus_10' => $totalCount > 0 ? round(($below10 / $totalCount) * 100, 2) : 0,
                    'percentage_between_minus_10_and_10' => $totalCount > 0 ? round(($between / $totalCount) * 100, 2) : 0,
                ]
            ];
        });

        return $paginatedData;
    }

    public function bulkDelete(array $ids): int
    {
        return ClimateData::whereIn('id', $ids)->delete();
    }

    public function deleteAll(): int
    {
        return ClimateData::query()->delete();
    }
}