<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventario — Sistema DAV</title>
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
      <div class="tb-title">Inventario</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Inventario</span>
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

<div class="ptitle">Inventario</div>
<div class="psub">{{ $saludo }} — Visualización del inventario actual</div>

<div class="stat-grid" style="margin-bottom:20px">
  <div class="sc sc-teal"><div><div class="sc-val">{{ $totalProductos }}</div><div class="sc-lbl">Productos distintos</div></div><i class="fas fa-boxes sc-icon"></i></div>
  <div class="sc sc-green"><div><div class="sc-val">{{ $totalStock }}</div><div class="sc-lbl">Unidades totales</div></div><i class="fas fa-warehouse sc-icon"></i></div>
  <div class="sc sc-amber"><div><div class="sc-val">${{ number_format($valor, 2) }}</div><div class="sc-lbl">Valor del inventario</div></div><i class="fas fa-dollar-sign sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $stockBajo }}</div><div class="sc-lbl">Stock bajo (&lt;10)</div></div><i class="fas fa-exclamation-triangle sc-icon"></i></div>
</div>

<div class="tcard">
  <div class="tcard-hdr">
    <div class="tcard-ttl"><i class="fas fa-warehouse" style="color:var(--accent3)"></i> Estado del inventario</div>
    <small style="color:var(--muted)">Ordenado: menor stock primero</small>
  </div>
  <table>
    <thead><tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Stock actual</th><th>Nivel</th><th>Precio venta</th><th>Valor stock</th><th>Ajustar</th></tr></thead>
    <tbody>
      @foreach($productos as $p)
      @php
        if ($p->Stock <= 0) { $c = '#ef4444'; $n = '🔴 Agotado'; }
        elseif ($p->Stock < 10) { $c = '#f97316'; $n = '🟠 Bajo'; }
        else { $c = '#22c55e'; $n = '🟢 Bueno'; }
      @endphp
      <tr><td>{{ $p->Id_producto }}</td><td><strong>{{ $p->Nombre }}</strong></td><td>{{ $p->categoria?->Nombre ?? '—' }}</td>
        <td><span style="font-size:18px;font-weight:800;color:{{ $c }}">{{ $p->Stock }}</span></td>
        <td><span style="background:{{ $c }}22;color:{{ $c }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700">{{ $n }}</span></td>
        <td>${{ number_format($p->Precioneto, 2) }}</td><td>${{ number_format($p->Stock * $p->Precioneto, 2) }}</td>
        <td><form action="{{ route('admin.inventario.update', $p) }}" method="post" style="display:flex;gap:6px;align-items:center">
          @csrf @method('PUT')
          <input class="fc" type="number" name="stock" value="{{ $p->Stock }}" min="0" style="width:70px;padding:5px 8px;font-size:13px">
          <button type="submit" class="btn btn-sm btn-primary" title="Guardar stock"><i class="fas fa-save"></i></button></form></td></tr>
      @endforeach
      @if($productos->isEmpty())<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No hay productos en inventario.</td></tr>@endif
    </tbody>
  </table>
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
