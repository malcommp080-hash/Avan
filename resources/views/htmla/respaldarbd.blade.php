<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Respaldar Base de Datos — Sistema DAV</title>
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
      <div class="tb-title">Respaldar Base de Datos</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Respaldar Base de Datos</span>
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

<div class="ptitle">Respaldar Base de Datos</div>
<div class="psub">Genera y descarga una copia de seguridad completa de la base de datos</div>

<div class="alert alert-warn" style="margin-bottom:18px">
  <i class="fas fa-info-circle"></i> El respaldo incluye la estructura y todos los datos de las tablas del sistema (clientes, ventas, prendas, productos, empleados, proveedores, etc).
</div>

<div class="fcard" style="max-width:520px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px"><i class="fas fa-database" style="color:var(--accent)"></i> Respaldo completo (.sql)</div>
  <div style="font-size:12.5px;color:var(--muted);margin-bottom:18px">Se descargará un archivo <span class="bg-zip">respaldo_AAAA-MM-DD_HH-MM-SS.sql</span></div>
  <div style="display:flex;gap:10px">
    <a href="{{ route('admin.respaldo.descargar') }}" class="btn btn-teal"><i class="fas fa-download"></i> Generar y descargar respaldo</a>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
  </div>
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
