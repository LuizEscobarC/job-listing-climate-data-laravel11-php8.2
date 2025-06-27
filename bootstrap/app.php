<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Força JSON response para todas as rotas da API
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
        
        // Exclude specific routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'telescope/*',
            '/login',
            '/',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([
            // 
        ]);
        
        $exceptions->renderable(function (Throwable $e, $request) {
            return match (true) {
                $e instanceof \Illuminate\Auth\AuthenticationException => response()->json([
                    'message' => 'Não autenticado.',
                    'error' => 'unauthenticated',
                ], 401),

                $e instanceof \Illuminate\Auth\Access\AuthorizationException => response()->json([
                    'message' => 'Não autorizado.',
                    'error' => 'unauthorized',
                ], 403),

                $e instanceof AccessDeniedHttpException => response()->json([
                    'message' => 'Acesso negado.',
                    'error' => 'access_denied',
                ], 403),

                $e instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException => response()->json([
                    'message' => 'Não autorizado.',
                    'error' => 'unauthorized',
                ], 401),

                $e instanceof NotFoundHttpException => response()->json([
                    'message' => $e->getMessage() ?: 'Rota não encontrada.',
                    'error' => 'route_not_found',
                ], 404),

                $e instanceof ValidationException => (function () use ($e) {
                    $errors = $e->validator->errors()->all();
                    $countErrors = $e->validator->errors()->count();
                    $primaryMessage = array_shift($errors);
                    if ($countErrors > 1) {
                        $primaryMessage .= ' '.trans_choice(
                            key: 'validation.and_more_errors',
                            number: $countErrors,
                            replace: ['count' => $countErrors]
                        );
                    }
                    return response()->json([
                        'message' => $primaryMessage,
                        'errors' => $e->errors(),
                    ], $e->status);
                })(),

                $e instanceof ModelNotFoundException => response()->json([
                    'message' => 'Registro não encontrado.',
                    'error' => 'not_found',
                ], 404),

                $e instanceof \InvalidArgumentException => (function () use ($e) {
                    // Trata especificamente o erro "Route [login] not defined"
                    if (str_contains($e->getMessage(), 'Route [login] not defined')) {
                        return response()->json([
                            'message' => 'Acesso não autorizado. Esta é uma API, use autenticação via token.',
                            'error' => 'authentication_required',
                        ], 401);
                    }
                    
                    return response()->json([
                        'message' => $e->getMessage(),
                        'error' => 'invalid_argument',
                    ], 422);
                })(),

                $e instanceof \Illuminate\Database\QueryException => response()->json([
                    'message' => 'Erro de consulta ao banco de dados.',
                    'error' => 'query_exception',
                ], 500),

                default => app()->environment('local') || app()->environment('development')
                    ? response()->json([
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->map(fn ($trace) => \Illuminate\Support\Arr::except($trace, ['args']))->all(),
                    ], 500)
                    : response()->json([
                        'message' => 'Ocorreu um erro ao processar sua solicitação... tente novamente mais tarde.',
                    ], 500),
            };
        });

    })->create();