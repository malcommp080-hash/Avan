<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Prendas — Sistema DAV</title>
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
      <div class="tb-title">Registro de Prendas</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Registro de Prendas</span>
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

<div class="ptitle">Registro de Prendas</div>
<div class="psub">{{ $saludo }}</div>

<div class="fcard">
  @php $e = $editar ?? null; @endphp
  <form action="{{ $e ? route('admin.prendas.update', $e) : route('admin.prendas.store') }}" method="post">
    @csrf
    @if($e) @method('PUT') @endif
    <div class="fgrid" style="grid-template-columns:1fr 1fr auto">
      <div class="fg"><label>Nombre de la Prenda *</label><input class="fc" type="text" name="prendas" maxlength="10" placeholder="Nombre de la prenda" value="{{ old('prendas', $e->Prendas ?? '') }}" required></div>
      <div class="fg"><label>Número de Piezas *</label><input class="fc" type="number" min="0" name="num_piezas" placeholder="0" value="{{ old('num_piezas', $e->Num_piezas ?? '') }}" required></div>
      <div class="fg" style="justify-content:flex-end"><label>&nbsp;</label>
        <button type="submit" class="btn btn-primary" style="height:42px"><i class="fas fa-save"></i> {{ $e ? 'Actualizar' : 'Guardar' }}</button>
      </div>
    </div>
    @if($e)<a href="{{ route('admin.prendas.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancelar edición</a>@endif
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-tshirt" style="color:var(--accent3)"></i> Lista de Prendas</div></div>
  <table>
    <thead><tr><th>ID</th><th>Prenda</th><th>Núm. Piezas</th><th>Eliminar</th><th>Modificar</th></tr></thead>
    <tbody>
      @foreach($prendas as $p)
      <tr><td>{{ $p->Id_prendas }}</td><td>{{ $p->Prendas }}</td><td>{{ $p->Num_piezas }}</td>
        <td><form action="{{ route('admin.prendas.destroy', $p) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este registro?')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td><td><a href="{{ route('admin.prendas.edit', $p) }}" class="ib ib-edit" title="Modificar"><i class="fas fa-pencil-alt"></i></a></td></tr>
      @endforeach
      @if($prendas->isEmpty())<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">No hay prendas registradas.</td></tr>@endif
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
