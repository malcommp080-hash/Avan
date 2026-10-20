<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Validation\ValidationException;

/**
 * Cálculo de renglones y movimiento de stock por talla. Lo usan la venta en mostrador
 * (RF002), la compra en línea y la cotización (RF003/RF009). Llamar dentro de DB::transaction().
 */
class Inventario
{
    /**
     * @param  array  $items  [['id_producto' => int, 'talla' => string, 'cantidad' => int], ...]
     * @param  bool   $bloquear  true para bloquear las filas (al vender); false sólo para cotizar
     * @return array  renglones con producto, talla, cantidad, precio y subtotal (precio desde la BD)
     */
    public static function renglones(array $items, bool $bloquear = true): array
    {
        $ids = array_unique(array_map(fn ($i) => (int) $i['id_producto'], $items));
        $productos = Producto::whereIn('Id_producto', $ids)->get()->keyBy('Id_producto');

        $q = ProductoTalla::whereIn('Id_producto', $ids);
        if ($bloquear) $q->lockForUpdate();
        $tallas = $q->get()->keyBy(fn ($t) => $t->Id_producto . '|' . $t->Talla);

        // El mayoreo se decide con el total de piezas del producto, sumando todas sus tallas
        $piezasPorProducto = [];
        foreach ($items as $i) {
            $piezasPorProducto[(int) $i['id_producto']] = ($piezasPorProducto[(int) $i['id_producto']] ?? 0) + (int) $i['cantidad'];
        }

        $renglones = [];
        foreach ($items as $i) {
            $p = $productos[(int) $i['id_producto']] ?? null;
            $t = $tallas[(int) $i['id_producto'] . '|' . $i['talla']] ?? null;
            if (!$p || !$t) {
                throw ValidationException::withMessages(['venta' => 'Un producto o talla seleccionado ya no existe.']);
            }
            $cant = (int) $i['cantidad'];
            if ($cant > $t->Stock) {
                throw ValidationException::withMessages(['venta' => "Sólo hay {$t->Stock} pieza(s) de \"{$p->Nombre}\" talla {$t->Talla}."]);
            }
            $precio = $p->precioPara($piezasPorProducto[$p->Id_producto]);
            $renglones[] = [
                'producto' => $p, 'talla' => $t, 'cantidad' => $cant, 'precio' => $precio,
                'subtotal' => round($cant * $precio, 2), 'mayoreo' => $precio < $p->Precioneto,
            ];
        }
        return $renglones;
    }

    /** Descuenta del stock las piezas de los renglones ya validados. */
    public static function descontar(array $renglones): void
    {
        foreach ($renglones as $r) {
            $r['talla']->decrement('Stock', $r['cantidad']);
        }
        foreach (collect($renglones)->pluck('producto')->unique('Id_producto') as $p) {
            $p->recalcularStock();
        }
    }

    /** Regresa al stock las piezas de un detalle (al eliminar o cancelar una venta). */
    public static function devolver(iterable $detalles): void
    {
        $productos = [];
        foreach ($detalles as $d) {
            $talla = $d->Talla ?: 'UNI';
            $fila = ProductoTalla::firstOrCreate(['Id_producto' => $d->Id_producto, 'Talla' => $talla], ['Stock' => 0]);
            $fila->increment('Stock', $d->Cantidad);
            $productos[$d->Id_producto] = true;
        }
        foreach (Producto::whereIn('Id_producto', array_keys($productos))->get() as $p) {
            $p->recalcularStock();
        }
    }
}
