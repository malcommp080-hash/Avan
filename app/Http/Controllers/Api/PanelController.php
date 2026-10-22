<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ReporteVentasController;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\Prenda;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PanelController extends ApiController
{
    /** Resumen del menú principal: los mismos totales del dashboard web y lo que está pendiente. */
    public function dashboard()
    {
        $ultimas = Venta::reales()->with('cliente')->orderByDesc('Id_venta')->limit(5)->get()
            ->map(fn ($v) => [
                'id' => $v->Id_venta, 'fecha' => $v->fecha_texto, 'total' => (float) $v->Total,
                'cliente' => $v->cliente?->nombre_completo ?? '—', 'origen' => $v->Origen,
            ]);

        // Ventas por mes (últimos 6 meses, aunque no tengan ventas), igual que el dashboard web
        $porMes = Venta::reales()->get(['Fecha', 'Total'])
            ->groupBy(fn ($v) => $v->Fecha->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('Total'));
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->startOfMonth()->subMonths($i);
            $meses[] = ['etiqueta' => ucfirst($mes->translatedFormat('M Y')), 'total' => $porMes[$mes->format('Y-m')] ?? 0];
        }

        return response()->json([
            'meses'          => $meses,
            'ultimosClientes' => Cliente::orderByDesc('Id_cliente')->limit(5)->get()->map(fn ($c) => [
                'id' => $c->Id_cliente, 'nombre' => $c->nombre_completo, 'telefono' => $c->Telefono, 'direccion' => $c->Direccion,
            ]),
            'saludo'         => $this->saludo(),
            'totalClientes'  => Cliente::count(),
            'totalVentas'    => Venta::reales()->count(),
            'totalPrendas'   => Prenda::count(),
            'montoTotal'     => (float) Venta::reales()->sum('Total'),
            'pagosPendientes' => Pago::where('Estado', 'PENDIENTE')->whereNotNull('Comprobante')->count(),
            'cotizacionesPendientes' => Cotizacion::where('Estado', 'PENDIENTE')->count(),
            'noLeidas'       => Notificacion::para($this->empleado()->Id_empleado)->where('Leida', false)->count(),
            'ultimasVentas'  => $ultimas,
        ]);
    }

    /** RF007: ventas por semana y por mes. */
    public function reportes(Request $request)
    {
        $f = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], ['hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.', 'date' => 'Escribe una fecha válida.']);

        $hasta = isset($f['hasta']) ? Carbon::parse($f['hasta'])->endOfDay() : now()->endOfDay();
        $desde = isset($f['desde']) ? Carbon::parse($f['desde'])->startOfDay() : $hasta->copy()->subMonthsNoOverflow(5)->startOfMonth();
        if ($desde->diffInDays($hasta) > 366 * 3) {
            return $this->error('El rango no puede ser mayor a 3 años.');
        }

        $r = ReporteVentasController::calcular($desde, $hasta);
        $r['desde'] = $desde->toDateString();
        $r['hasta'] = $hasta->toDateString();

        return response()->json($r);
    }
}
