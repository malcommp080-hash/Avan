<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo', 'Tienda') — Distribuidora David</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/tienda.css') }}">
  @stack('head')
</head>
<body>
@php $piezasCarrito = \App\Services\Carrito::piezas(); @endphp

<header class="enc">
  <div class="enc-fila">
    <a href="{{ route('tienda.inicio') }}" class="enc-logo" title="Inicio"><img src="{{ asset('img/logo.jpeg') }}" alt="Distribuidora David"></a>

    <form class="buscador" action="{{ route('tienda.catalogo') }}" method="get" role="search">
      <input type="search" name="q" placeholder="Buscar" value="{{ request('q') }}" aria-label="Buscar productos" maxlength="60">
      <button type="submit" aria-label="Buscar"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></button>
    </form>

    <a href="{{ route('tienda.carrito') }}" class="enc-carrito" aria-label="Carrito ({{ $piezasCarrito }} piezas)">
      <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h2.2l2.4 12.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.5L21 7H5.4"/><circle cx="9.5" cy="20.5" r="1.4"/><circle cx="17.5" cy="20.5" r="1.4"/></svg>
      @if($piezasCarrito)<span class="globo">{{ $piezasCarrito > 99 ? '99+' : $piezasCarrito }}</span>@endif
    </a>
  </div>

  <nav class="enc-nav" aria-label="Principal">
    <a href="{{ route('tienda.categorias') }}" class="{{ request()->routeIs('tienda.categorias') ? 'act' : '' }}">Categorías</a>
    <a href="{{ route('tienda.catalogo', ['filtro' => 'ofertas']) }}" class="{{ request('filtro') === 'ofertas' ? 'act' : '' }}">Ofertas</a>
    @if(session('cliente_id'))
      <a href="{{ route('tienda.pedidos') }}" class="{{ request()->routeIs('tienda.pedidos') ? 'act' : '' }}">Mis pedidos</a>
      <form action="{{ route('tienda.logout') }}" method="post" style="display:inline">@csrf<button type="submit">Cerrar sesión</button></form>
    @else
      <a href="{{ route('tienda.login') }}">Iniciar sesión</a>
      <a href="{{ route('tienda.registro') }}">Crear Cuenta</a>
    @endif
  </nav>
</header>
<div class="espacio-nav"></div>

<main>
  @yield('contenido')
</main>

@hasSection('sinPie')
@else
<footer class="pie">
  <div class="caja">
    <div>Visítanos en:</div>
    <div>{{ config('dav.direccion') }}</div>
  </div>
</footer>
@endif

@if(session('cliente_id'))@include('partials.inactividad')@endif
@stack('scripts')
</body>
</html>
