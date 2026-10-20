<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RF004: el administrador revisa los pagos de las compras en línea (folio, cliente, monto y ticket)
 * y los aprueba o rechaza. Un pago ya resuelto no se puede volver a modificar.
 */
class ValidacionPagoController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate(['estado' => 'nullable|in:PENDIENTE,APROBADO,RECHAZADO', 'buscar' => 'nullable|string|max:40']);
        $estado = $f['estado'] ?? 'PENDIENTE';

        $pagos = Pago::with(['venta.cliente', 'venta.detalles.producto', 'validador'])
            ->where('Estado', $estado)
            ->when($f['buscar'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w->where('Folio', 'like', "%{$v}%")
                ->orWhereHas('venta.cliente', fn ($c) => $c->where('Nombre', 'like', "%{$v}%")->orWhere('Ap_paterno', 'like', "%{$v}%")->orWhere('Telefono', 'like', "%{$v}%"))))
            // Pendientes: primero los que ya tienen ticket y los más antiguos
            ->when($estado === 'PENDIENTE', fn ($q) => $q->orderByRaw('Comprobante IS NULL')->orderBy('Id_pago'), fn ($q) => $q->orderByDesc('Validado_en'))
            ->paginate(20)->withQueryString();

        $conteo = Pago::select('Estado', DB::raw('COUNT(*) n'), DB::raw('SUM(Monto) total'))->groupBy('Estado')->get()->keyBy('Estado');

        return view('htmla.validacionpa', [
            'saludo'   => $this->saludo(),
            'pagos'    => $pagos,
            'estado'   => $estado,
            'filtros'  => $f,
            'conteo'   => $conteo,
            'puedeValidar' => in_array(session('admin_rol'), ['admin', 'contador'], true),
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
        $resultado = DB::transaction(function () use ($pago, $estado, $motivo) {
            // Se vuelve a leer bloqueado: si dos administradores lo resuelven a la vez, sólo cuenta el primero
            $p = Pago::with('venta.detalles')->lockForUpdate()->find($pago->Id_pago);
            if (!$p || $p->Estado !== 'PENDIENTE') {
                return $p;
            }
            $p->update([
                'Estado' => $estado, 'Motivo_rechazo' => $motivo,
                'Validado_por' => session('admin_id'), 'Validado_en' => now(),
            ]);
            // Al rechazar, las piezas apartadas regresan al inventario
            if ($estado === 'RECHAZADO' && $p->venta) {
                Inventario::devolver($p->venta->detalles);
            }
            return true;
        });

        if ($resultado !== true) {
            $ya = $resultado ? strtolower(Pago::ESTADOS[$resultado->Estado] ?? $resultado->Estado) : 'eliminado';
            return back()->with('err', "El pago {$pago->Folio} ya fue {$ya} y no puede modificarse.");
        }

        return back()->with('ok', $estado === 'APROBADO'
            ? "Pago {$pago->Folio} aprobado. La venta ya cuenta en los reportes."
            : "Pago {$pago->Folio} rechazado. Las piezas regresaron al inventario.");
    }
}
