<?php

namespace App\Http\Controllers\Api;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends ApiController
{
    private function reglas(): array
    {
        return [
            'nombre'     => 'required|string|max:20',
            'ap_paterno' => 'required|string|max:20',
            'telefono'   => 'required|string|max:10',
            'direccion'  => 'required|string|max:40',
        ];
    }

    private function datos(array $d): array
    {
        return ['Nombre' => $d['nombre'], 'Ap_paterno' => $d['ap_paterno'],
                'Telefono' => $d['telefono'], 'Direccion' => $d['direccion']];
    }

    public function index()
    {
        return response()->json(['proveedores' => Proveedor::orderByDesc('Id_proovedor')->get()->map(fn ($p) => [
            'id' => $p->Id_proovedor, 'nombre' => $p->Nombre, 'ap_paterno' => $p->Ap_paterno,
            'telefono' => $p->Telefono, 'direccion' => $p->Direccion,
        ])]);
    }

    public function store(Request $request)
    {
        Proveedor::create($this->datos($this->validar($request, $this->reglas())));
        return $this->ok('Proveedor guardado correctamente.', [], 201);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->datos($this->validar($request, $this->reglas())));
        return $this->ok('Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        return $this->borrarJson(fn () => $proveedor->delete(), 'Proveedor eliminado.');
    }
}
