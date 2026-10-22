<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;

/** Base de la API móvil: mismas validaciones en español que el panel, respuestas JSON. */
abstract class ApiController extends Controller
{
    /** Empleado autenticado por el middleware admin.api. */
    protected function empleado(): Empleado
    {
        return request()->attributes->get('empleado');
    }

    protected function ok(string $mensaje, array $extra = [], int $status = 200)
    {
        return response()->json(['message' => $mensaje] + $extra, $status);
    }

    protected function error(string $mensaje, int $status = 422)
    {
        return response()->json(['message' => $mensaje], $status);
    }

    /** Borra; si hay registros relacionados (FK) avisa en vez de tronar. */
    protected function borrarJson(callable $accion, string $ok)
    {
        try {
            $accion();
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->error('No se puede eliminar: tiene registros relacionados en otras tablas.', 409);
        }
        return $this->ok($ok);
    }
}
