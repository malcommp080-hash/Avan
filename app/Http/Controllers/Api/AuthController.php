<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use App\Services\ControlAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Inicio de sesión de la app móvil con las mismas reglas que el panel (RF001). */
class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $datos = $this->validar($request, [
            'telefono'   => 'required|string|max:50',
            'contrasena' => 'required|string',
        ]);
        $telefono = trim($datos['telefono']);

        // RF001.5: tras 5 intentos fallidos consecutivos la cuenta queda bloqueada 5 minutos
        if (ControlAcceso::bloqueada(ControlAcceso::ADMIN, $telefono)) {
            ControlAcceso::registrar(ControlAcceso::ADMIN, $telefono, null, 'BLOQUEADO');
            return $this->error(ControlAcceso::MSG_BLOQUEO, 423);
        }

        $empleado = Empleado::where('Telefono', $telefono)->first();

        if (!$empleado || !Hash::check($datos['contrasena'], $empleado->Contrasena)) {
            $bloqueada = ControlAcceso::fallo(ControlAcceso::ADMIN, $telefono, $empleado?->Id_empleado);
            return $this->error($bloqueada ? ControlAcceso::MSG_BLOQUEO : 'Número de teléfono o contraseña incorrectos.', $bloqueada ? 423 : 401);
        }

        ControlAcceso::exito(ControlAcceso::ADMIN, $telefono, $empleado->Id_empleado);

        // Sólo se guarda el hash; el token en claro lo conserva el teléfono
        $token = Str::random(60);
        DB::table('empleado_token')->insert([
            'Id_empleado' => $empleado->Id_empleado,
            'Token'       => hash('sha256', $token),
            'Dispositivo' => mb_substr((string) $request->userAgent(), 0, 255),
            'Creado_en'   => now(),
            'Ultimo_uso'  => now(),
        ]);

        return response()->json(['token' => $token, 'empleado' => $this->datos($empleado)]);
    }

    public function logout(Request $request)
    {
        DB::table('empleado_token')->where('Id_token', $request->attributes->get('token_id'))->delete();
        ControlAcceso::registrar(ControlAcceso::ADMIN, '', $this->empleado()->Id_empleado, 'LOGOUT');
        return $this->ok('Sesión cerrada.');
    }

    public function yo()
    {
        return response()->json(['empleado' => $this->datos($this->empleado())]);
    }

    private function datos(Empleado $e): array
    {
        return ['id' => $e->Id_empleado, 'nombre' => $e->Nombre . ' ' . $e->Ap_paterno, 'telefono' => $e->Telefono, 'rol' => $e->rol];
    }
}
