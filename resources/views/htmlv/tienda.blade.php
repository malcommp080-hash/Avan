<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda</title>
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
</head>
<body>

<div class="contenedor">

    <header>
        <div class="top-header">
            <div class="logo">
                <img src="{{ asset('img/logo.jpg') }}" alt="Logo de Ventas">
            </div>

            <div class="barra-busqueda">
                <input type="text" placeholder="Buscar">
                <span>🔍</span>
            </div>
        </div>

        <nav>
            <a href="{{ route('tienda.categorias') }}">Categorías</a>
            <a href="#">Ofertas</a>
            <a href="#" onclick="document.getElementById('logout').submit();return false">Cerrar sesión ({{ $nombre }})</a>
            <form id="logout" action="{{ route('tienda.logout') }}" method="post" style="display:none">@csrf</form>
        </nav>
    </header>

    <section class="productos">

        <div class="producto">
            <div class="titulo">Oferta del día</div>
            <img src="{{ asset('img/ves1.jpg') }}" alt="">
        </div>

        <div class="producto">
            <div class="titulo">Menos de $100</div>
            <img src="{{ asset('img/ves2.jpg') }}" alt="">
            @if($precioMenos100 !== null)<div class="precio">${{ rtrim(rtrim(number_format($precioMenos100, 2), '0'), '.') }}</div>@endif
        </div>

        <div class="producto">
            <div class="titulo">Mas vendidos</div>
            <img src="{{ asset('img/ves3.jpg') }}" alt="">
            @if($precioMasVendido !== null)<div class="precio">${{ rtrim(rtrim(number_format($precioMasVendido, 2), '0'), '.') }}</div>@endif
        </div>

    </section>

    <footer>
        <p>Visítanos en:</p>
        <p>C. Reforma, San Lucas Atoyatenco,</p>
        <p>74053 San Martín Texmelucan de Labastida Puebla</p>
    </footer>

</div>

@include('partials.inactividad')
</body>
</html>
