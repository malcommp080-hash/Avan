<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Tienda en línea (RF003): portada, categorías, catálogo con filtros y ficha del producto. */
class TiendaController extends Controller
{
    /** Productos visibles y con al menos una pieza. */
    private function visibles()
    {
        return Producto::with(['tallas', 'categoria'])->where('Activo', true)->where('Stock', '>', 0);
    }

    /** IDs de los productos más vendidos (por piezas). */
    private function masVendidosIds(int $limite = 12): array
    {
        return DB::table('detalleventa')->select('Id_producto', DB::raw('SUM(Cantidad) as vendidos'))
            ->groupBy('Id_producto')->orderByDesc('vendidos')->limit($limite)->pluck('Id_producto')->all();
    }

    public function inicio()
    {
        $vendidos = $this->masVendidosIds(1);

        return view('htmlv.inicio', [
            'oferta'     => $this->visibles()->whereNotNull('Precio_mayoreo')->inRandomOrder()->first(),
            'menos100'   => $this->visibles()->where('Precioneto', '<', 100)->orderByDesc('Precioneto')->first(),
            'masVendido' => $vendidos ? $this->visibles()->whereKey($vendidos[0])->first() : $this->visibles()->latest('Id_producto')->first(),
        ]);
    }

    public function categorias()
    {
        return view('htmlv.categorias', [
            'categorias' => Categoria::withCount(['productos' => fn ($q) => $q->where('Activo', true)->where('Stock', '>', 0)])
                ->orderBy('Nombre')->get(),
        ]);
    }

    /**
     * Catálogo: una sola página con filtros (categoría, Caballero/Dama/Niño/Niña, búsqueda,
     * ofertas, menos de $100, más vendidos) en lugar de pantallas anidadas.
     */
    public function catalogo(Request $request)
    {
        $f = $request->validate([
            'categoria' => 'nullable|integer',
            'segmento'  => 'nullable|in:' . implode(',', array_keys(Producto::SEGMENTOS)),
            'q'         => 'nullable|string|max:60',
            'filtro'    => 'nullable|in:ofertas,menos100,vendidos',
        ]);

        $categoria = isset($f['categoria']) ? Categoria::find($f['categoria']) : null;
        $q = $this->visibles()
            ->when($categoria, fn ($q) => $q->where('Id_categoria', $categoria->Id_categoria))
            // Lo unisex aparece en cualquier segmento
            ->when($f['segmento'] ?? null, fn ($q, $v) => $q->whereIn('Segmento', [$v, 'UNISEX']))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w->where('Nombre', 'like', "%{$v}%")->orWhere('Color', 'like', "%{$v}%")->orWhere('Descripcion', 'like', "%{$v}%")));

        $titulo = $categoria?->Nombre ?? 'Catálogo';
        switch ($f['filtro'] ?? null) {
            case 'ofertas':  $q->whereNotNull('Precio_mayoreo'); $titulo = 'Ofertas y mayoreo'; break;
            case 'menos100': $q->where('Precioneto', '<', 100); $titulo = 'Menos de $100'; break;
            case 'vendidos':
                $ids = $this->masVendidosIds();
                $q->whereIn('Id_producto', $ids ?: [0]);
                if ($ids) $q->orderByRaw('FIELD(Id_producto, ' . implode(',', array_map('intval', $ids)) . ')');
                $titulo = 'Más vendidos';
                break;
        }
        if (!empty($f['q'])) $titulo = 'Resultados para "' . $f['q'] . '"';

        $productos = $q->orderBy('Nombre')->get();

        // Segmentos que tienen algo en esta categoría, para no mostrar filtros vacíos
        $conSegmento = Producto::where('Activo', true)->where('Stock', '>', 0)
            ->when($categoria, fn ($q) => $q->where('Id_categoria', $categoria->Id_categoria))
            ->distinct()->pluck('Segmento')->all();
        $segmentos = collect(Producto::SEGMENTOS)->except('UNISEX')
            ->filter(fn ($_, $k) => in_array($k, $conSegmento, true) || in_array('UNISEX', $conSegmento, true));

        return view('htmlv.catalogo', [
            'titulo'     => $titulo,
            'categoria'  => $categoria,
            'productos'  => $productos,
            'segmentos'  => $segmentos,
            'filtros'    => $f,
        ]);
    }

    public function producto(Producto $producto)
    {
        abort_unless($producto->Activo, 404);
        $producto->load(['tallas', 'categoria']);

        return view('htmlv.producto', [
            'producto'    => $producto,
            'relacionados' => $this->visibles()->where('Id_categoria', $producto->Id_categoria)
                ->whereKeyNot($producto->Id_producto)->inRandomOrder()->limit(4)->get(),
        ]);
    }
}
