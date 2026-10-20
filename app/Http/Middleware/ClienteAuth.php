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
            return redirect()->guest(route('tienda.login'))->with('err', 'Inicia sesión o crea una cuenta para continuar.');
        }
        if (ControlAcceso::expirada(ControlAcceso::CLIENTE)) {
            return redirect()->route('tienda.login')->with('err', 'Tu sesión se cerró por inactividad. Vuelve a iniciar sesión.');
        }
        return $next($request);
    }
}
