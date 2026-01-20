<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Verifica el rol del usuario autenticado (permite varios roles).
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        if (!auth()->check()) {
            abort(403, 'Acceso denegado.');
        }

        // Separa varios roles con "|", por ejemplo: admin|secretaria
        $rolesArray = explode('|', $roles);

        if (!in_array(auth()->user()->role, $rolesArray)) {
            abort(403, 'Acceso denegado.');
        }

        return $next($request);
    }
}
