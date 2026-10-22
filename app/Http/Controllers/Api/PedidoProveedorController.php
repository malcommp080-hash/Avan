<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\PedidoProveedorController as PedidoProveedorWeb;
use App\Models\Cliente;
use App\Models\PedidoProveedor;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class PedidoProveedorController extends ApiController
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('Nombre')->get();
        $mapa = $proveedores->keyBy('Id_proovedor');

        return response()->json([
            'pedidos' => PedidoProveedor::orderByDesc('Fecha')->orderByDesc('Id_venta')->get()->map(function ($p) use ($mapa) {
                $prov = $mapa->get($p->proveedor_id);
                return [
                    'id' => $p->Id_venta, 'fecha' => $p->Fecha->format('d/m/Y'), 'total' => (float) $p->Total,
                    'piezas' => (int) $p->Total_piezas, 'estado' => $p->estado,
                    'proveedor' => $prov ? $prov->Nombre . ' ' . $prov->Ap_paterno : 'Proveedor eliminado',
                ];
            }),
            'proveedores' => $proveedores->map(fn ($p) => ['id' => $p->Id_proovedor, 'nombre' => $p->Nombre . ' ' . $p->Ap_paterno]),
            'estados' => PedidoProveedorWeb::ESTADOS,
        ]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request, [
            'id_proovedor' => 'required|exists:proovedor,Id_proovedor',
            'fecha'        => 'required|date',
            'total'        => 'required|numeric|min:0',
            'total_piezas' => 'required|integer|min:0',
            'estado'       => 'required|in:' . implode(',', PedidoProveedorWeb::ESTADOS),
        ]);

        // venta.Id_cliente es obligatorio con FK: se usa el primer cliente existente como "sistema"
        $idCliente = Cliente::min('Id_cliente');
        if (!$idCliente) {
            return $this->error('Registra al menos un cliente antes de guardar pedidos a proveedor (la tabla venta requiere un cliente).');
        }

        PedidoProveedor::create([
            'Fecha' => $d['fecha'],
            'Realizacion' => "PROV:{$d['id_proovedor']}:{$d['estado']}",
            'Total' => $d['total'],
            'Id_cliente' => $idCliente,
            'Total_piezas' => $d['total_piezas'],
        ]);

        return $this->ok('Pedido a proveedor registrado.', [], 201);
    }

    public function destroy(PedidoProveedor $pedido)
    {
        return $this->borrarJson(fn () => $pedido->delete(), 'Pedido eliminado.');
    }
}
