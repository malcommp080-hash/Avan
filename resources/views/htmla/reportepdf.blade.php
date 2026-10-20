<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte PDF — Sistema DAV</title>
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
      <div class="tb-title">Reporte PDF</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Reporte PDF</span>
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

<div class="ptitle">Reporte PDF</div>
<div class="psub">Genera un reporte en PDF con el listado completo de clientes</div>

<div class="fcard" style="max-width:520px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px"><i class="fas fa-file-pdf" style="color:var(--red)"></i> Reporte de Clientes</div>
  <div style="font-size:12.5px;color:var(--muted);margin-bottom:18px">Incluye ID, nombre, teléfono, dirección y código postal de todos los clientes registrados, con fecha y hora de generación.</div>
  <a href="{{ route('admin.reporte.clientes') }}" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Generar reporte PDF</a>
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
