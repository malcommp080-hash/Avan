<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BitacoraController;
use App\Models\BitacoraAcceso;
use App\Models\Cliente;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Bitácora de accesos (RF014) y restauración de la BD para la app móvil; sólo administradores. */
class SistemaController extends ApiController
{
    public function bitacora(Request $request)
    {
        $f = $request->validate([
            'tipo'      => 'nullable|in:ADMIN,CLIENTE',
            'resultado' => 'nullable|in:' . implode(',', BitacoraController::RESULTADOS),
        ]);

        $registros = BitacoraAcceso::query()
            ->when($f['tipo'] ?? null, fn ($q, $v) => $q->where('Tipo', $v))
            ->when($f['resultado'] ?? null, fn ($q, $v) => $q->where('Resultado', $v))
            ->orderByDesc('Id_bitacora')->limit(100)->get();

        // Nombre de quien inició sesión, sin una consulta por renglón
        $ids = $registros->groupBy('Tipo')->map(fn ($g) => $g->pluck('Id_usuario')->filter()->unique());
        $nombres = [
            'ADMIN'   => Empleado::whereIn('Id_empleado', $ids['ADMIN'] ?? [])->get()->mapWithKeys(fn ($e) => [$e->Id_empleado => "{$e->Nombre} {$e->Ap_paterno}"]),
            'CLIENTE' => Cliente::whereIn('Id_cliente', $ids['CLIENTE'] ?? [])->get()->mapWithKeys(fn ($c) => [$c->Id_cliente => $c->nombre_completo]),
        ];

        $hoy = BitacoraAcceso::where('Creado_en', '>=', today());

        return response()->json([
            'exitosHoy'   => (clone $hoy)->where('Resultado', 'EXITO')->count(),
            'fallidosHoy' => (clone $hoy)->where('Resultado', 'FALLIDO')->count(),
            'bloqueosHoy' => (clone $hoy)->where('Resultado', 'BLOQUEADO')->count(),
            'resultados'  => BitacoraController::RESULTADOS,
            'registros'   => $registros->map(fn ($r) => [
                'id' => $r->Id_bitacora, 'tipo' => $r->Tipo, 'telefono' => $r->Telefono, 'resultado' => $r->Resultado,
                'nombre' => $nombres[$r->Tipo][$r->Id_usuario] ?? null, 'ip' => $r->Ip,
                'fecha' => $r->Creado_en?->format('d/m/Y H:i:s'),
            ]),
        ]);
    }
}
