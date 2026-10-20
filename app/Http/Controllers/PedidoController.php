<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\DetalleVenta;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\Venta;
use App\Services\Carrito;
use App\Services\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Compra en línea (RF003) siguiendo el flujo de Figma:
 * carrito → Confirmación de pedido → Información de pago → Confirmación de pago (ticket) → Compra finalizada.
 * El pago queda PENDIENTE hasta que un administrador lo aprueba (RF004). También genera cotizaciones (RF009).
 */
class PedidoController extends Controller
{
    private function cliente(): Cliente
    {
        return Cliente::findOrFail(session('cliente_id'));
    }

    /** Sólo el dueño ve su pedido, y sólo si es una compra en línea. */
    private function propio(Venta $venta): Venta
    {
        abort_unless($venta->Origen === 'EN_LINEA' && (int) $venta->Id_cliente === (int) session('cliente_id'), 404);
        return $venta->load(['pago', 'detalles.producto']);
    }

    /* ---------- 1. Confirmación de pedido ---------- */

    public function confirmar()
    {
        $carrito = Carrito::resumen();
        if (!$carrito['renglones']) {
            return redirect()->route('tienda.carrito')->with('err', 'Tu carrito está vacío.');
        }
        if ($carrito['hayFaltantes']) {
            return redirect()->route('tienda.carrito')->with('err', 'Ajusta las cantidades marcadas: ya no hay suficiente stock.');
        }
        return view('htmlv.pedido-confirmar', $carrito + ['cliente' => $this->cliente()]);
    }

    public function crear(Request $request)
    {
        $d = $this->validar($request, [
            'direccion' => 'required|string|max:100',
            'cp'        => ['required', 'regex:/^[0-9]{5}$/'],
        ]);
        $items = Carrito::items();
        if (!$items) return redirect()->route('tienda.carrito');

        try {
            $venta = DB::transaction(function () use ($d, $items) {
                $renglones = Inventario::renglones($items);
                $total = round(array_sum(array_column($renglones, 'subtotal')), 2);
                $ahora = now();

                $venta = Venta::create([
                    'Fecha' => $ahora->toDateString(), 'Fecha_hora' => $ahora, 'Realizacion' => 'Compra en línea',
                    'Total' => $total, 'Forma_pago' => Venta::PAGO_EN_LINEA, 'Origen' => 'EN_LINEA',
                    'Direccion_entrega' => $d['direccion'], 'CP_entrega' => $d['cp'],
                    'Id_cliente' => session('cliente_id'), 'Total_piezas' => array_sum(array_column($renglones, 'cantidad')),
                ]);
                foreach ($renglones as $r) {
                    DetalleVenta::create([
                        'Id_venta' => $venta->Id_venta, 'Id_producto' => $r['producto']->Id_producto, 'Talla' => $r['talla']->Talla,
                        'Cantidad' => $r['cantidad'], 'Precio_unitario' => $r['precio'], 'Subtotal' => $r['subtotal'],
                    ]);
                }
                // Las piezas se apartan desde ahora; si el pago se rechaza o se cancela, regresan
                Inventario::descontar($renglones);

                Pago::create([
                    'Folio' => Pago::nuevoFolio($venta->Id_venta), 'Id_venta' => $venta->Id_venta, 'Monto' => $total,
                    'Metodo' => Venta::PAGO_EN_LINEA, 'Estado' => 'PENDIENTE', 'Creado_en' => $ahora,
                ]);
                return $venta;
            });
        } catch (ValidationException $e) {
            return redirect()->route('tienda.carrito')->with('err', collect($e->errors())->flatten()->first());
        }

        Carrito::vaciar();
        return redirect()->route('tienda.pedido.pago', $venta);
    }

    /* ---------- 2. Información de pago ---------- */

    public function pago(Venta $venta)
    {
        $venta = $this->propio($venta);
        if ($venta->pago?->Comprobante) return redirect()->route('tienda.pedido.finalizado', $venta);
        return view('htmlv.pedido-pago', ['venta' => $venta, 'banco' => config('dav.banco')]);
    }

    /* ---------- 3. Confirmación de pago: subir ticket ---------- */

    public function comprobante(Venta $venta)
    {
        $venta = $this->propio($venta);
        if ($venta->pago?->Estado !== 'PENDIENTE') return redirect()->route('tienda.pedidos');
        return view('htmlv.pedido-comprobante', ['venta' => $venta]);
    }

    public function subirComprobante(Request $request, Venta $venta)
    {
        $venta = $this->propio($venta);
        abort_unless($venta->pago && $venta->pago->Estado === 'PENDIENTE', 404);

        $request->validate(
            ['ticket' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120', 'referencia' => 'nullable|string|max:100'],
            ['ticket.required' => 'Sube la foto o PDF de tu ticket de pago.', 'ticket.mimes' => 'El ticket debe ser una imagen (JPG, PNG, WEBP) o un PDF.', 'ticket.max' => 'El ticket no puede pesar más de 5 MB.']
        );

        $archivo = $request->file('ticket');
        $nombre = 'ticket_' . $venta->Id_venta . '_' . uniqid() . '.' . strtolower($archivo->guessExtension() ?: $archivo->getClientOriginalExtension());
        $archivo->move(public_path('img/comprobantes'), $nombre);
        if ($venta->pago->Comprobante) @unlink(public_path('img/comprobantes/' . basename($venta->pago->Comprobante)));

        $venta->pago->update(['Comprobante' => $nombre, 'Comprobante_en' => now(), 'Referencia' => $request->input('referencia')]);

        Notificacion::aRoles(['admin', 'contador'], 'PAGO',
            "Pago por validar: folio {$venta->pago->Folio} de {$venta->cliente->nombre_completo} por $" . number_format($venta->Total, 2) . '.',
            route('admin.validacion-pago.index'));

        return redirect()->route('tienda.pedido.finalizado', $venta);
    }

    /* ---------- 4. Compra finalizada ---------- */

    public function finalizado(Venta $venta)
    {
        return view('htmlv.pedido-finalizado', ['venta' => $this->propio($venta)]);
    }

    /** El cliente puede cancelar mientras no haya enviado su ticket; las piezas regresan al inventario. */
    public function cancelar(Venta $venta)
    {
        $venta = $this->propio($venta);
        if (!$venta->pago || $venta->pago->Estado !== 'PENDIENTE' || $venta->pago->Comprobante) {
            return redirect()->route('tienda.pedidos')->with('err', 'Este pedido ya no se puede cancelar desde aquí; comunícate con la tienda.');
        }
        DB::transaction(function () use ($venta) {
            Inventario::devolver($venta->detalles);
            $venta->delete(); // detalle y pago se borran en cascada
        });
        return redirect()->route('tienda.pedidos')->with('ok', 'Pedido cancelado.');
    }

    /* ---------- Mis pedidos (RF004: el cliente consulta el estado de su pago) ---------- */

    public function index()
    {
        $id = session('cliente_id');
        return view('htmlv.mis-pedidos', [
            'pedidos' => Venta::with(['pago', 'detalles'])->where('Origen', 'EN_LINEA')->where('Id_cliente', $id)->orderByDesc('Id_venta')->get(),
            'cotizaciones' => Cotizacion::withCount('detalles')->where('Id_cliente', $id)->orderByDesc('Id_cotizacion')->get(),
        ]);
    }

    /* ---------- RF009: cotización desde el carrito ---------- */

    public function cotizar()
    {
        $carrito = Carrito::resumen();
        if (!$carrito['renglones']) {
            return redirect()->route('tienda.carrito')->with('err', 'Tu carrito está vacío.');
        }
        $cliente = $this->cliente();

        $cot = DB::transaction(function () use ($carrito, $cliente) {
            $cot = Cotizacion::create(['Id_cliente' => $cliente->Id_cliente, 'Total' => $carrito['total'], 'Estado' => 'PENDIENTE', 'Creado_en' => now()]);
            foreach ($carrito['renglones'] as $r) {
                DetalleCotizacion::create([
                    'Id_cotizacion' => $cot->Id_cotizacion, 'Id_producto' => $r['producto']->Id_producto, 'Talla' => $r['talla'],
                    'Cantidad' => $r['cantidad'], 'Precio_unitario' => $r['precio'], 'Subtotal' => $r['subtotal'],
                ]);
            }
            return $cot;
        });

        // RF009.2: aviso al colaborador con los datos del cliente
        Notificacion::aRoles(['admin', 'vendedor'], 'COTIZACION',
            "Nueva cotización #{$cot->Id_cotizacion} de {$cliente->nombre_completo} (tel. {$cliente->Telefono}) por $" . number_format($cot->Total, 2) . " — {$carrito['piezas']} pieza(s).",
            route('admin.cotizaciones.show', $cot->Id_cotizacion));

        return redirect()->route('tienda.cotizacion', $cot)->with('ok', 'Cotización generada. Un colaborador se pondrá en contacto contigo.');
    }

    public function cotizacion(Cotizacion $cotizacion)
    {
        abort_unless((int) $cotizacion->Id_cliente === (int) session('cliente_id'), 404);
        return view('htmlv.cotizacion', ['cot' => $cotizacion->load('detalles.producto')]);
    }
}
