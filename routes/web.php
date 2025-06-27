<?php

use Illuminate\Support\Facades\Route;

// Root route - API information
Route::get('/', function () {
    return response()->json([
        'message' => 'Estech API',
        'version' => '1.0.0',
        'status' => 'active',
        'api_endpoint' => url('/api'),
        'documentation' => url('/telescope'),
        'endpoints' => [
            'health' => '/api/health',
            'auth' => '/api/auth/*',
            'users' => '/api/users',
            'job-listings' => '/api/job-listings',
            'job-applications' => '/api/job-applications',
            'climate-data' => '/api/climate-data',
        ],
        'note' => 'This is an API-only application. Use /api/* endpoints.',
        'timestamp' => now()->toISOString(),
    ]);
});

// Rota de fallback para login (retorna JSON explicando que é uma API)
Route::get('/login', function () {
    return response()->json([
        'message' => 'Esta é uma API. Use autenticação via token Bearer.',
        'error' => 'authentication_required',
        'documentation' => url('/telescope'),
        'auth_endpoint' => url('/api/auth/login'),
    ], 401);
})->name('login');
