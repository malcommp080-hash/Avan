@extends('htmlv.layout')

@section('titulo', 'Inicio')

@section('contenido')
@php
  // Las 3 tarjetas de Figma: si hay producto con foto se usa; si no, la imagen de muestra
  $tarjetas = [
    ['Oferta del día', $oferta, 'ves1.jpg', route('tienda.catalogo', ['filtro' => 'ofertas'])],
    ['Menos de $100', $menos100, 'ves2.jpg', route('tienda.catalogo', ['filtro' => 'menos100'])],
    ['Más vendidos', $masVendido, 'ves3.jpg', route('tienda.catalogo', ['filtro' => 'vendidos'])],
  ];
@endphp

<div style="max-width:1100px;margin:30px auto 0;padding:0 16px">@include('htmlv.partials.avisos')</div>

<section class="portada">
  @foreach($tarjetas as [$titulo, $p, $muestra, $url])
  <a class="destacado" href="{{ $url }}">
    <span class="tit">{{ $titulo }}</span>
    <span class="img">
      <img src="{{ $p?->imagen_url ?? asset('img/' . $muestra) }}" alt="{{ $p?->Nombre ?? $titulo }}">
      @if($p)<span class="px">${{ rtrim(rtrim(number_format($p->tiene_mayoreo && $titulo === 'Oferta del día' ? $p->Precio_mayoreo : $p->Precioneto, 2), '0'), '.') }}</span>@endif
    </span>
  </a>
  @endforeach
</section>

<div style="text-align:center;margin:10px 0 40px">
  <a href="{{ route('tienda.categorias') }}" class="pildora azul">Ver todas las categorías →</a>
</div>
@endsection
