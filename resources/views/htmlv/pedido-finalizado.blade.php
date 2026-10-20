@extends('htmlv.layout')

@section('titulo', 'Compra finalizada')
@section('sinPie', true)

@section('contenido')
<div class="panel" style="padding-bottom:0">
  @include('htmlv.partials.barra', ['titulo' => 'Compra finalizada', 'volver' => route('tienda.pedidos'), 'volverTexto' => 'Regresar'])

  <div class="contenido">
    <div class="dos-col">
      <div style="display:flex;flex-direction:column;gap:18px">
        <div class="campo-pildora"><span class="valor">Dirección: {{ $venta->Direccion_entrega }}</span></div>
        <div class="campo-pildora"><span class="valor">Piezas: {{ $venta->Total_piezas }}</span></div>
        <div class="campo-pildora"><span class="valor">Total a pagar: <b>${{ number_format($venta->Total, 2) }}</b></span></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:18px">
        <div class="campo-pildora"><span class="valor">C.P.: {{ $venta->CP_entrega }}</span></div>
        <div class="miniaturas">
          @foreach($venta->detalles as $d)
            @if($d->producto?->imagen_url)<img src="{{ $d->producto->imagen_url }}" alt="{{ $d->producto->Nombre }}">@else<span class="sin">👕</span>@endif
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="banda-blanca">
    @if($venta->pago?->Comprobante)
      Felicidades por su compra, en breve se validará su pedido. :)
      <div style="font-size:14px;color:var(--tenue);margin-top:6px">Folio {{ $venta->pago->Folio }} · Puedes ver el estado en <a href="{{ route('tienda.pedidos') }}">Mis pedidos</a>.</div>
    @else
      Tu pedido está apartado. <a href="{{ route('tienda.pedido.comprobante', $venta) }}">Sube tu ticket de pago</a> para que lo validemos.
    @endif
  </div>
  <div style="height:40px"></div>
</div>
@endsection
