@extends('htmlv.layout')

@section('titulo', 'Información de pago')
@section('sinPie', true)

@section('contenido')
<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Información de pago', 'volver' => route('tienda.pedidos'), 'volverTexto' => 'Cerrar'])

  <div class="contenido">
    @include('htmlv.partials.avisos')

    <p style="text-align:center;margin:-6px 0 24px;font-size:16px">
      Pedido <strong>{{ $venta->pago->Folio }}</strong> · Deposita o transfiere <strong style="font-size:20px">${{ number_format($venta->Total, 2) }}</strong> y después sube tu ticket.
    </p>

    <div class="dos-col">
      <div class="cuadro">Núm. de cuenta para depósitos en efectivo:<strong>{{ $banco['cuenta'] }}</strong></div>
      <div class="cuadro">CLABE:<strong>{{ $banco['clabe'] }}</strong></div>
      <div class="cuadro">Banco:<strong>{{ $banco['banco'] }}</strong></div>
      <div class="cuadro">A nombre de:<strong>{{ $banco['titular'] }}</strong></div>
    </div>
    <p style="text-align:center;font-size:13.5px;color:#1f3b63;margin-top:18px">Escribe el folio <b>{{ $venta->pago->Folio }}</b> como concepto o referencia. Tus piezas quedan apartadas mientras validamos tu pago.</p>

    <div class="acciones-centro">
      <form action="{{ route('tienda.pedido.cancelar', $venta) }}" method="post" onsubmit="return confirm('¿Cancelar este pedido? Las piezas se liberarán.')">
        @csrf @method('DELETE')
        <button type="submit" class="pildora">Cancelar</button>
      </form>
      <a href="{{ route('tienda.pedido.comprobante', $venta) }}" class="pildora">Ya pagué, subir ticket</a>
    </div>
  </div>
</div>
@endsection
