<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        // Admin siempre tiene acceso a todo
        if ($request->user()->role === 'admin') {
            return $next($request);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json(['message' => 'No tienes permisos para acceder a este recurso.'], 403);
        }

        return $next($request);
    }
}
