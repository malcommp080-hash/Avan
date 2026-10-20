<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo') — Dav Tienda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/tienda.css') }}">
</head>
<body class="auth">
  <header class="auth-enc">
    <a href="{{ route('tienda.inicio') }}"><img src="{{ asset('img/logo.jpeg') }}" alt=""> Dav Tienda</a>
  </header>

  <main class="auth-main">
    <h1>@yield('titulo')</h1>
    @hasSection('subtitulo')<p class="sub">@yield('subtitulo')</p>@endif

    @if(session('ok'))<div class="auth-aviso ok" role="status">{{ session('ok') }}</div>@endif
    @if(session('err'))<div class="auth-aviso" role="alert">{{ session('err') }}</div>@endif
    @if($errors->any())<div class="auth-aviso" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @yield('contenido')
  </main>
</body>
</html>
