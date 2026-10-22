<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** RF002: registro de una venta en mostrador. Lo usan el panel web y la app móvil. */
class VentaMostrador
{
    public const MSG_INCOMPLETO = 'Complete todos los campos antes de guardar.';

    /** Reglas y mensajes de validación (cualquier campo vacío da el mismo aviso, RF002.4). */
    public static function validar(array $datos): array
    {
        $validador = validator($datos, [
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
            throw ValidationException::withMessages(['venta' => collect($validador->errors()->all())->unique()->first()]);
        }
        $d = $validador->validated();

        // El mismo producto y talla no puede ir en dos renglones
        $combos = array_map(fn ($i) => $i['id_producto'] . '|' . $i['talla'], $d['items']);
        if (count($combos) !== count(array_unique($combos))) {
            throw ValidationException::withMessages(['venta' => 'Un producto con la misma talla aparece dos veces; junta las cantidades en un solo renglón.']);
        }

        return $d;
    }

    /** Guarda la venta y su detalle y descuenta el stock; lanza ValidationException si no alcanza. */
    public static function crear(array $d, int $idEmpleado): Venta
    {
        return DB::transaction(function () use ($d, $idEmpleado) {
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
                'Id_empleado'  => $idEmpleado,
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
    }
}
