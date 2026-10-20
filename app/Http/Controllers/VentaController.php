<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** RF002 (registro de venta por producto) y RF012 (resumen de la venta). */
class VentaController extends Controller
{
    private const MSG_INCOMPLETO = 'Complete todos los campos antes de guardar.';

    public function index()
    {
        return view('htmla.ventas', [
            'ventas'    => Venta::reales()->with('cliente')->withCount('detalles')->orderByDesc('Id_venta')->get(),
            'clientes'  => Cliente::orderBy('Nombre')->get(),
            'productos' => Producto::with('tallas')->where('Activo', true)->orderBy('Nombre')->get(),
            'formas'    => Venta::FORMAS_PAGO,
        ]);
    }

    public function store(Request $request)
    {
        // RF002.4: cualquier campo obligatorio vacío da el mismo mensaje
        $validador = validator($request->all(), [
            'id_cliente'           => 'required|exists:cliente,Id_cliente',
            'forma_pago'           => 'required|in:' . implode(',', array_keys(Venta::FORMAS_PAGO)),
            'items'                => 'required|array|min:1',
            'items.*.id_producto'  => 'required|integer|exists:registroproductos,Id_producto',
            'items.*.talla'        => 'required|string|max:5',
            'items.*.cantidad'     => 'required|integer|min:1|max:100000',
        ], [
            'required' => self::MSG_INCOMPLETO,
            'items.min' => self::MSG_INCOMPLETO,
            'min'      => 'La cantidad debe ser al menos 1.',
            'exists'   => 'El cliente o un producto seleccionado ya no existe.',
            'in'       => 'Selecciona una forma de pago válida.',
        ]);
        if ($validador->fails()) {
            // Un solo aviso aunque falten varios campos
            return back()->withInput()->withErrors(['venta' => collect($validador->errors()->all())->unique()->first()]);
        }
        $d = $validador->validated();

        // El mismo producto y talla no puede ir en dos renglones
        $combos = array_map(fn ($i) => $i['id_producto'] . '|' . $i['talla'], $d['items']);
        if (count($combos) !== count(array_unique($combos))) {
            return back()->withInput()->withErrors(['venta' => 'Un producto con la misma talla aparece dos veces; junta las cantidades en un solo renglón.']);
        }

        try {
            $venta = DB::transaction(function () use ($d) {
                $renglones = Inventario::renglones($d['items']);

                $ahora = now();
                $venta = Venta::create([
                    'Fecha'        => $ahora->toDateString(),
                    'Fecha_hora'   => $ahora,
                    'Realizacion'  => 'Venta en mostrador',
                    'Total'        => round(array_sum(array_column($renglones, 'subtotal')), 2),
                    'Forma_pago'   => $d['forma_pago'],
                    'Origen'       => 'MOSTRADOR',
                    'Id_cliente'   => $d['id_cliente'],
                    'Id_empleado'  => session('admin_id'),
                    'Total_piezas' => array_sum(array_column($renglones, 'cantidad')),
                ]);

                foreach ($renglones as $r) {
                    DetalleVenta::create([
                        'Id_venta' => $venta->Id_venta, 'Id_producto' => $r['producto']->Id_producto, 'Talla' => $r['talla']->Talla,
                        'Cantidad' => $r['cantidad'], 'Precio_unitario' => $r['precio'], 'Subtotal' => $r['subtotal'],
                    ]);
                }
                Inventario::descontar($renglones);

                return $venta;
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('admin.ventas.show', $venta)->with('ok', 'Venta guardada correctamente.');
    }

    /** RF012: resumen detallado de la venta. */
    public function show(Venta $venta)
    {
        abort_if(str_starts_with($venta->Realizacion, 'PROV:'), 404);
        $venta->load(['cliente', 'empleado', 'detalles.producto']);

        return view('htmla.venta-resumen', ['venta' => $venta]);
    }

    /** Eliminar una venta devuelve al inventario las piezas que se habían descontado. */
    public function destroy(Venta $venta)
    {
        abort_if(str_starts_with($venta->Realizacion, 'PROV:'), 404);

        return $this->borrar(function () use ($venta) {
            DB::transaction(function () use ($venta) {
                // Si el pago en línea se rechazó, las piezas ya habían regresado al inventario
                if ($venta->pago?->Estado !== 'RECHAZADO') {
                    Inventario::devolver($venta->detalles);
                }
                $venta->delete(); // el detalle se borra en cascada
            });
        }, 'Venta eliminada y stock devuelto al inventario.', route('admin.ventas.index'));
    }
}
