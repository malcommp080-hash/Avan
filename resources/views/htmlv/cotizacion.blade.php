@extends('htmlv.layout')

@section('titulo', 'Cotización #' . $cot->Id_cotizacion)

@section('contenido')
<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Cotización #' . $cot->Id_cotizacion, 'volver' => route('tienda.pedidos')])

  <div class="contenido" style="max-width:860px">
    @include('htmlv.partials.avisos')

    <div class="lista-carrito">
      @foreach($cot->detalles as $d)
      <div class="item">
        <div class="mini">@if($d->producto?->imagen_url)<img src="{{ $d->producto->imagen_url }}" alt="">@else<span>👕</span>@endif</div>
        <div>
          <h3>{{ $d->producto?->Nombre ?? 'Producto' }}</h3>
          <div class="meta">Talla {{ $d->Talla === 'UNI' ? 'única' : $d->Talla }} · {{ $d->Cantidad }} × ${{ number_format($d->Precio_unitario, 2) }}</div>
        </div>
        <div class="importe">${{ number_format($d->Subtotal, 2) }}</div>
      </div>
      @endforeach
      <div class="item" style="grid-template-columns:1fr auto"><strong style="font-size:18px">Total cotizado</strong><div class="importe" style="font-size:22px">${{ number_format($cot->Total, 2) }}</div></div>
    </div>

    <div class="alerta ok" style="margin-top:20px">
      @if($cot->Estado === 'ATENDIDA')
        ✅ Un colaborador ya atendió tu cotización el {{ $cot->Atendida_en?->format('d/m/Y') }}.
      @else
        ⏳ Enviamos tu cotización a un colaborador; te contactará al teléfono de tu cuenta. Los precios pueden cambiar según existencias.
      @endif
    </div>
    <div style="text-align:center;margin-top:10px"><a href="{{ route('tienda.carrito') }}" class="pildora">Ir a mi carrito</a></div>
  </div>
</div>
@endsection
