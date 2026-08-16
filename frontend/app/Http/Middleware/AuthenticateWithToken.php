<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica si existe un JWT en sesión.
 * Reemplaza al middleware 'auth' de Breeze que requería base de datos.
 */
class AuthenticateWithToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('api_token')) {
            return redirect()->route('login')->with('status', 'Debes iniciar sesión para continuar.');
        }

        return $next($request);
    }
}
