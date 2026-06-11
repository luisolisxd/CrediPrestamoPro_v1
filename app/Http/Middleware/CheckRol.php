<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRol
{
    /**
     * Comprueba si el usuario tiene uno de los roles permitidos.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Si el rol del usuario está dentro de la lista de roles permitidos, lo dejamos pasar
        if (in_array($user->rol->nombre, $roles)) {
            return $next($request);
        }

        // Si no tiene permiso, bloqueamos el acceso
        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}