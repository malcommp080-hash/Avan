@extends('htmlv.layout')

@section('titulo', 'Mis pedidos')

@section('contenido')
<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Mis pedidos', 'volver' => route('tienda.inicio')])

  <div class="contenido" style="max-width:900px">
    @include('htmlv.partials.avisos')

    <h2 class="seccion-titulo">Compras</h2>
    @forelse($pedidos as $v)
    @php $p = $v->pago; @endphp
    <div class="pedido">
      <div>
        <h3>{{ $p?->Folio ?? '#' . $v->Id_venta }} · ${{ number_format($v->Total, 2) }}</h3>
        <div class="meta">{{ $v->fecha_texto }} · {{ $v->Total_piezas }} pieza(s) · Entrega en {{ $v->Direccion_entrega }}, C.P. {{ $v->CP_entrega }}</div>
        @if($p)<div style="margin-top:6px"><span class="estado {{ $p->Estado }}">{{ $p->estado_texto }}</span>
          @if($p->Validado_en)<span class="meta"> · {{ $p->Validado_en->format('d/m/Y H:i') }}</span>@endif</div>@endif
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        @if($p && $p->Estado === 'PENDIENTE' && !$p->Comprobante)
          <a href="{{ route('tienda.pedido.pago', $v) }}" class="pildora chica azul">Pagar</a>
        @elseif($p && $p->Estado === 'PENDIENTE')
          <a href="{{ route('tienda.pedido.comprobante', $v) }}" class="pildora chica">Cambiar ticket</a>
        @endif
        <a href="{{ route('tienda.pedido.finalizado', $v) }}" class="pildora chica">Ver</a>
      </div>
    </div>
    @empty
    <div class="vacio">Aún no tienes compras. <a href="{{ route('tienda.categorias') }}">Explora el catálogo</a>.</div>
    @endforelse

    <h2 class="seccion-titulo">Cotizaciones</h2>
    @forelse($cotizaciones as $c)
    <div class="pedido">
      <div>
        <h3>Cotización #{{ $c->Id_cotizacion }} · ${{ number_format($c->Total, 2) }}</h3>
        <div class="meta">{{ $c->Creado_en->format('d/m/Y H:i') }} · {{ $c->detalles_count }} artículo(s)</div>
        <div style="margin-top:6px"><span class="estado {{ $c->Estado }}">{{ $c->Estado === 'ATENDIDA' ? 'Atendida' : 'En espera de un colaborador' }}</span></div>
      </div>
      <a href="{{ route('tienda.cotizacion', $c) }}" class="pildora chica">Ver</a>
    </div>
    @empty
    <div class="vacio">No has generado cotizaciones. Puedes hacerlo desde tu <a href="{{ route('tienda.carrito') }}">carrito</a>.</div>
    @endforelse
  </div>
</div>
@endsection
