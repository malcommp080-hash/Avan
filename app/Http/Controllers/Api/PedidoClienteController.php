<?php

namespace App\Http\Controllers\Api;

use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoClienteController extends ApiController
{
    public function index()
    {
        // Más antiguos primero = más urgentes
        $pedidos = Pedido::with('cliente')->orderBy('Fecha')->orderBy('Id_pedido')->get()
            ->map(function ($p) {
                $dias = max(0, (int) $p->Fecha->startOfDay()->diffInDays(now()->startOfDay(), false));
                return [
                    'id' => $p->Id_pedido, 'cliente' => $p->cliente?->nombre_completo ?? '—',
                    'fecha' => $p->Fecha->format('d/m/Y'), 'total' => (float) $p->Total, 'piezas' => (int) $p->Totalpiezas,
                    'dias' => $dias, 'prioridad' => $dias >= 7 ? 'URGENTE' : ($dias >= 3 ? 'ALTA' : 'NORMAL'),
                ];
            });

        return response()->json([
            'pedidos'  => $pedidos,
            'clientes' => Cliente::orderBy('Nombre')->get()->map(fn ($c) => ['id' => $c->Id_cliente, 'nombre' => $c->nombre_completo]),
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request, [
            'id_cliente'   => 'required|exists:cliente,Id_cliente',
            'fecha'        => 'required|date',
            'total'        => 'required|numeric|min:0',
            'total_piezas' => 'required|integer|min:0',
        ]);
        Pedido::create([
            'Id_cliente' => $d['id_cliente'], 'Fecha' => $d['fecha'],
            'Total' => $d['total'], 'Totalpiezas' => $d['total_piezas'],
        ]);
        return $this->ok('Pedido registrado correctamente.', [], 201);
    }

    public function destroy(Pedido $pedido)
    {
        return $this->borrarJson(fn () => $pedido->delete(), 'Pedido eliminado.');
    }
}
