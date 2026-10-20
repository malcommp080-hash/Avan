<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;

/** RF009: el colaborador revisa las cotizaciones de los clientes y las marca como atendidas. */
class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->validate(['estado' => 'nullable|in:PENDIENTE,ATENDIDA'])['estado'] ?? 'PENDIENTE';

        return view('htmla.cotizaciones', [
            'cotizaciones' => Cotizacion::with(['cliente', 'atendio'])->withCount('detalles')
                ->where('Estado', $estado)->orderBy($estado === 'PENDIENTE' ? 'Id_cotizacion' : 'Atendida_en', $estado === 'PENDIENTE' ? 'asc' : 'desc')
                ->get(),
            'estado'     => $estado,
            'pendientes' => Cotizacion::where('Estado', 'PENDIENTE')->count(),
        ]);
    }

    public function show(Cotizacion $cotizacion)
    {
        return view('htmla.cotizacion', ['cot' => $cotizacion->load(['cliente', 'atendio', 'detalles.producto'])]);
    }

    public function atender(Cotizacion $cotizacion)
    {
        if ($cotizacion->Estado === 'ATENDIDA') {
            return back()->with('err', 'Esta cotización ya estaba atendida.');
        }
        $cotizacion->update(['Estado' => 'ATENDIDA', 'Atendida_por' => session('admin_id'), 'Atendida_en' => now()]);

        return redirect()->route('admin.cotizaciones.index')->with('ok', "Cotización #{$cotizacion->Id_cotizacion} marcada como atendida.");
    }
}
