<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/** Usuarios (empleados) del panel; sólo administradores. */
class EmpleadoController extends ApiController
{
    private function reglas(?Empleado $actual = null): array
    {
        return [
            'nombre'     => 'required|string|max:20',
            'ap_paterno' => 'required|string|max:20',
            'telefono'   => ['required', 'string', 'max:10',
                Rule::unique('empleado', 'Telefono')->ignore($actual?->Id_empleado, 'Id_empleado')],
            'direccion'  => 'required|string|max:40',
            'contrasena' => [$actual ? 'nullable' : 'required', 'string', 'min:6', 'max:100'],
            'rol'        => 'required|in:admin,vendedor,almacen,contador',
        ];
    }

    public function index()
    {
        return response()->json(['usuarios' => Empleado::orderByDesc('Id_empleado')->get()->map(fn ($e) => [
            'id' => $e->Id_empleado, 'nombre' => $e->Nombre, 'ap_paterno' => $e->Ap_paterno,
            'telefono' => $e->Telefono, 'direccion' => $e->Direccion, 'rol' => $e->rol,
        ])]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request, $this->reglas());
        Empleado::create([
            'Nombre' => $d['nombre'], 'Ap_paterno' => $d['ap_paterno'], 'Telefono' => $d['telefono'],
            'Direccion' => $d['direccion'], 'Contrasena' => Hash::make($d['contrasena']), 'rol' => $d['rol'],
        ]);
        return $this->ok('Empleado guardado correctamente.', [], 201);
    }

    public function update(Request $request, Empleado $usuario)
    {
        $d = $this->validar($request, $this->reglas($usuario));
        $usuario->fill([
            'Nombre' => $d['nombre'], 'Ap_paterno' => $d['ap_paterno'], 'Telefono' => $d['telefono'],
            'Direccion' => $d['direccion'], 'rol' => $d['rol'],
        ]);
        if (!empty($d['contrasena'])) {
            $usuario->Contrasena = Hash::make($d['contrasena']);
        }
        $usuario->save();
        return $this->ok('Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $usuario)
    {
        if ((int) $usuario->Id_empleado === $this->empleado()->Id_empleado) {
            return $this->error('No puedes eliminar tu propio usuario.', 409);
        }
        return $this->borrarJson(fn () => $usuario->delete(), 'Empleado eliminado.');
    }
}
