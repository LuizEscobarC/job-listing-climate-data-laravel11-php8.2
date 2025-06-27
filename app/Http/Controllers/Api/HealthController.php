<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Check API health status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $status = 'ok';
        $dbConnection = true;

        // Verificar conexão com o banco de dados
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbConnection = false;
            $status = 'error';
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbConnection ? 'ok' : 'error',
                'api' => 'ok',
            ],
            'environment' => app()->environment(),
            'version' => config('app.version', '1.0.0'),
        ]);
    }
}
