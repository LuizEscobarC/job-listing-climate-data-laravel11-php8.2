<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "/home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/'; // Não será usado em API Only

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        // Configuração do limitador de taxa para API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        $this->routes(function () {
            // Rotas da API
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

        });
    }
}