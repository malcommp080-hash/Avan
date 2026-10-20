@extends('htmlv.layout')

@section('titulo', 'Mi carrito')

@section('contenido')
<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Mi carrito', 'volver' => url()->previous() !== url()->current() ? url()->previous() : route('tienda.catalogo'), 'volverTexto' => 'Seguir comprando'])

  <div class="contenido">
    @include('htmlv.partials.avisos')

    @if(!$renglones)
      <div class="vacio">
        <p style="font-size:42px;margin:0" aria-hidden="true">🛒</p>
        <p style="font-size:18px;margin:6px 0">Tu carrito está vacío.</p>
        <a class="pildora azul" href="{{ route('tienda.categorias') }}" style="margin-top:12px">Explorar categorías</a>
      </div>
    @else
    <div class="carrito">
      <section class="lista-carrito" aria-label="Artículos">
        @foreach($renglones as $r)
        @php $p = $r['producto']; @endphp
        <div class="item">
          <a class="mini" href="{{ route('tienda.producto', $p) }}">@if($p->imagen_url)<img src="{{ $p->imagen_url }}" alt="">@else<span aria-hidden="true">👕</span>@endif</a>
          <div>
            <h3><a href="{{ route('tienda.producto', $p) }}">{{ $p->Nombre }}</a></h3>
            <div class="meta">Talla {{ $r['talla'] === 'UNI' ? 'única' : $r['talla'] }}@if($p->Color) · {{ $p->Color }}@endif · ${{ number_format($r['precio'], 2) }} c/u</div>
            @if($r['falta'])<div class="falta">Sólo quedan {{ $r['stock'] }} pieza(s) en esta talla. Ajusta la cantidad.</div>@endif
            <div class="controles">
              <form action="{{ route('tienda.carrito.actualizar') }}" method="post" class="form-cant">
                @csrf @method('PATCH')
                <input type="hidden" name="clave" value="{{ $r['clave'] }}">
                <div class="cantidad">
                  <button type="submit" name="cantidad" value="{{ $r['cantidad'] - 1 }}" aria-label="Quitar una">−</button>
                  <input type="number" name="cantidad_manual" value="{{ $r['cantidad'] }}" min="0" max="{{ max($r['stock'], 1) }}" aria-label="Cantidad de {{ $p->Nombre }}">
                  <button type="submit" name="cantidad" value="{{ $r['cantidad'] + 1 }}" aria-label="Agregar una" @disabled($r['cantidad'] >= $r['stock'])>+</button>
                </div>
              </form>
              <form action="{{ route('tienda.carrito.quitar') }}" method="post">
                @csrf @method('DELETE')
                <input type="hidden" name="clave" value="{{ $r['clave'] }}">
                <button type="submit" class="quitar">Quitar</button>
              </form>
            </div>
          </div>
          <div class="importe">
            ${{ number_format($r['subtotal'], 2) }}
            @if($r['mayoreo'])<small class="may">Precio de mayoreo</small>@endif
          </div>
        </div>
        @endforeach
      </section>

      <aside class="resumen-caja" aria-label="Resumen">
        <h2>Resumen</h2>
        <div class="fila"><span>Piezas</span><span>{{ $piezas }}</span></div>
        <div class="fila total"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>

        @if($hayFaltantes)
          <button class="pildora azul" disabled>Pagar</button>
          <p class="nota" style="color:var(--error)">Ajusta las cantidades marcadas para continuar.</p>
        @else
          <a href="{{ route('tienda.pedido.confirmar') }}" class="pildora azul">Pagar</a>
        @endif
        <form action="{{ route('tienda.cotizar') }}" method="post">@csrf
          <button type="submit" class="pildora">Generar cotización</button>
        </form>
        <p class="nota">¿Compras por mayoreo o quieres apartar? Genera una cotización y un colaborador te contactará.@unless(session('cliente_id')) Para pagar o cotizar necesitas <a href="{{ route('tienda.login') }}">iniciar sesión</a>.@endunless</p>
      </aside>
    </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Al escribir una cantidad y salir del campo se guarda sola
  // (Enter también, sin que cuente como clic en el botón "−", que es el primero del formulario)
  document.querySelectorAll('.form-cant input[type=number]').forEach(i => {
    const guardar = () => { i.form.querySelectorAll('button[name=cantidad]').forEach(b => b.disabled = true); i.form.submit(); };
    i.addEventListener('change', guardar);
    i.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); guardar(); } });
  });
</script>
@endpush
