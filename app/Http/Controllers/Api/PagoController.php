<?php

namespace App\Http\Controllers\Api;

use App\Models\Pago;
use App\Services\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** RF004 en la app móvil: revisar el ticket de las compras en línea y aprobar o rechazar el pago. */
class PagoController extends ApiController
{
    public function index(Request $request)
    {
        $f = $request->validate(['estado' => 'nullable|in:PENDIENTE,APROBADO,RECHAZADO', 'buscar' => 'nullable|string|max:40']);
        $estado = $f['estado'] ?? 'PENDIENTE';

        $pagos = Pago::with(['venta.cliente', 'venta.detalles.producto', 'validador'])
            ->where('Estado', $estado)
            ->when($f['buscar'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w->where('Folio', 'like', "%{$v}%")
                ->orWhereHas('venta.cliente', fn ($c) => $c->where('Nombre', 'like', "%{$v}%")->orWhere('Ap_paterno', 'like', "%{$v}%")->orWhere('Telefono', 'like', "%{$v}%"))))
            ->when($estado === 'PENDIENTE', fn ($q) => $q->orderByRaw('Comprobante IS NULL')->orderBy('Id_pago'), fn ($q) => $q->orderByDesc('Validado_en'))
            ->limit(50)->get()
            ->map(fn ($p) => [
                'id' => $p->Id_pago, 'folio' => $p->Folio, 'monto' => $p->Monto, 'estado' => $p->Estado,
                'estado_texto' => $p->estado_texto, 'referencia' => $p->Referencia,
                // Ruta relativa: la app le antepone la dirección del servidor
                'comprobante' => $p->Comprobante ? 'img/comprobantes/' . $p->Comprobante : null,
                'comprobante_en' => $p->Comprobante_en?->format('d/m/Y H:i'),
                'validado_por' => $p->validador ? $p->validador->Nombre . ' ' . $p->validador->Ap_paterno : null,
                'validado_en' => $p->Validado_en?->format('d/m/Y H:i'),
                'motivo_rechazo' => $p->Motivo_rechazo,
                'id_venta' => $p->Id_venta,
                'cliente' => $p->venta?->cliente?->nombre_completo ?? '—',
                'telefono' => $p->venta?->cliente?->Telefono,
                'detalles' => $p->venta ? $p->venta->detalles->map(fn ($d) => [
                    'producto' => $d->producto?->Nombre ?? 'Producto eliminado', 'talla' => $d->Talla, 'cantidad' => (int) $d->Cantidad,
                ]) : [],
            ]);

        return response()->json([
            'pagos' => $pagos,
            'puedeValidar' => in_array($this->empleado()->rol, ['admin', 'contador'], true),
        ]);
    }

    public function aprobar(Pago $pago)
    {
        return $this->resolver($pago, 'APROBADO', null);
    }

    public function rechazar(Request $request, Pago $pago)
    {
        $d = $request->validate(['motivo' => 'required|string|max:255'], ['motivo.required' => 'Escribe el motivo del rechazo para que el cliente lo vea.']);
        return $this->resolver($pago, 'RECHAZADO', $d['motivo']);
    }

    private function resolver(Pago $pago, string $estado, ?string $motivo)
    {
        $idEmpleado = $this->empleado()->Id_empleado;
        $resultado = DB::transaction(function () use ($pago, $estado, $motivo, $idEmpleado) {
            // Se vuelve a leer bloqueado: si dos administradores lo resuelven a la vez, sólo cuenta el primero
            $p = Pago::with('venta.detalles')->lockForUpdate()->find($pago->Id_pago);
            if (!$p || $p->Estado !== 'PENDIENTE') {
                return $p;
            }
            $p->update([
                'Estado' => $estado, 'Motivo_rechazo' => $motivo,
                'Validado_por' => $idEmpleado, 'Validado_en' => now(),
            ]);
            // Al rechazar, las piezas apartadas regresan al inventario
            if ($estado === 'RECHAZADO' && $p->venta) {
                Inventario::devolver($p->venta->detalles);
            }
            return true;
        });

        if ($resultado !== true) {
            $ya = $resultado ? strtolower(Pago::ESTADOS[$resultado->Estado] ?? $resultado->Estado) : 'eliminado';
            return $this->error("El pago {$pago->Folio} ya fue {$ya} y no puede modificarse.", 409);
        }

        return $this->ok($estado === 'APROBADO'
            ? "Pago {$pago->Folio} aprobado. La venta ya cuenta en los reportes."
            : "Pago {$pago->Folio} rechazado. Las piezas regresaron al inventario.");
    }
}
