<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ControlAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteAuthController extends Controller
{


    public function showLogin()
    {
        if (session()->has('cliente_id')) {
            return redirect()->route('tienda.inicio');
        }
        return view('htmlv.login');
    }

    public function login(Request $request)
    {
        $d = $this->validar($request, ['telefono' => 'required|string|max:10', 'contrasena' => 'required|string']);
        $telefono = trim($d['telefono']);

        if (ControlAcceso::bloqueada(ControlAcceso::CLIENTE, $telefono)) {
            ControlAcceso::registrar(ControlAcceso::CLIENTE, $telefono, null, 'BLOQUEADO');
            return back()->withInput($request->only('telefono'))->with('err', ControlAcceso::MSG_BLOQUEO);
        }

        $cliente = Cliente::where('Telefono', $telefono)->first();
        if (!$cliente || !Hash::check($d['contrasena'], $cliente->Contrasena)) {
            $bloqueada = ControlAcceso::fallo(ControlAcceso::CLIENTE, $telefono, $cliente?->Id_cliente);
            return back()->withInput($request->only('telefono'))
                ->with('err', $bloqueada ? ControlAcceso::MSG_BLOQUEO : 'Número de teléfono o contraseña incorrectos.');
        }

        $request->session()->regenerate();
        session(['cliente_id' => $cliente->Id_cliente, 'cliente_nombre' => $cliente->nombre_completo]);
        ControlAcceso::exito(ControlAcceso::CLIENTE, $telefono, $cliente->Id_cliente);

        return redirect()->intended(route('tienda.inicio'));
    }

    public function logout(Request $request)
    {
        ControlAcceso::cerrar(ControlAcceso::CLIENTE);
        return redirect()->route('tienda.inicio');
    }

    /* ---------- Crear cuenta ---------- */

    public function showRegistro()
    {
        return view('htmlv.crearcu');
    }

    public function registro(Request $request)
    {
        $d = $request->validate([
            'nombre'     => 'required|string|max:20',
            'paterno'    => 'required|string|max:20',
            'telefono'   => ['required', 'regex:/^[0-9]{10}$/', 'unique:cliente,Telefono'],
            'contrasena' => 'required|string|min:6|max:100',
            'confirmar'  => 'required|same:contrasena',
            'direccion'  => 'required|string|max:100',
            'cp'         => ['required', 'regex:/^[0-9]{5}$/'],
        ], $this->mensajes + [
            'telefono.unique' => 'Este número ya cuenta con una cuenta.',
            'telefono.regex'  => 'El teléfono debe tener 10 dígitos.',
            'cp.regex'        => 'El código postal debe tener 5 dígitos.',
        ], $this->atributos + ['paterno' => 'apellido paterno']);

        Cliente::create([
            'Nombre' => $d['nombre'], 'Ap_paterno' => $d['paterno'], 'Telefono' => $d['telefono'],
            'Contrasena' => Hash::make($d['contrasena']), 'Direccion' => $d['direccion'], 'CP' => $d['cp'],
        ]);

        return redirect()->route('tienda.registro.exito');
    }

    public function registroExito()
    {
        return view('htmlv.crearcuex');
    }

    /* ---------- Recuperar contraseña (3 pasos) ---------- */

    public function recuperar()
    {
        return view('htmlv.recuperarcon');
    }

    public function recuperarBuscar(Request $request)
    {
        $d = $this->validar($request, ['telefono' => 'required|string|max:10', 'cp' => ['required', 'regex:/^[0-9]{5}$/']]);

        // Además del teléfono se pide el C.P. registrado; el mensaje no dice cuál de los dos falló
        $cliente = Cliente::where('Telefono', trim($d['telefono']))->where('CP', $d['cp'])->first();
        if (!$cliente) {
            ControlAcceso::registrar(ControlAcceso::CLIENTE, trim($d['telefono']), null, 'FALLIDO');
            return back()->withInput($request->only('telefono'))->with('err', 'No encontramos una cuenta con esos datos.');
        }

        session(['recuperar_id' => $cliente->Id_cliente]);
        return redirect()->route('tienda.recuperar.paso2');
    }

    public function recuperarPaso2()
    {
        $cliente = Cliente::find(session('recuperar_id'));
        if (!$cliente) {
            return redirect()->route('tienda.recuperar');
        }
        return view('htmlv.recuperarco2', ['cliente' => $cliente]);
    }

    public function recuperarGuardar(Request $request)
    {
        $cliente = Cliente::find(session('recuperar_id'));
        if (!$cliente) {
            return redirect()->route('tienda.recuperar');
        }

        $d = $this->validar($request, [
            'nueva'     => 'required|string|min:6|max:100',
            'confirmar' => 'required|same:nueva',
        ]);

        $cliente->update(['Contrasena' => Hash::make($d['nueva'])]);
        session()->forget('recuperar_id');

        return redirect()->route('tienda.recuperar.exito');
    }

    public function recuperarExito()
    {
        return view('htmlv.recuperarcon3');
    }
}
