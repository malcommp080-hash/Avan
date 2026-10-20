<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos — Sistema DAV</title>
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
      <div class="tb-title">Productos</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Productos</span>
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

<div class="ptitle">Productos</div>
<div class="psub">{{ $saludo }} — Catálogo y registro de productos</div>

<div class="stat-grid" style="margin-bottom:20px">
  <div class="sc sc-teal"><div><div class="sc-val">{{ $totalProductos }}</div><div class="sc-lbl">Productos registrados</div></div><i class="fas fa-box-open sc-icon"></i></div>
  <div class="sc sc-amber"><div><div class="sc-val">{{ $totalStock }}</div><div class="sc-lbl">Unidades en stock total</div></div><i class="fas fa-warehouse sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $stockBajo }}</div><div class="sc-lbl">Stock bajo (&lt;10)</div></div><i class="fas fa-exclamation-triangle sc-icon"></i></div>
</div>

<div class="fcard" style="margin-bottom:20px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-plus-circle" style="color:var(--accent)"></i> {{ ($editar ?? null) ? 'Modificar producto' : 'Agregar nuevo producto' }}</div>
  @php $e = $editar ?? null; @endphp
  <form action="{{ $e ? route('admin.productos.update', $e) : route('admin.productos.store') }}" method="post">
    @csrf
    @if($e) @method('PUT') @endif
    <div class="fgrid">
      <div class="fg"><label>Nombre del producto *</label><input class="fc" type="text" name="nombre" maxlength="20" placeholder="Nombre" value="{{ old('nombre', $e->Nombre ?? '') }}" required></div>
      <div class="fg"><label>Stock {{ $e ? '' : 'inicial' }} *</label><input class="fc" type="number" min="0" name="stock" placeholder="0" value="{{ old('stock', $e->Stock ?? '') }}" required></div>
      <div class="fg"><label>Categoría *</label>
        <select class="fc" name="id_categoria" required><option value="">— Categoría —</option>
          @foreach($categorias as $cat)<option value="{{ $cat->Id_categoria }}" @selected(old('id_categoria', $e->Id_categoria ?? '') == $cat->Id_categoria)>{{ $cat->Nombre }}</option>@endforeach
        </select>
      </div>
      <div class="fg"><label>Precio proveedor ($) *</label><input class="fc" type="number" step="0.01" min="0" name="preciopro" placeholder="0.00" value="{{ old('preciopro', $e->Preciopro ?? '') }}" required></div>
      <div class="fg"><label>Precio neto / venta ($) *</label><input class="fc" type="number" step="0.01" min="0" name="precioneto" placeholder="0.00" value="{{ old('precioneto', $e->Precioneto ?? '') }}" required></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $e ? 'Actualizar producto' : 'Guardar producto' }}</button>
    @if($e)<a href="{{ route('admin.productos.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancelar edición</a>@endif
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-box-open" style="color:var(--accent)"></i> Catálogo de Productos</div></div>
  <table>
    <thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Precio proveedor</th><th>Precio venta</th><th>Margen</th><th>Modificar</th><th>Eliminar</th></tr></thead>
    <tbody>
      @foreach($productos as $p)
      @php
        $sc = $p->Stock <= 0 ? '#ef4444' : ($p->Stock < 10 ? '#f97316' : '#22c55e');
        $margen = $p->Precioneto - $p->Preciopro;
        $pct = $p->Preciopro > 0 ? ($margen / $p->Preciopro) * 100 : 0;
      @endphp
      <tr><td>{{ $p->Id_producto }}</td><td><strong>{{ $p->Nombre }}</strong></td><td>{{ $p->categoria?->Nombre ?? '—' }}</td>
        <td><span style="background:{{ $sc }}22;color:{{ $sc }};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700">{{ $p->Stock }} uds</span></td>
        <td>${{ number_format($p->Preciopro, 2) }}</td><td>${{ number_format($p->Precioneto, 2) }}</td>
        <td><span style="color:{{ $margen >= 0 ? '#22c55e' : '#ef4444' }};font-weight:700">{{ $margen >= 0 ? '+' : '-' }}${{ number_format(abs($margen), 2) }} <small style="opacity:.7">({{ number_format($pct, 1) }}%)</small></span></td>
        <td><a href="{{ route('admin.productos.edit', $p) }}" class="ib ib-edit" title="Modificar"><i class="fas fa-pencil-alt"></i></a></td><td><form action="{{ route('admin.productos.destroy', $p) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td></tr>
      @endforeach
      @if($productos->isEmpty())<tr><td colspan="9" style="text-align:center;color:var(--muted);padding:24px">No hay productos registrados.</td></tr>@endif
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
