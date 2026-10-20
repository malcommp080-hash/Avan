<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedidos de Clientes — Sistema DAV</title>
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
      <div class="tb-title">Pedidos de Clientes</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Pedidos de Clientes</span>
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

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:10px">
  <div><div class="ptitle">Pedidos de Clientes</div>
  <div class="psub">Ordenados por prioridad — alertas automáticas por tiempo de espera</div></div>
</div>

<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)"><span style="width:12px;height:12px;border-radius:50%;background:#ef4444;display:inline-block"></span> Urgente (+7 días)</div>
  <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)"><span style="width:12px;height:12px;border-radius:50%;background:#f97316;display:inline-block"></span> Alta prioridad (3–6 días)</div>
  <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)"><span style="width:12px;height:12px;border-radius:50%;background:#22c55e;display:inline-block"></span> Normal (0–2 días)</div>
</div>

<div class="fcard" style="margin-bottom:20px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-plus-circle" style="color:var(--accent)"></i> Registrar nuevo pedido</div>
  <form action="{{ route('admin.pedidos-clientes.store') }}" method="post">
    @csrf
    <div class="fgrid">
      <div class="fg"><label>Cliente *</label><select class="fc" name="id_cliente" required><option value="">— Selecciona cliente —</option>
        @foreach($clientes as $c)<option value="{{ $c->Id_cliente }}" @selected(old('id_cliente') == $c->Id_cliente)>{{ $c->Id_cliente }} - {{ $c->nombre_completo }}</option>@endforeach
      </select></div>
      <div class="fg"><label>Fecha del pedido *</label><input class="fc" type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" required></div>
      <div class="fg"><label>Total ($) *</label><input class="fc" type="number" step="0.01" min="0" name="total" placeholder="0.00" value="{{ old('total') }}" required></div>
      <div class="fg"><label>Total piezas *</label><input class="fc" type="number" min="0" name="total_piezas" placeholder="0" value="{{ old('total_piezas') }}" required></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar pedido</button>
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-box" style="color:var(--accent)"></i> Lista de Pedidos</div></div>
  <table>
    <thead><tr><th>#</th><th>Cliente</th><th>Teléfono</th><th>Fecha</th><th>Días espera</th><th>Total</th><th>Piezas</th><th>Prioridad</th><th>Acción</th></tr></thead>
    <tbody>
      @foreach($pedidos as $p)
      @php
        $color = ['URGENTE' => '#ef4444', 'ALTA' => '#f97316', 'NORMAL' => '#22c55e'][$p->prioridad];
        $icono = ['URGENTE' => '🚨', 'ALTA' => '🔶', 'NORMAL' => '✅'][$p->prioridad];
      @endphp
      <tr style="border-left:3px solid {{ $color }}">
        <td>{{ $p->Id_pedido }}</td><td><strong>{{ $p->cliente?->nombre_completo ?? '—' }}</strong></td><td>{{ $p->cliente?->Telefono }}</td><td>{{ $p->Fecha->format('Y-m-d') }}</td>
        <td><span style="background:{{ $color }}22;color:{{ $color }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700">{{ $p->dias }} {{ $p->dias == 1 ? 'día' : 'días' }}</span></td>
        <td>${{ number_format($p->Total, 2) }}</td><td>{{ $p->Totalpiezas }}</td>
        <td><span style="background:{{ $color }}22;color:{{ $color }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:.5px">{{ $icono }} {{ $p->prioridad }}</span></td>
        <td><form action="{{ route('admin.pedidos-clientes.destroy', $p) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td></tr>
      @endforeach
      @if($pedidos->isEmpty())<tr><td colspan="9" style="text-align:center;color:var(--muted);padding:24px">No hay pedidos registrados.</td></tr>@endif
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
