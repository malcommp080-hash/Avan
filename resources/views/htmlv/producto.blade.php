@extends('htmlv.layout')

@section('titulo', $producto->Nombre)

@section('contenido')
@php
  $tallas = $producto->tallas_ordenadas;
  $disponibles = $tallas->where('Stock', '>', 0);
  // Si sólo hay una talla con piezas se deja elegida
  $elegida = old('talla', $disponibles->count() === 1 ? $disponibles->first()->Talla : null);
  $colores = ['negro' => '#111', 'blanco' => '#fff', 'gris' => '#888', 'azul' => '#1e4fd8', 'marino' => '#14234b', 'rojo' => '#d32f2f', 'verde' => '#2e7d32', 'rosa' => '#ec6fa9', 'beige' => '#d8c3a5', 'cafe' => '#6d4c41', 'café' => '#6d4c41', 'amarillo' => '#f9c80e', 'morado' => '#7b3fa0', 'naranja' => '#f57c00', 'caqui' => '#a89f68'];
  $muestra = $colores[mb_strtolower(trim((string) $producto->Color))] ?? null;
@endphp

<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Catálogo David', 'volver' => route('tienda.catalogo', ['categoria' => $producto->Id_categoria]), 'volverTexto' => 'Cerrar'])

  <div class="contenido">
    @include('htmlv.partials.avisos')

    <article class="ficha">
      <div class="foto">
        @if($producto->imagen_url)<img src="{{ $producto->imagen_url }}" alt="{{ $producto->Nombre }}">@else<span style="font-size:80px;opacity:.3" aria-hidden="true">👕</span>@endif
      </div>

      <form action="{{ route('tienda.carrito.agregar') }}" method="post" id="formAgregar">
        @csrf
        <input type="hidden" name="id_producto" value="{{ $producto->Id_producto }}">

        <h2>{{ $producto->Nombre }}</h2>
        <div class="meta">{{ $producto->categoria?->Nombre }} · {{ $producto->segmento_texto }}</div>
        <div class="precio">${{ number_format($producto->Precioneto, 2) }}</div>
        @if($producto->Descripcion)<p class="desc">{{ $producto->Descripcion }}</p>@endif
        @if($producto->Color)
        <div class="color">@if($muestra)<span class="muestra" style="background:{{ $muestra }}"></span>@endif Color: {{ $producto->Color }}</div>
        @endif

        <div class="etiqueta" id="lblTalla">Talla</div>
        <div class="tallas" role="radiogroup" aria-labelledby="lblTalla">
          @foreach($tallas as $t)
          <input type="radio" name="talla" id="t{{ $loop->index }}" value="{{ $t->Talla }}" data-stock="{{ $t->Stock }}" @checked($elegida === $t->Talla) @disabled($t->Stock < 1) required>
          <label for="t{{ $loop->index }}" title="{{ $t->Stock < 1 ? 'Agotada' : $t->Stock . ' disponibles' }}">{{ $t->Talla === 'UNI' ? 'Única' : $t->Talla }}</label>
          @endforeach
        </div>
        <div class="stock" id="stock" aria-live="polite">{{ $disponibles->isEmpty() ? 'Agotado por ahora' : 'Elige tu talla para ver las piezas disponibles' }}</div>

        <div class="etiqueta">Cantidad</div>
        <div class="cantidad">
          <button type="button" data-paso="-1" aria-label="Quitar una">−</button>
          <input type="number" name="cantidad" id="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="{{ $disponibles->max('Stock') ?: 1 }}" inputmode="numeric" aria-label="Cantidad">
          <button type="button" data-paso="1" aria-label="Agregar una">+</button>
        </div>

        @if($producto->tiene_mayoreo)
        <div class="aviso-mayoreo" id="mayoreo">Precio mayoreo desde {{ $producto->Min_mayoreo }} piezas: <strong>${{ number_format($producto->Precio_mayoreo, 2) }}</strong> c/u (puedes combinar tallas)</div>
        @endif

        <div class="ficha-acciones">
          <button type="submit" class="pildora" @disabled($disponibles->isEmpty())>🛒 Agregar al carrito</button>
          <a href="{{ route('tienda.carrito') }}" class="pildora secundaria">Ver carrito</a>
        </div>
      </form>
    </article>

    @if($relacionados->isNotEmpty())
    <h2 class="seccion-titulo" style="text-align:center">También te puede gustar</h2>
    <div class="grid-prod">
      @foreach($relacionados as $p)
      <a class="tarjeta" href="{{ route('tienda.producto', $p) }}">
        <div class="foto">@if($p->imagen_url)<img src="{{ $p->imagen_url }}" alt="{{ $p->Nombre }}" loading="lazy">@else<span class="sin" aria-hidden="true">👕</span>@endif</div>
        <div class="info"><h3>{{ $p->Nombre }}</h3><div class="precio">${{ number_format($p->Precioneto, 2) }}</div></div>
      </a>
      @endforeach
    </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const form = document.getElementById('formAgregar');
  const cant = document.getElementById('cantidad');
  const stock = document.getElementById('stock');
  const tallas = form.querySelectorAll('input[name="talla"]');

  function actualizar() {
    const t = form.querySelector('input[name="talla"]:checked');
    if (t) {
      const s = parseInt(t.dataset.stock, 10);
      cant.max = s;
      if (parseInt(cant.value, 10) > s) cant.value = s;
      stock.textContent = `Stock disponible: ${s} ${s === 1 ? 'pieza' : 'piezas'}`;
    }
  }
  tallas.forEach(r => r.addEventListener('change', actualizar));
  form.querySelectorAll('[data-paso]').forEach(b => b.addEventListener('click', () => {
    const v = (parseInt(cant.value, 10) || 1) + parseInt(b.dataset.paso, 10);
    cant.value = Math.max(1, Math.min(parseInt(cant.max, 10) || 999, v));
  }));
  form.addEventListener('submit', e => {
    if (!form.querySelector('input[name="talla"]:checked')) {
      e.preventDefault();
      stock.textContent = 'Elige una talla antes de agregar al carrito.';
      stock.style.color = 'var(--error)';
    }
  });
  actualizar();
})();
</script>
@endpush
