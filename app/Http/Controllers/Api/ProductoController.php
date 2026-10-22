<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ProductoController as ProductoWeb;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Modelos del catálogo para la app móvil. Reutiliza la validación, la imagen y las tallas
 * del controlador web; sólo cambia la respuesta a JSON. La imagen llega como multipart.
 */
class ProductoController extends ProductoWeb
{
    public function index(Request $request)
    {
        $todos = Producto::with(['categoria', 'tallas'])->orderBy('Id_categoria')->orderBy('Nombre')->get();

        return response()->json([
            'totalProductos' => $todos->count(),
            'totalStock'     => (int) $todos->sum('Stock'),
            'stockBajo'      => $todos->filter(fn ($p) => $p->tallas->contains(fn ($t) => $t->Stock < 5))->count(),
            'categorias'     => Categoria::orderBy('Nombre')->get()->map(fn ($c) => ['id' => $c->Id_categoria, 'nombre' => $c->Nombre]),
            'segmentos'      => Producto::SEGMENTOS,
            'sugeridas'      => ProductoTalla::SUGERIDAS,
            'productos'      => $todos->map(fn ($p) => [
                'id' => $p->Id_producto, 'nombre' => $p->Nombre, 'id_categoria' => $p->Id_categoria,
                'categoria' => $p->categoria?->Nombre, 'segmento' => $p->Segmento, 'segmento_texto' => $p->segmento_texto,
                'color' => $p->Color, 'descripcion' => $p->Descripcion,
                'preciopro' => $p->Preciopro, 'precioneto' => $p->Precioneto,
                'precio_mayoreo' => $p->Precio_mayoreo, 'min_mayoreo' => $p->Min_mayoreo,
                'activo' => $p->Activo, 'stock' => (int) $p->Stock,
                'imagen' => $p->Imagen ? 'img/productos/' . $p->Imagen : null,
                'tallas' => $p->tallas_ordenadas->map(fn ($t) => ['talla' => $t->Talla, 'stock' => (int) $t->Stock])->values(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validarProducto($request);

        DB::transaction(function () use ($d, $request) {
            $producto = Producto::create($this->datos($d) + ['Stock' => 0, 'Imagen' => $this->subirImagen($request)]);
            $this->guardarTallas($producto, $d['tallas']);
        });

        return response()->json(['message' => 'Modelo agregado al catálogo.'], 201);
    }

    /** Llega por POST (multipart no funciona con PUT en PHP). */
    public function update(Request $request, Producto $producto)
    {
        $d = $this->validarProducto($request);

        DB::transaction(function () use ($d, $request, $producto) {
            $datos = $this->datos($d);
            if ($nueva = $this->subirImagen($request)) {
                $this->borrarImagen($producto->Imagen);
                $datos['Imagen'] = $nueva;
            } elseif ($request->boolean('quitar_imagen')) {
                $this->borrarImagen($producto->Imagen);
                $datos['Imagen'] = null;
            }
            $producto->update($datos);
            $this->guardarTallas($producto, $d['tallas']);
        });

        return response()->json(['message' => 'Modelo actualizado correctamente.']);
    }

    public function destroy(Producto $producto)
    {
        $imagen = $producto->Imagen;
        try {
            $producto->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'No se puede eliminar: tiene registros relacionados en otras tablas.'], 409);
        }
        $this->borrarImagen($imagen);

        return response()->json(['message' => 'Modelo eliminado.']);
    }
}
