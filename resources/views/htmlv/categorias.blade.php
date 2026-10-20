@extends('htmlv.layout')

@section('titulo', 'Categorías')

@section('contenido')
<div class="panel cat-pagina" style="padding-top:0">
  <div style="background:var(--azul);padding:26px 0 1px">
    @include('htmlv.partials.barra', ['titulo' => 'Categorías', 'volver' => route('tienda.inicio')])
  </div>

  @include('htmlv.partials.avisos')

  <nav class="cat-lista" aria-label="Categorías">
    @foreach($categorias as $c)
    <a class="pildora" href="{{ route('tienda.catalogo', ['categoria' => $c->Id_categoria]) }}">
      {{ $c->Nombre }}
      <small>{{ $c->productos_count }} {{ $c->productos_count === 1 ? 'modelo' : 'modelos' }}</small>
    </a>
    @endforeach
    <a class="pildora" href="{{ route('tienda.catalogo') }}">Ver todo<small>catálogo completo</small></a>
  </nav>
</div>
@endsection
