<?php

namespace App\Http\Controllers\Api;

use App\Models\Cotizacion;
use Illuminate\Http\Request;

/** RF009: cotizaciones de los clientes. */
class CotizacionController extends ApiController
{
    public function index(Request $request)
    {
        $estado = $request->validate(['estado' => 'nullable|in:PENDIENTE,ATENDIDA'])['estado'] ?? 'PENDIENTE';

        $lista = Cotizacion::with(['cliente', 'atendio'])->withCount('detalles')
            ->where('Estado', $estado)->orderBy($estado === 'PENDIENTE' ? 'Id_cotizacion' : 'Atendida_en', $estado === 'PENDIENTE' ? 'asc' : 'desc')
            ->get()->map(fn ($c) => $this->resumen($c));

        return response()->json(['cotizaciones' => $lista]);
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['cliente', 'atendio', 'detalles.producto']);

        return response()->json($this->resumen($cotizacion) + [
            'telefono' => $cotizacion->cliente?->Telefono,
            'detalles' => $cotizacion->detalles->map(fn ($d) => [
                'producto' => $d->producto?->Nombre ?? 'Producto eliminado', 'talla' => $d->Talla,
                'cantidad' => (int) $d->Cantidad, 'precio' => $d->Precio_unitario, 'subtotal' => $d->Subtotal,
            ]),
        ]);
    }

    public function atender(Cotizacion $cotizacion)
    {
        if ($cotizacion->Estado === 'ATENDIDA') {
            return $this->error('Esta cotización ya estaba atendida.', 409);
        }
        $cotizacion->update(['Estado' => 'ATENDIDA', 'Atendida_por' => $this->empleado()->Id_empleado, 'Atendida_en' => now()]);

        return $this->ok("Cotización #{$cotizacion->Id_cotizacion} marcada como atendida.");
    }

    private function resumen(Cotizacion $c): array
    {
        return [
            'id' => $c->Id_cotizacion, 'total' => $c->Total, 'estado' => $c->Estado,
            'cliente' => $c->cliente?->nombre_completo ?? '—',
            'fecha' => $c->Creado_en?->format('d/m/Y H:i'),
            'atendio' => $c->atendio ? $c->atendio->Nombre . ' ' . $c->atendio->Ap_paterno : null,
            'atendida_en' => $c->Atendida_en?->format('d/m/Y H:i'),
            'renglones' => $c->detalles_count ?? $c->detalles->count(),
        ];
    }
}
