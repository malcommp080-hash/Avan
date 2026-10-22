<?php

namespace App\Http\Controllers\Api;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteController extends ApiController
{
    private function reglas(bool $nuevo): array
    {
        return [
            'nombre'     => 'required|string|max:20',
            'ap_paterno' => 'required|string|max:20',
            'telefono'   => 'required|string|max:10',
            'contrasena' => [$nuevo ? 'required' : 'nullable', 'string', 'min:4', 'max:100'],
            'direccion'  => 'required|string|max:100',
            'cp'         => 'required|string|max:5',
        ];
    }

    public function index()
    {
        return response()->json(['clientes' => Cliente::orderByDesc('Id_cliente')->get()->map(fn ($c) => [
            'id' => $c->Id_cliente, 'nombre' => $c->Nombre, 'ap_paterno' => $c->Ap_paterno,
            'telefono' => $c->Telefono, 'direccion' => $c->Direccion, 'cp' => $c->CP,
        ])]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request, $this->reglas(true));
        Cliente::create([
            'Nombre' => $d['nombre'], 'Ap_paterno' => $d['ap_paterno'], 'Telefono' => $d['telefono'],
            'Contrasena' => Hash::make($d['contrasena']), 'Direccion' => $d['direccion'], 'CP' => $d['cp'],
        ]);
        return $this->ok('Cliente guardado correctamente.', [], 201);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $d = $this->validar($request, $this->reglas(false));
        $cliente->fill([
            'Nombre' => $d['nombre'], 'Ap_paterno' => $d['ap_paterno'], 'Telefono' => $d['telefono'],
            'Direccion' => $d['direccion'], 'CP' => $d['cp'],
        ]);
        if (!empty($d['contrasena'])) {
            $cliente->Contrasena = Hash::make($d['contrasena']);
        }
        $cliente->save();
        return $this->ok('Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        return $this->borrarJson(fn () => $cliente->delete(), 'Cliente eliminado.');
    }
}
