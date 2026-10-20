<?php

namespace App\Http\Middleware;

use App\Services\ControlAcceso;
use Closure;
use Illuminate\Http\Request;

class ClienteAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('cliente_id')) {
            return redirect()->route('tienda.login');
        }
        if (ControlAcceso::expirada(ControlAcceso::CLIENTE)) {
            return redirect()->route('tienda.login')->with('err', 'Tu sesión se cerró por inactividad. Vuelve a iniciar sesión.');
        }
        return $next($request);
    }
}
