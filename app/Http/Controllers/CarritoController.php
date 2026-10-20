<?php

namespace App\Http\Controllers;

use App\Models\ProductoTalla;
use App\Services\Carrito;
use Illuminate\Http\Request;

/** RF003: agregar, cambiar y quitar artículos del carrito; el total se recalcula en cada vista. */
class CarritoController extends Controller
{
    public function index()
    {
        return view('htmlv.carrito', Carrito::resumen());
    }

    public function agregar(Request $request)
    {
        $d = $request->validate([
            'id_producto' => 'required|integer',
            'talla'       => 'required|string|max:5',
            'cantidad'    => 'required|integer|min:1|max:' . Carrito::MAX_POR_RENGLON,
        ], ['talla.required' => 'Elige una talla.', 'cantidad.min' => 'La cantidad debe ser al menos 1.']);

        $talla = ProductoTalla::with('producto')->where('Id_producto', $d['id_producto'])->where('Talla', $d['talla'])->first();
        if (!$talla || !$talla->producto?->Activo) {
            return back()->with('err', 'Ese producto ya no está disponible.');
        }

        $enCarrito = Carrito::crudo()[Carrito::clave($talla->Id_producto, $talla->Talla)] ?? 0;
        if ($enCarrito + $d['cantidad'] > $talla->Stock) {
            return back()->withInput()->with('err', "Sólo hay {$talla->Stock} pieza(s) en talla {$talla->Talla}" . ($enCarrito ? " y ya tienes {$enCarrito} en tu carrito." : '.'));
        }

        Carrito::agregar($talla->Id_producto, $talla->Talla, (int) $d['cantidad']);

        return back()->with('ok', "Agregaste {$d['cantidad']} pieza(s) de {$talla->producto->Nombre} (talla {$talla->Talla}) al carrito.")
            ->with('agregado', true);
    }

    public function actualizar(Request $request)
    {
        // Los botones −/+ mandan "cantidad"; escribir en el campo manda "cantidad_manual"
        $d = $request->validate([
            'clave'           => 'required|string|max:80',
            'cantidad'        => 'nullable|integer|min:0|max:' . Carrito::MAX_POR_RENGLON,
            'cantidad_manual' => 'nullable|integer|min:0|max:' . Carrito::MAX_POR_RENGLON,
        ]);
        Carrito::cambiar($d['clave'], (int) ($d['cantidad'] ?? $d['cantidad_manual'] ?? 1));
        return redirect()->route('tienda.carrito');
    }

    public function quitar(Request $request)
    {
        $d = $request->validate(['clave' => 'required|string|max:80']);
        Carrito::quitar($d['clave']);
        return redirect()->route('tienda.carrito')->with('ok', 'Artículo quitado del carrito.');
    }
}
