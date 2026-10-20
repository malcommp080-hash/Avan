<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAcceso;
use App\Models\Cliente;
use App\Models\Empleado;
use Illuminate\Http\Request;

/** RF014: bitácora de intentos de acceso (solo administradores). */
class BitacoraController extends Controller
{
    public const RESULTADOS = ['EXITO', 'FALLIDO', 'BLOQUEADO', 'LOGOUT', 'INACTIVIDAD'];

    public function index(Request $request)
    {
        $f = $request->validate([
            'tipo'      => 'nullable|in:ADMIN,CLIENTE',
            'resultado' => 'nullable|in:' . implode(',', self::RESULTADOS),
            'desde'     => 'nullable|date',
            'hasta'     => 'nullable|date',
        ]);

        $registros = BitacoraAcceso::query()
            ->when($f['tipo'] ?? null, fn ($q, $v) => $q->where('Tipo', $v))
            ->when($f['resultado'] ?? null, fn ($q, $v) => $q->where('Resultado', $v))
            ->when($f['desde'] ?? null, fn ($q, $v) => $q->where('Creado_en', '>=', $v . ' 00:00:00'))
            ->when($f['hasta'] ?? null, fn ($q, $v) => $q->where('Creado_en', '<=', $v . ' 23:59:59'))
            ->orderByDesc('Id_bitacora')
            ->paginate(50)->withQueryString();

        // Nombre de quien inició sesión, sin una consulta por renglón
        $ids = $registros->getCollection()->groupBy('Tipo')->map(fn ($g) => $g->pluck('Id_usuario')->filter()->unique());
        $nombres = [
            'ADMIN'   => Empleado::whereIn('Id_empleado', $ids['ADMIN'] ?? [])->get()->mapWithKeys(fn ($e) => [$e->Id_empleado => "{$e->Nombre} {$e->Ap_paterno}"]),
            'CLIENTE' => Cliente::whereIn('Id_cliente', $ids['CLIENTE'] ?? [])->get()->mapWithKeys(fn ($c) => [$c->Id_cliente => $c->nombre_completo]),
        ];

        $hoy = BitacoraAcceso::where('Creado_en', '>=', today());

        return view('htmla.bitacora', [
            'registros'  => $registros,
            'nombres'    => $nombres,
            'filtros'    => $f,
            'resultados' => self::RESULTADOS,
            'exitosHoy'  => (clone $hoy)->where('Resultado', 'EXITO')->count(),
            'fallidosHoy' => (clone $hoy)->where('Resultado', 'FALLIDO')->count(),
            'bloqueosHoy' => (clone $hoy)->where('Resultado', 'BLOQUEADO')->count(),
        ]);
    }
}
