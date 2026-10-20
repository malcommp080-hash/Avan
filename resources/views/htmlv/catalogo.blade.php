@extends('htmlv.layout')

@section('titulo', $titulo)

@section('contenido')
@php
  // Conserva los demás filtros al cambiar de segmento
  $base = array_filter(['categoria' => $filtros['categoria'] ?? null, 'q' => $filtros['q'] ?? null, 'filtro' => $filtros['filtro'] ?? null]);
  $actual = $filtros['segmento'] ?? null;
  $iconos = ['CABALLERO' => '👨', 'DAMA' => '👩', 'NINO' => '👦', 'NINA' => '👧'];
@endphp

<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => $titulo, 'volver' => $categoria ? route('tienda.categorias') : route('tienda.inicio')])

  <div class="contenido">
    @include('htmlv.partials.avisos')

    {{-- En lugar de 3 pantallas (Hombre/Mujer → Adulto/Niño → producto) un clic filtra --}}
    @if($segmentos->isNotEmpty())
    <nav class="chips" aria-label="Filtrar por">
      <a href="{{ route('tienda.catalogo', $base) }}" class="chip {{ !$actual ? 'act' : '' }}">Todos</a>
      @foreach($segmentos as $val => $txt)
      <a href="{{ route('tienda.catalogo', $base + ['segmento' => $val]) }}" class="chip {{ $actual === $val ? 'act' : '' }}"><span aria-hidden="true">{{ $iconos[$val] ?? '' }}</span> {{ $txt }}</a>
      @endforeach
    </nav>
    @endif

    <p class="resultado">{{ $productos->count() }} {{ $productos->count() === 1 ? 'modelo disponible' : 'modelos disponibles' }}</p>

    @if($productos->isEmpty())
      <div class="vacio">
        <p style="font-size:18px;margin:0 0 6px">No encontramos productos con esos filtros.</p>
        <p style="color:var(--tenue);margin:0 0 18px">Prueba con otra opción o revisa todo el catálogo.</p>
        <a class="pildora azul" href="{{ route('tienda.catalogo') }}">Ver todo el catálogo</a>
      </div>
    @else
      <div class="grid-prod">
        @foreach($productos as $p)
        <a class="tarjeta" href="{{ route('tienda.producto', $p) }}">
          <div class="foto">
            @if($p->imagen_url)<img src="{{ $p->imagen_url }}" alt="{{ $p->Nombre }}" loading="lazy">@else<span class="sin" aria-hidden="true">👕</span>@endif
          </div>
          <div class="info">
            <h3>{{ $p->Nombre }}</h3>
            <div class="meta">{{ $p->segmento_texto }}@if($p->Color) · {{ $p->Color }}@endif · Tallas {{ $p->tallas_ordenadas->where('Stock', '>', 0)->pluck('Talla')->implode(', ') }}</div>
            <div class="precio">${{ number_format($p->Precioneto, 2) }}</div>
            @if($p->tiene_mayoreo)<div class="mayoreo">Mayoreo +{{ $p->Min_mayoreo }} pz: ${{ number_format($p->Precio_mayoreo, 2) }}</div>@endif
          </div>
        </a>
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection
