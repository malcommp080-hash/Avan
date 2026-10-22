<?php

namespace App\Http\Controllers\Api;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\Inventario;
use App\Services\VentaMostrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** RF002 (venta en mostrador) y RF012 (resumen) para la app móvil. */
class VentaController extends ApiController
{
    public function index()
    {
        $ventas = Venta::reales()->with('cliente')->withCount('detalles')->orderByDesc('Id_venta')->limit(100)->get()
            ->map(fn ($v) => [
                'id' => $v->Id_venta, 'fecha' => $v->fecha_texto, 'total' => (float) $v->Total,
                'cliente' => $v->cliente?->nombre_completo ?? '—', 'origen' => $v->Origen,
                'forma_pago' => $v->forma_pago_texto, 'piezas' => (int) $v->Total_piezas, 'renglones' => $v->detalles_count,
            ]);

        return response()->json(['ventas' => $ventas]);
    }

    /** Lo que necesita el formulario de nueva venta: clientes, productos con tallas y formas de pago. */
    public function catalogo()
    {
        return response()->json([
            'clientes' => Cliente::orderBy('Nombre')->get()->map(fn ($c) => ['id' => $c->Id_cliente, 'nombre' => $c->nombre_completo, 'telefono' => $c->Telefono]),
            'productos' => Producto::with('tallas')->where('Activo', true)->orderBy('Nombre')->get()->map(fn ($p) => [
                'id' => $p->Id_producto, 'nombre' => $p->Nombre, 'precio' => $p->Precioneto,
                'precio_mayoreo' => $p->tiene_mayoreo ? $p->Precio_mayoreo : null, 'min_mayoreo' => $p->tiene_mayoreo ? $p->Min_mayoreo : null,
                'tallas' => $p->tallas_ordenadas->map(fn ($t) => ['talla' => $t->Talla, 'stock' => (int) $t->Stock])->values(),
            ]),
            'formas' => Venta::FORMAS_PAGO,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $venta = VentaMostrador::crear(VentaMostrador::validar($request->all()), $this->empleado()->Id_empleado);
        } catch (ValidationException $e) {
            return $this->error(collect($e->errors())->flatten()->first());
        }

        return $this->ok('Venta guardada correctamente.', ['id' => $venta->Id_venta], 201);
    }

    public function show(Venta $venta)
    {
        abort_if(str_starts_with($venta->Realizacion, 'PROV:'), 404);
        $venta->load(['cliente', 'empleado', 'detalles.producto', 'pago']);

        return response()->json([
            'id' => $venta->Id_venta, 'fecha' => $venta->fecha_texto, 'total' => (float) $venta->Total,
            'piezas' => (int) $venta->Total_piezas, 'origen' => $venta->Origen, 'forma_pago' => $venta->forma_pago_texto,
            'cliente' => $venta->cliente?->nombre_completo ?? '—', 'telefono' => $venta->cliente?->Telefono,
            'empleado' => $venta->empleado ? $venta->empleado->Nombre . ' ' . $venta->empleado->Ap_paterno : null,
            'direccion_entrega' => $venta->Direccion_entrega,
            'pago' => $venta->pago ? ['folio' => $venta->pago->Folio, 'estado' => $venta->pago->Estado, 'estado_texto' => $venta->pago->estado_texto] : null,
            'detalles' => $venta->detalles->map(fn ($d) => [
                'producto' => $d->producto?->Nombre ?? 'Producto eliminado', 'talla' => $d->Talla,
                'cantidad' => (int) $d->Cantidad, 'precio' => $d->Precio_unitario, 'subtotal' => $d->Subtotal,
            ]),
        ]);
    }

    /** Eliminar una venta devuelve al inventario las piezas que se habían descontado. */
    public function destroy(Venta $venta)
    {
        abort_if(str_starts_with($venta->Realizacion, 'PROV:'), 404);

        return $this->borrarJson(function () use ($venta) {
            DB::transaction(function () use ($venta) {
                if ($venta->pago?->Estado !== 'RECHAZADO') {
                    Inventario::devolver($venta->detalles);
                }
                $venta->delete();
            });
        }, 'Venta eliminada y stock devuelto al inventario.');
    }
}
