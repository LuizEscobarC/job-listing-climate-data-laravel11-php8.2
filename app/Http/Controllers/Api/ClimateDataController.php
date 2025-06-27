<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClimateDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class ClimateDataController extends Controller
{
    protected ClimateDataService $climateDataService;

    public function __construct(ClimateDataService $climateDataService)
    {
        $this->climateDataService = $climateDataService;
    }

    /**
     * Analyze daily climate data with optional filters
     * @param Request $request
     * @return JsonResponse
     */
    public function analysis(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'source' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $cacheKey = 'climate_analysis_' . md5(json_encode($filters));
            $cacheTtl = config('cache.ttl.climate_analysis', 300);

            $data = Cache::tags(['climate_data', 'climate_analysis'])
                ->remember($cacheKey, $cacheTtl, function () use ($filters) {
                    return $this->climateDataService->getDailyAnalysis($filters);
                });

            return response()->json([
                'message' => __('messages.success'),
                'data' => $data
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('messages.error'),
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Delete multiple climate data records
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'ids' => 'nullable|array|min:1',
                'ids.*' => 'integer|exists:climate_data,id',
                'delete_all' => 'nullable|boolean'
            ]);

            if (!empty($validatedData['delete_all']) && $validatedData['delete_all'] === true) {
                $deletedCount = $this->climateDataService->deleteAll();
                $message = __('messages.all_data_deleted_successfully', [
                    'count' => $deletedCount,
                    'resource' => 'dados climáticos'
                ]);
            } elseif (!empty($validatedData['ids'])) {
                $deletedCount = $this->climateDataService->bulkDelete($validatedData['ids']);
                $message = __('messages.bulk_deleted_successfully', [
                    'count' => $deletedCount,
                    'resource' => 'dados climáticos'
                ]);
            } else {
                return response()->json([
                    'message' => __('messages.error'),
                    'errors' => ['ids' => 'Forneça uma lista de IDs para deletar ou use delete_all: true']
                ], 422);
            }

            // Invalidar cache relacionado
            Cache::tags(['climate_data', 'climate_analysis'])->flush();

            return response()->json([
                'message' => $message,
                'deleted_count' => $deletedCount
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('messages.error'),
                'errors' => $e->errors()
            ], 422);
        }
    }
}
