<?php

namespace App\Services;

use App\Models\Producto;

/**
 * RF003: carrito en sesión. Guarda sólo [clave "idProducto|talla" => cantidad];
 * precios y stock siempre se leen de la BD al mostrarlo o al pagar.
 */
class Carrito
{
    private const SESION = 'carrito';
    public const MAX_POR_RENGLON = 999;

    public static function crudo(): array
    {
        return session(self::SESION, []);
    }

    public static function clave(int $idProducto, string $talla): string
    {
        return $idProducto . '|' . $talla;
    }

    public static function agregar(int $idProducto, string $talla, int $cantidad): void
    {
        $c = self::crudo();
        $k = self::clave($idProducto, $talla);
        $c[$k] = min(self::MAX_POR_RENGLON, ($c[$k] ?? 0) + max(1, $cantidad));
        session([self::SESION => $c]);
    }

    public static function cambiar(string $clave, int $cantidad): void
    {
        $c = self::crudo();
        if (!isset($c[$clave])) return;
        if ($cantidad < 1) unset($c[$clave]);
        else $c[$clave] = min(self::MAX_POR_RENGLON, $cantidad);
        session([self::SESION => $c]);
    }

    public static function quitar(string $clave): void
    {
        $c = self::crudo();
        unset($c[$clave]);
        session([self::SESION => $c]);
    }

    public static function vaciar(): void
    {
        session()->forget(self::SESION);
    }

    /** Número de piezas, para el globo del ícono del carrito. */
    public static function piezas(): int
    {
        return array_sum(self::crudo());
    }

    /** Items en el formato que espera Inventario::renglones(). */
    public static function items(): array
    {
        $items = [];
        foreach (self::crudo() as $k => $cant) {
            [$id, $talla] = explode('|', $k, 2) + [1 => ''];
            $items[] = ['id_producto' => (int) $id, 'talla' => $talla, 'cantidad' => (int) $cant];
        }
        return $items;
    }

    /**
     * Renglones para mostrar: con precio vigente (mayoreo incluido) y aviso si ya no alcanza el stock.
     * Quita del carrito lo que ya no existe o se ocultó de la tienda.
     */
    public static function resumen(): array
    {
        $items = self::items();
        $productos = Producto::with('tallas')->whereIn('Id_producto', array_column($items, 'id_producto'))->get()->keyBy('Id_producto');

        $piezas = [];
        foreach ($items as $i) $piezas[$i['id_producto']] = ($piezas[$i['id_producto']] ?? 0) + $i['cantidad'];

        $renglones = [];
        foreach ($items as $i) {
            $p = $productos[$i['id_producto']] ?? null;
            $t = $p?->tallas->firstWhere('Talla', $i['talla']);
            if (!$p || !$t || !$p->Activo) {
                self::quitar(self::clave($i['id_producto'], $i['talla']));
                continue;
            }
            $precio = $p->precioPara($piezas[$p->Id_producto]);
            $renglones[] = [
                'clave' => self::clave($p->Id_producto, $t->Talla), 'producto' => $p, 'talla' => $t->Talla,
                'stock' => $t->Stock, 'cantidad' => $i['cantidad'], 'precio' => $precio,
                'mayoreo' => $precio < $p->Precioneto, 'subtotal' => round($precio * $i['cantidad'], 2),
                'falta' => $i['cantidad'] > $t->Stock,
            ];
        }

        return [
            'renglones' => $renglones,
            'total'     => round(array_sum(array_column($renglones, 'subtotal')), 2),
            'piezas'    => array_sum(array_column($renglones, 'cantidad')),
            'hayFaltantes' => collect($renglones)->contains('falta', true),
        ];
    }
}
