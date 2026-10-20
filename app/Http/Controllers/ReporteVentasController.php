<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RF007: ventas agrupadas por semana y por mes en un rango de fechas.
 * Sólo cuentan las ventas reales (mostrador y compras en línea con pago aprobado),
 * así que los totales coinciden con la suma de las ventas registradas.
 */
class ReporteVentasController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], ['hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.', 'date' => 'Escribe una fecha válida.']);

        // Por defecto: los últimos 6 meses completos hasta hoy
        $hasta = isset($f['hasta']) ? Carbon::parse($f['hasta'])->endOfDay() : now()->endOfDay();
        $desde = isset($f['desde']) ? Carbon::parse($f['desde'])->startOfDay() : $hasta->copy()->subMonthsNoOverflow(5)->startOfMonth();
        if ($desde->diffInDays($hasta) > 366 * 3) {
            return back()->withErrors(['desde' => 'El rango no puede ser mayor a 3 años.'])->withInput();
        }

        $ventas = Venta::reales()->whereBetween('Fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->get(['Id_venta', 'Fecha', 'Total', 'Total_piezas', 'Origen']);

        // Semanas de lunes a domingo; la primera y la última se recortan al rango
        $semanas = [];
        foreach (CarbonPeriod::create($desde->copy()->startOfWeek(Carbon::MONDAY), '1 week', $hasta) as $ini) {
            $semanas[$ini->format('o-W')] = [
                'etiqueta' => $ini->max($desde)->format('d/m') . '–' . $ini->copy()->endOfWeek(Carbon::SUNDAY)->min($hasta)->format('d/m'),
                'inicio' => $ini->max($desde)->toDateString(),
                'total' => 0.0, 'ventas' => 0, 'piezas' => 0,
            ];
        }
        $meses = [];
        foreach (CarbonPeriod::create($desde->copy()->startOfMonth(), '1 month', $hasta) as $m) {
            $meses[$m->format('Y-m')] = ['etiqueta' => ucfirst($m->translatedFormat('M Y')), 'total' => 0.0, 'ventas' => 0, 'piezas' => 0];
        }
        $sumar = function (array &$grupo, string $clave, Venta $v) {
            if (!isset($grupo[$clave])) return;
            $grupo[$clave]['total'] += (float) $v->Total;
            $grupo[$clave]['ventas']++;
            $grupo[$clave]['piezas'] += (int) $v->Total_piezas;
        };
        foreach ($ventas as $v) {
            $sumar($semanas, $v->Fecha->format('o-W'), $v);
            $sumar($meses, $v->Fecha->format('Y-m'), $v);
        }
        $redondear = fn (array $g) => array_values(array_map(fn ($x) => ['total' => round($x['total'], 2)] + $x, $g));
        $semanas = $redondear($semanas);
        $meses = $redondear($meses);

        // RF007.3: el total se calcula aparte, directo en SQL, y se compara con las gráficas
        $totalSql = (float) Venta::reales()->whereBetween('Fecha', [$desde->toDateString(), $hasta->toDateString()])->sum('Total');
        $total = round($ventas->sum('Total'), 2);
        $cuadra = abs(round(array_sum(array_column($semanas, 'total')), 2) - $totalSql) < 0.01
               && abs(round(array_sum(array_column($meses, 'total')), 2) - $totalSql) < 0.01;

        $top = DB::table('detalleventa as d')
            ->join('registroproductos as p', 'p.Id_producto', '=', 'd.Id_producto')
            ->whereIn('d.Id_venta', $ventas->pluck('Id_venta'))
            ->groupBy('p.Id_producto', 'p.Nombre')
            ->orderByDesc('importe')->limit(5)
            ->get(['p.Nombre', DB::raw('SUM(d.Cantidad) as piezas'), DB::raw('SUM(d.Subtotal) as importe')]);

        return view('htmla.reportes', [
            'desde'   => $desde, 'hasta' => $hasta,
            'semanas' => $semanas, 'meses' => $meses,
            'total'   => $total, 'totalSql' => $totalSql, 'cuadra' => $cuadra,
            'numVentas' => $ventas->count(),
            'piezas'  => (int) $ventas->sum('Total_piezas'),
            'promedio' => $ventas->count() ? $total / $ventas->count() : 0,
            'enLinea' => round($ventas->where('Origen', 'EN_LINEA')->sum('Total'), 2),
            'top'     => $top,
        ]);
    }
}
