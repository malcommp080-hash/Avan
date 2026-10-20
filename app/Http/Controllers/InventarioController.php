<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['categoria', 'tallas'])->orderBy('Stock')->orderBy('Id_producto')->get();

        return view('htmla.inventario', [
            'saludo'    => $this->saludo(),
            'productos' => $productos,
            'totalProductos' => $productos->count(),
            'totalStock'     => (int) $productos->sum('Stock'),
            'valor'          => (float) $productos->sum(fn ($p) => $p->Stock * $p->Precioneto),
            'stockBajo'      => $productos->filter(fn ($p) => $p->tallas->contains(fn ($t) => $t->Stock < 5))->count(),
        ]);
    }

    /** Ajusta el stock de cada talla de un producto: tallas[Id_pt] = piezas. */
    public function update(Request $request, Producto $producto)
    {
        $d = $this->validar($request, ['tallas' => 'required|array', 'tallas.*' => 'required|integer|min:0|max:100000']);

        DB::transaction(function () use ($d, $producto) {
            foreach ($d['tallas'] as $idPt => $stock) {
                ProductoTalla::where('Id_pt', $idPt)->where('Id_producto', $producto->Id_producto)->update(['Stock' => $stock]);
            }
            $producto->recalcularStock();
        });

        return redirect()->route('admin.inventario.index')->with('ok', 'Stock de "' . $producto->Nombre . '" actualizado.');
    }
}
