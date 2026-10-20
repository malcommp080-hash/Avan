<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios / Empleados — Sistema DAV</title>
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
      <div class="tb-title">Usuarios / Empleados</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Usuarios / Empleados</span>
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

<div class="ptitle">Usuarios / Empleados</div>
<div class="psub">{{ $saludo }} — Gestiona los accesos al sistema</div>

<div class="fcard" style="margin-bottom:20px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-user-plus" style="color:var(--accent2)"></i> {{ ($editar ?? null) ? 'Modificar usuario/empleado' : 'Registrar nuevo usuario/empleado' }}</div>
  @php $e = $editar ?? null; @endphp
  <form action="{{ $e ? route('admin.usuarios.update', $e) : route('admin.usuarios.store') }}" method="post">
    @csrf
    @if($e) @method('PUT') @endif
    <div class="fgrid">
      <div class="fg"><label>Nombre *</label><input class="fc" type="text" name="nombre" maxlength="20" placeholder="Nombre" value="{{ old('nombre', $e->Nombre ?? '') }}" required></div>
      <div class="fg"><label>Apellido Paterno *</label><input class="fc" type="text" name="ap_paterno" maxlength="20" placeholder="Apellido" value="{{ old('ap_paterno', $e->Ap_paterno ?? '') }}" required></div>
      <div class="fg"><label>Teléfono *</label><input class="fc" type="text" name="telefono" placeholder="10 dígitos" maxlength="10" value="{{ old('telefono', $e->Telefono ?? '') }}" required></div>
      <div class="fg"><label>Dirección *</label><input class="fc" type="text" name="direccion" maxlength="40" placeholder="Dirección" value="{{ old('direccion', $e->Direccion ?? '') }}" required></div>
      <div class="fg"><label>Contraseña {{ $e ? '(vacío = no cambiar)' : '* (mín. 6 car.)' }}</label><input class="fc" type="password" name="contrasena" placeholder="••••••••" minlength="6" {{ $e ? '' : 'required' }}></div>
      <div class="fg"><label>Rol *</label>
        <select class="fc" name="rol" required>
          @foreach(['admin' => 'Administrador', 'vendedor' => 'Vendedor', 'almacen' => 'Almacén', 'contador' => 'Contador'] as $val => $txt)
          <option value="{{ $val }}" @selected(old('rol', $e->rol ?? 'vendedor') == $val)>{{ $txt }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $e ? 'Actualizar empleado' : 'Guardar empleado' }}</button>
    @if($e)<a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancelar edición</a>@endif
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-user-gear" style="color:var(--accent2)"></i> Empleados registrados</div></div>
  <table>
    <thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Dirección</th><th>Rol</th><th>Editar</th><th>Eliminar</th></tr></thead>
    <tbody>
      @foreach($empleados as $emp)
      @php $rc = ['admin' => '#f75e5e', 'vendedor' => '#4f8ef7', 'almacen' => '#22d3a5', 'contador' => '#f7c948'][$emp->rol] ?? '#7a8099'; @endphp
      <tr><td>{{ $emp->Id_empleado }}</td><td>{{ $emp->Nombre }}</td><td>{{ $emp->Ap_paterno }}</td><td>{{ $emp->Telefono }}</td><td>{{ $emp->Direccion }}</td>
        <td><span style="background:{{ $rc }}22;color:{{ $rc }};padding:3px 12px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase">{{ $emp->rol }}</span></td>
        <td><a href="{{ route('admin.usuarios.edit', $emp) }}" class="ib ib-edit" title="Modificar"><i class="fas fa-pencil-alt"></i></a></td><td><form action="{{ route('admin.usuarios.destroy', $emp) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td></tr>
      @endforeach
      @if($empleados->isEmpty())<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No hay empleados registrados.</td></tr>@endif
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
