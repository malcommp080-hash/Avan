<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoTalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Modelos del catálogo: datos, imagen, precio de mayoreo y stock por talla. */
class ProductoController extends Controller
{
    private const CARPETA = 'img/productos';

    protected function reglas(): array
    {
        return [
            'nombre'         => 'required|string|max:60',
            'id_categoria'   => 'required|exists:categoria,Id_categoria',
            'segmento'       => ['required', Rule::in(array_keys(Producto::SEGMENTOS))],
            'color'          => 'nullable|string|max:30',
            'descripcion'    => 'nullable|string|max:255',
            'preciopro'      => 'required|numeric|min:0',
            'precioneto'     => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0|lt:precioneto',
            'min_mayoreo'    => 'nullable|integer|min:2|max:1000',
            'activo'         => 'nullable|boolean',
            'imagen'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'tallas'         => 'required|array|min:1',
            'tallas.*.talla' => 'required|string|max:5|distinct:ignore_case',
            'tallas.*.stock' => 'required|integer|min:0|max:100000',
        ];
    }

    protected function validarProducto(Request $request): array
    {
        return $request->validate($this->reglas(), $this->mensajes + [
            'precio_mayoreo.lt' => 'El precio de mayoreo debe ser menor al precio de venta.',
            'tallas.required'   => 'Agrega al menos una talla.',
            'tallas.*.talla.distinct' => 'Hay tallas repetidas.',
            'tallas.*.talla.required' => 'Escribe el nombre de cada talla.',
            'tallas.*.stock.required' => 'Escribe el stock de cada talla.',
            'image' => 'El archivo debe ser una imagen.',
            'mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede pesar más de 4 MB.',
        ], $this->atributos + ['segmento' => 'segmento', 'precio_mayoreo' => 'precio de mayoreo', 'min_mayoreo' => 'piezas para mayoreo']);
    }

    public function index(Request $request)
    {
        return $this->vista(null, $request);
    }

    private function vista(?Producto $editar, Request $request)
    {
        $f = $request->validate(['categoria' => 'nullable|integer', 'segmento' => 'nullable|string', 'buscar' => 'nullable|string|max:60']);

        $todos = Producto::with('tallas')->get();
        $productos = Producto::with(['categoria', 'tallas'])
            ->when($f['categoria'] ?? null, fn ($q, $v) => $q->where('Id_categoria', $v))
            ->when($f['segmento'] ?? null, fn ($q, $v) => $q->where('Segmento', $v))
            ->when($f['buscar'] ?? null, fn ($q, $v) => $q->where('Nombre', 'like', "%{$v}%"))
            ->orderBy('Id_categoria')->orderBy('Nombre')->get();

        return view('htmla.productos', [
            'saludo'         => $this->saludo(),
            'productos'      => $productos,
            'categorias'     => Categoria::orderBy('Nombre')->get(),
            'segmentos'      => Producto::SEGMENTOS,
            'sugeridas'      => ProductoTalla::SUGERIDAS,
            'filtros'        => $f,
            'totalProductos' => $todos->count(),
            'totalStock'     => (int) $todos->sum('Stock'),
            'stockBajo'      => $todos->filter(fn ($p) => $p->tallas->contains(fn ($t) => $t->Stock < 5))->count(),
            'editar'         => $editar?->load('tallas'),
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validarProducto($request);

        DB::transaction(function () use ($d, $request) {
            $producto = Producto::create($this->datos($d) + ['Stock' => 0, 'Imagen' => $this->subirImagen($request)]);
            $this->guardarTallas($producto, $d['tallas']);
        });

        return redirect()->route('admin.productos.index')->with('ok', 'Modelo agregado al catálogo.');
    }

    public function edit(Request $request, Producto $producto)
    {
        return $this->vista($producto, $request);
    }

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

        return redirect()->route('admin.productos.index')->with('ok', 'Modelo actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $imagen = $producto->Imagen;
        $resp = $this->borrar(fn () => $producto->delete(), 'Modelo eliminado.', route('admin.productos.index'));
        if (!Producto::whereKey($producto->Id_producto)->exists()) {
            $this->borrarImagen($imagen);
        }
        return $resp;
    }

    protected function datos(array $d): array
    {
        $mayoreo = isset($d['precio_mayoreo']) && $d['precio_mayoreo'] !== '' ? $d['precio_mayoreo'] : null;
        return [
            'Nombre' => $d['nombre'], 'Id_categoria' => $d['id_categoria'], 'Segmento' => $d['segmento'],
            'Color' => $d['color'] ?? null, 'Descripcion' => $d['descripcion'] ?? null,
            'Preciopro' => $d['preciopro'], 'Precioneto' => $d['precioneto'],
            'Precio_mayoreo' => $mayoreo, 'Min_mayoreo' => $d['min_mayoreo'] ?? 5,
            'Activo' => (bool) ($d['activo'] ?? false),
        ];
    }

    /** Reemplaza las tallas por las del formulario y actualiza el stock total. */
    protected function guardarTallas(Producto $producto, array $tallas): void
    {
        $nuevas = collect($tallas)->mapWithKeys(fn ($t) => [mb_strtoupper(trim($t['talla'])) => (int) $t['stock']]);

        // Las tallas que se quitan del formulario se borran; si tienen ventas, el detalle conserva el texto de la talla
        $producto->tallas()->whereNotIn('Talla', $nuevas->keys())->delete();
        foreach ($nuevas as $talla => $stock) {
            ProductoTalla::updateOrCreate(['Id_producto' => $producto->Id_producto, 'Talla' => $talla], ['Stock' => $stock]);
        }
        $producto->recalcularStock();
    }

    protected function subirImagen(Request $request): ?string
    {
        if (!$request->hasFile('imagen')) return null;
        $archivo = $request->file('imagen');
        $nombre = uniqid('p_') . '.' . strtolower($archivo->guessExtension() ?: $archivo->getClientOriginalExtension());
        $archivo->move(public_path(self::CARPETA), $nombre);
        return $nombre;
    }

    protected function borrarImagen(?string $nombre): void
    {
        if ($nombre && is_file($ruta = public_path(self::CARPETA . '/' . basename($nombre)))) {
            @unlink($ruta);
        }
    }
}
