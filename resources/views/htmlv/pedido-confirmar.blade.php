@extends('htmlv.layout')

@section('titulo', 'Confirmación de pedido')
@section('sinPie', true)

@section('contenido')
<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Confirmación de pedido', 'volver' => route('tienda.carrito'), 'volverTexto' => 'Cerrar'])

  <div class="contenido">
    @include('htmlv.partials.avisos')

    <form action="{{ route('tienda.pedido.crear') }}" method="post">
      @csrf
      <div class="dos-col">
        <div style="display:flex;flex-direction:column;gap:22px">
          <div class="campo-pildora"><label for="direccion">Dirección de entrega</label>
            <input id="direccion" name="direccion" maxlength="100" required value="{{ old('direccion', $cliente->Direccion) }}" autocomplete="street-address"></div>
          <div class="campo-pildora"><span class="lbl">Piezas</span><span class="valor">{{ $piezas }}</span></div>
          <div class="campo-pildora"><label for="cp">C.P.</label>
            <input id="cp" name="cp" required inputmode="numeric" pattern="[0-9]{5}" maxlength="5" value="{{ old('cp', $cliente->CP) }}" autocomplete="postal-code"></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:22px">
          <div class="campo-pildora" style="align-items:center"><span class="lbl">Total a pagar</span><span class="valor" style="font-size:24px;font-weight:700">${{ number_format($total, 2) }}</span></div>
          <div class="miniaturas" aria-label="Artículos del pedido">
            @foreach($renglones as $r)
              @if($r['producto']->imagen_url)<img src="{{ $r['producto']->imagen_url }}" alt="{{ $r['producto']->Nombre }}" title="{{ $r['producto']->Nombre }} · {{ $r['talla'] }} × {{ $r['cantidad'] }}">
              @else<span class="sin" title="{{ $r['producto']->Nombre }} · {{ $r['talla'] }} × {{ $r['cantidad'] }}">👕</span>@endif
            @endforeach
          </div>
          <div style="font-size:13px;color:#1f3b63">
            @foreach($renglones as $r)<div>{{ $r['cantidad'] }} × {{ $r['producto']->Nombre }} ({{ $r['talla'] === 'UNI' ? 'única' : $r['talla'] }}) — ${{ number_format($r['subtotal'], 2) }}@if($r['mayoreo']) <b>mayoreo</b>@endif</div>@endforeach
          </div>
        </div>
      </div>

      <div class="acciones-centro">
        <a href="{{ route('tienda.carrito') }}" class="pildora">Cancelar</a>
        <button type="submit" class="pildora">Quiero mi pedido</button>
      </div>
    </form>
  </div>
</div>
@endsection
