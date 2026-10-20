<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Notificacion;
use App\Services\ControlAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('htmla.login');
    }

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
            return back()->withInput($request->only('telefono'))->withErrors(['telefono' => ControlAcceso::MSG_BLOQUEO]);
        }

        $empleado = Empleado::where('Telefono', $telefono)->first();

        if (!$empleado || !Hash::check($datos['contrasena'], $empleado->Contrasena)) {
            $bloqueada = ControlAcceso::fallo(ControlAcceso::ADMIN, $telefono, $empleado?->Id_empleado);
            return back()->withInput($request->only('telefono'))->withErrors([
                'telefono' => $bloqueada ? ControlAcceso::MSG_BLOQUEO : 'Número de teléfono o contraseña incorrectos.',
            ]);
        }

        $request->session()->regenerate();
        session([
            'admin_id'     => $empleado->Id_empleado,
            'admin_nombre' => $empleado->Nombre . ' ' . $empleado->Ap_paterno,
            'admin_rol'    => $empleado->rol,
        ]);
        ControlAcceso::exito(ControlAcceso::ADMIN, $telefono, $empleado->Id_empleado);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        ControlAcceso::cerrar(ControlAcceso::ADMIN);
        return redirect()->route('admin.login');
    }

    /* ---------- RF001.4: ¿Olvidó su contraseña? ---------- */

    public function showRecuperar()
    {
        return view('htmla.recuperar');
    }

    /**
     * Los empleados no tienen correo, así que la contraseña la restablece un administrador
     * desde Usuarios. Aquí sólo se le avisa con una notificación. La respuesta es la misma
     * exista o no el teléfono, para no revelar qué números están registrados.
     */
    public function recuperar(Request $request)
    {
        $d = $this->validar($request, ['telefono' => 'required|string|max:50']);

        $empleado = Empleado::where('Telefono', trim($d['telefono']))->first();
        if ($empleado) {
            Notificacion::aRoles(['admin'], 'RECUPERAR_PASSWORD',
                "{$empleado->Nombre} {$empleado->Ap_paterno} (tel. {$empleado->Telefono}) solicitó restablecer su contraseña.",
                route('admin.usuarios.edit', $empleado->Id_empleado));
        }

        return redirect()->route('admin.recuperar')->with('ok',
            'Si el teléfono está registrado, se avisó al administrador para que restablezca tu contraseña. Comunícate con él para recibir la nueva.');
    }
}
