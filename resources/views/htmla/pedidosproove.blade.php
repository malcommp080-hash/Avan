<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedidos a Proveedores — Sistema DAV</title>
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
      <div class="tb-title">Pedidos a Proveedores</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Pedidos a Proveedores</span>
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

<div class="ptitle">Pedidos a Proveedores</div>
<div class="psub">Registro de pedidos realizados a proveedores</div>

<div class="fcard" style="margin-bottom:20px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-truck-loading" style="color:var(--accent2)"></i> Registrar pedido a proveedor</div>
  <form action="{{ route('admin.pedidos-proveedores.store') }}" method="post">
    @csrf
    <div class="fgrid">
      <div class="fg"><label>Proveedor *</label><select class="fc" name="id_proovedor" required><option value="">— Selecciona proveedor —</option>
        @foreach($proveedores as $pr)<option value="{{ $pr->Id_proovedor }}" @selected(old('id_proovedor') == $pr->Id_proovedor)>{{ $pr->Id_proovedor }} - {{ $pr->Nombre }} {{ $pr->Ap_paterno }}</option>@endforeach
      </select></div>
      <div class="fg"><label>Fecha *</label><input class="fc" type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" required></div>
      <div class="fg"><label>Total ($) *</label><input class="fc" type="number" step="0.01" min="0" name="total" placeholder="0.00" value="{{ old('total') }}" required></div>
      <div class="fg"><label>Total piezas *</label><input class="fc" type="number" min="0" name="total_piezas" placeholder="0" value="{{ old('total_piezas') }}" required></div>
      <div class="fg"><label>Estado</label><select class="fc" name="estado">@foreach($estados as $es)<option value="{{ $es }}" @selected(old('estado', 'PENDIENTE') == $es)>{{ ucfirst(strtolower($es)) }}</option>@endforeach</select></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar pedido</button>
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-truck-loading" style="color:var(--accent2)"></i> Pedidos realizados</div></div>
  <table>
    <thead><tr><th>ID</th><th>Proveedor</th><th>Teléfono</th><th>Fecha</th><th>Total</th><th>Piezas</th><th>Estado</th><th>Eliminar</th></tr></thead>
    <tbody>
      @foreach($pedidos as $p)
      @php $c = ['PENDIENTE' => '#f7c948', 'ENVIADO' => '#4f8ef7', 'RECIBIDO' => '#22d3a5', 'CANCELADO' => '#f75e5e'][$p->estado] ?? '#7a8099'; @endphp
      <tr><td>#{{ $p->Id_venta }}</td>
        <td>@if($p->proveedor){{ $p->proveedor->Nombre }} {{ $p->proveedor->Ap_paterno }}@else<em style="color:var(--muted)">Sin asignar</em>@endif</td>
        <td>{{ $p->proveedor->Telefono ?? '—' }}</td><td>{{ $p->Fecha->format('Y-m-d') }}</td><td>${{ number_format($p->Total, 2) }}</td><td>{{ $p->Total_piezas }}</td>
        <td><span style="background:{{ $c }}22;color:{{ $c }};padding:3px 12px;border-radius:20px;font-size:11px;font-weight:800">{{ $p->estado }}</span></td>
        <td><form action="{{ route('admin.pedidos-proveedores.destroy', $p) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td></tr>
      @endforeach
      @if($pedidos->isEmpty())<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No hay pedidos a proveedores registrados.</td></tr>@endif
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
