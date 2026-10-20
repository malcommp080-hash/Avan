<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restaurar Base de Datos — Sistema DAV</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

  @include('htmla.partials.sidebar')

  <!-- MAIN -->
  <div id="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <button class="tb-toggle" onclick="toggleSB()"><i class="fas fa-bars"></i></button>
      <div class="tb-title">Restaurar Base de Datos</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Restaurar Base de Datos</span>
      </div>
      <div class="tb-right">
        @include('htmla.partials.topbar-acciones')
      </div>
    </div>
    <!-- CONTENT START -->
    
    <div id="content">

@if(session('ok'))<div class="alert alert-ok"><i class="fas fa-check-circle"></i> {{ session('ok') }}</div>@endif
@if(session('err'))<div class="alert alert-err"><i class="fas fa-exclamation-circle"></i> {{ session('err') }}</div>@endif
@if($errors->any())<div class="alert alert-err"><i class="fas fa-exclamation-circle"></i><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>@endif

<div class="ptitle">Restaurar Base de Datos</div>
<div class="psub">Importa un archivo .sql para restaurar los datos</div>

<div class="alert alert-warn" style="margin-bottom:18px">
  <i class="fas fa-exclamation-triangle"></i>
  <strong>Advertencia:</strong> Esta acción sobreescribirá la base de datos actual. No se puede deshacer.
</div>

<div class="fcard" style="max-width:500px">
  <form action="{{ route('admin.restaurar.store') }}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Se sobreescribirá la base de datos actual. ¿Continuar?')">
    @csrf
    <div class="fg" style="margin-bottom:16px">
      <label>Selecciona un archivo .sql</label>
      <input class="fc" type="file" name="archivo" accept=".sql" required>
    </div>
    <div style="display:flex;gap:10px">
      <button type="submit" class="btn btn-orange"><i class="fas fa-upload"></i> Restaurar</button>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Cancelar</a>
    </div>
  </form>
</div>

    </div><!-- end #content -->
  </div><!-- end #main -->

  <script>
  function toggleSB(){
    document.getElementById('sb').classList.toggle('col');
    document.body.classList.toggle('col-mode');
  }
  </script>
</body>
</html>
