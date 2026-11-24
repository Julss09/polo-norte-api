<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $rol)
    {
        $token = $request->bearerToken();

        abort_if(!$token, 401, 'Token requerido');

        $claves = config('services.api_keys', []);
        $rolActual = null;

        foreach ($claves as $nombre => $clave) {
            if ($clave && hash_equals($clave, $token)) {
                $rolActual = $nombre;
                break;
            }
        }

        abort_if(!$rolActual, 401, 'Token invalido');
        abort_if($rolActual !== $rol, 403, 'Rol insuficiente');

        return $next($request);
    }
}
