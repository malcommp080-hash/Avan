<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\Inventario;
use App\Services\VentaMostrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** RF002 (registro de venta por producto) y RF012 (resumen de la venta). */
class VentaController extends Controller
{
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
        try {
            $venta = VentaMostrador::crear(VentaMostrador::validar($request->all()), (int) session('admin_id'));
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
