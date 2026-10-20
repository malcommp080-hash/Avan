<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Clientes — Sistema DAV</title>
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
      <div class="tb-title">Registro de Clientes</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Registro de Clientes</span>
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

<div class="ptitle">Registro de Clientes</div>
<div class="psub">{{ $saludo }}</div>

<div class="fcard">
  @php $e = $editar ?? null; @endphp
  <form action="{{ $e ? route('admin.clientes.update', $e) : route('admin.clientes.store') }}" method="post">
    @csrf
    @if($e) @method('PUT') @endif
    <div class="fgrid">
      <div class="fg"><label>Nombre *</label><input class="fc" type="text" name="nombre" maxlength="20" placeholder="Nombre" value="{{ old('nombre', $e->Nombre ?? '') }}" required></div>
      <div class="fg"><label>Apellido Paterno *</label><input class="fc" type="text" name="ap_paterno" maxlength="20" placeholder="Apellido Paterno" value="{{ old('ap_paterno', $e->Ap_paterno ?? '') }}" required></div>
      <div class="fg"><label>Teléfono *</label><input class="fc" type="text" name="telefono" maxlength="10" placeholder="Teléfono" value="{{ old('telefono', $e->Telefono ?? '') }}" required></div>
      <div class="fg"><label>Contraseña {{ $e ? '(vacío = no cambiar)' : '*' }}</label><input class="fc" type="password" name="contrasena" placeholder="Contraseña" {{ $e ? '' : 'required' }}></div>
      <div class="fg"><label>Dirección *</label><input class="fc" type="text" name="direccion" maxlength="100" placeholder="Dirección" value="{{ old('direccion', $e->Direccion ?? '') }}" required></div>
      <div class="fg"><label>Código Postal *</label><input class="fc" type="text" name="cp" maxlength="5" placeholder="Código Postal" value="{{ old('cp', $e->CP ?? '') }}" required></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $e ? 'Actualizar' : 'Guardar' }}</button>
    @if($e)<a href="{{ route('admin.clientes.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancelar edición</a>@endif
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-users" style="color:var(--accent)"></i> Lista de Clientes</div></div>
  <table>
    <thead><tr><th>ID</th><th>Nombre</th><th>Ap. Paterno</th><th>Teléfono</th><th>Dirección</th><th>CP</th><th>Eliminar</th><th>Modificar</th></tr></thead>
    <tbody>
      @foreach($clientes as $c)
      <tr><td>{{ $c->Id_cliente }}</td><td>{{ $c->Nombre }}</td><td>{{ $c->Ap_paterno }}</td><td>{{ $c->Telefono }}</td><td>{{ $c->Direccion }}</td><td>{{ $c->CP }}</td>
        <td><form action="{{ route('admin.clientes.destroy', $c) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td>
        <td><a href="{{ route('admin.clientes.edit', $c) }}" class="ib ib-edit" title="Modificar"><i class="fas fa-pencil-alt"></i></a></td></tr>
      @endforeach
      @if($clientes->isEmpty())<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No hay clientes registrados.</td></tr>@endif
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
