<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            $status = 500;
            $detalles = [];
            $mensaje = 'Error interno del servidor';

            if ($e instanceof ValidationException) {
                $status = 400;
                $detalles = $e->errors();
                $mensaje = 'Datos de entrada invalidos';
            } elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $mensaje = $e->getMessage();
            }

            $codigos = [
                400 => 'ENTRADA_INVALIDA',
                401 => 'NO_AUTENTICADO',
                403 => 'ROL_INSUFICIENTE',
                404 => 'NO_ENCONTRADO',
                409 => 'CONFLICTO',
                422 => 'REGLA_NEGOCIO',
                500 => 'ERROR_INTERNO',
                503 => 'SERVICIO_NO_DISPONIBLE'
            ];

            return response()->json([
                'codigo' => $codigos[$status] ?? 'ERROR_HTTP',
                'mensaje' => $mensaje,
                'detalles' => $detalles,
                'timestamp' => now()->toIso8601String(),
                'ruta' => '/'.$request->path()
            ], $status);
        });
    })->create();
