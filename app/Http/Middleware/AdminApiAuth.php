<?php

namespace App\Http\Middleware;

use App\Models\Empleado;
use App\Services\ControlAcceso;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Igual que AdminAuth pero para la app móvil: el empleado se identifica con
 * "Authorization: Bearer <token>" en lugar de la sesión del navegador.
 * Uso: 'admin.api' (cualquier empleado) o 'admin.api:admin' (solo ciertos roles).
 */
class AdminApiAuth
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $plano = $request->bearerToken();
        $token = $plano ? DB::table('empleado_token')->where('Token', hash('sha256', $plano))->first() : null;
        $empleado = $token ? Empleado::find($token->Id_empleado) : null;

        if (!$empleado) {
            return response()->json(['message' => 'Inicia sesión para continuar.'], 401);
        }

        // RF014: el token caduca tras el mismo tiempo de inactividad que el panel web
        if (now()->diffInMinutes($token->Ultimo_uso, true) > config('dav.inactividad_minutos')) {
            DB::table('empleado_token')->where('Id_token', $token->Id_token)->delete();
            ControlAcceso::registrar(ControlAcceso::ADMIN, '', $empleado->Id_empleado, 'INACTIVIDAD');
            return response()->json(['message' => 'Tu sesión se cerró por inactividad. Vuelve a iniciar sesión.'], 401);
        }
        DB::table('empleado_token')->where('Id_token', $token->Id_token)->update(['Ultimo_uso' => now()]);

        if ($roles && !in_array($empleado->rol, $roles, true)) {
            return response()->json(['message' => 'No tienes permiso para entrar a esa sección.'], 403);
        }

        $request->attributes->set('empleado', $empleado);
        $request->attributes->set('token_id', $token->Id_token);

        return $next($request);
    }
}
