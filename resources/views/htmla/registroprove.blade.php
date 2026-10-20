<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Proveedores — Sistema DAV</title>
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
      <div class="tb-title">Registro de Proveedores</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Registro de Proveedores</span>
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

<div class="ptitle">Registro de Proveedores</div>
<div class="psub">{{ $saludo }} — Administra tu catálogo de proveedores</div>

<div class="fcard" style="margin-bottom:20px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-clipboard-list" style="color:var(--accent3)"></i> {{ ($editar ?? null) ? 'Modificar proveedor' : 'Agregar nuevo proveedor' }}</div>
  @php $e = $editar ?? null; @endphp
  <form action="{{ $e ? route('admin.proveedores.update', $e) : route('admin.proveedores.store') }}" method="post">
    @csrf
    @if($e) @method('PUT') @endif
    <div class="fgrid">
      <div class="fg"><label>Nombre *</label><input class="fc" type="text" name="nombre" maxlength="20" placeholder="Nombre" value="{{ old('nombre', $e->Nombre ?? '') }}" required></div>
      <div class="fg"><label>Apellido Paterno *</label><input class="fc" type="text" name="ap_paterno" maxlength="20" placeholder="Apellido" value="{{ old('ap_paterno', $e->Ap_paterno ?? '') }}" required></div>
      <div class="fg"><label>Teléfono *</label><input class="fc" type="text" name="telefono" placeholder="10 dígitos" maxlength="10" value="{{ old('telefono', $e->Telefono ?? '') }}" required></div>
      <div class="fg"><label>Dirección *</label><input class="fc" type="text" name="direccion" maxlength="40" placeholder="Dirección completa" value="{{ old('direccion', $e->Direccion ?? '') }}" required></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $e ? 'Actualizar proveedor' : 'Guardar proveedor' }}</button>
    @if($e)<a href="{{ route('admin.proveedores.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancelar edición</a>@endif
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-clipboard-list" style="color:var(--accent3)"></i> Lista de Proveedores</div></div>
  <table>
    <thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Dirección</th><th>Modificar</th><th>Eliminar</th></tr></thead>
    <tbody>
      @foreach($proveedores as $p)
      <tr><td>{{ $p->Id_proovedor }}</td><td>{{ $p->Nombre }}</td><td>{{ $p->Ap_paterno }}</td><td>{{ $p->Telefono }}</td><td>{{ $p->Direccion }}</td>
        <td><a href="{{ route('admin.proveedores.edit', $p) }}" class="ib ib-edit" title="Modificar"><i class="fas fa-pencil-alt"></i></a></td><td><form action="{{ route('admin.proveedores.destroy', $p) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td></tr>
      @endforeach
      @if($proveedores->isEmpty())<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">No hay proveedores registrados.</td></tr>@endif
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
