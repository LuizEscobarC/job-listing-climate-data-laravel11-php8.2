<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoint para monitoramento
Route::get('/health', [HealthController::class, 'index']);

// Rota de teste ping-pong
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/test', function () {
    return response()->json(['status' => 'API is working!']);
});

Route::get('/health-redis', function () {
    try {
        $redis = \Illuminate\Support\Facades\Redis::connection();
        
        $testKey = 'test_key_' . time();
        $testValue = 'Redis está funcionando! ' . now();
        
        $redis->set($testKey, $testValue);
        $retrieved = $redis->get($testKey);
        $redis->del($testKey);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Redis está funcionando corretamente',
            'test_value' => $retrieved,
            'client' => config('database.redis.client'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erro ao conectar com Redis',
            'error' => $e->getMessage(),
            'client' => config('database.redis.client'),
        ], 500);
    }
});

// Auth Routes
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);
    
    // Users
    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
    Route::patch('/users/{id}/toggle-status', [\App\Http\Controllers\Api\UserController::class, 'toggleStatus']);
    Route::patch('/users/{id}/restore', [\App\Http\Controllers\Api\UserController::class, 'restore']);
    // BULKS
    Route::delete('/users/bulk-delete', [\App\Http\Controllers\Api\UserController::class, 'bulkDelete']);
    Route::patch('/users/bulk-restore', [\App\Http\Controllers\Api\UserController::class, 'bulkRestore']);
    Route::patch('/users/bulk-toggle-status', [\App\Http\Controllers\Api\UserController::class, 'bulkToggleStatus']);

    // Job Listings
    Route::apiResource('job-listings', \App\Http\Controllers\Api\JobListingController::class);
    Route::patch('/job-listings/{id}/toggle-status', [\App\Http\Controllers\Api\JobListingController::class, 'toggleStatus']);
    // BULKS
    Route::delete('/job-listings/bulk-delete', [\App\Http\Controllers\Api\JobListingController::class, 'bulkDelete']);
    Route::patch('/job-listings/bulk-toggle-status', [\App\Http\Controllers\Api\JobListingController::class, 'bulkToggleStatus']);

    // Job Applications
    Route::apiResource('job-applications', \App\Http\Controllers\Api\JobApplicationController::class);
    Route::patch('/job-applications/{id}/withdraw', [\App\Http\Controllers\Api\JobApplicationController::class, 'withdraw']);
    /// BULKS
    Route::delete('/job-applications/bulk-delete', [\App\Http\Controllers\Api\JobApplicationController::class, 'bulkDelete']);
    Route::patch('/job-applications/bulk-update-status', [\App\Http\Controllers\Api\JobApplicationController::class, 'bulkUpdateStatus']);

    // Climate Data
    Route::get('/climate-data/analysis', [\App\Http\Controllers\Api\ClimateDataController::class, 'analysis']);
    Route::delete('/climate-data/bulk-delete', [\App\Http\Controllers\Api\ClimateDataController::class, 'bulkDelete']);
});

// Public job listings 
Route::get('/public/job-listings', [\App\Http\Controllers\Api\JobListingController::class, 'index']);
Route::get('/public/job-listings/{id}', [\App\Http\Controllers\Api\JobListingController::class, 'show']);
