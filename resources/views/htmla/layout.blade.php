<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo') — Sistema DAV</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('head')
</head>
<body>

  @include('htmla.partials.sidebar')

  <!-- MAIN -->
  <div id="main">
    <div class="topbar">
      <button class="tb-toggle" onclick="toggleSB()"><i class="fas fa-bars"></i></button>
      <div class="tb-title">@yield('titulo')</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">@yield('titulo')</span>
      </div>
      <div class="tb-right">
        @include('htmla.partials.topbar-acciones')
      </div>
    </div>

    <div id="content">
      @if(session('ok'))<div class="alert alert-ok"><i class="fas fa-check-circle"></i> {{ session('ok') }}</div>@endif
      @if(session('err'))<div class="alert alert-err"><i class="fas fa-exclamation-circle"></i> {{ session('err') }}</div>@endif
      @if($errors->any())<div class="alert alert-err"><i class="fas fa-exclamation-circle"></i><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>@endif

      @yield('contenido')
    </div>
  </div>

  <script>
  function toggleSB(){
    document.getElementById('sb').classList.toggle('col');
    document.body.classList.toggle('col-mode');
  }
  </script>
  @stack('scripts')
</body>
</html>
