@extends('htmla.layout')

@section('titulo', 'Bitácora de accesos')

@section('contenido')
@php
  $colores = ['EXITO' => '#22d3a5', 'FALLIDO' => '#f7c948', 'BLOQUEADO' => '#f75e5e', 'LOGOUT' => '#7a8099', 'INACTIVIDAD' => '#4f8ef7'];
  $textos  = ['EXITO' => 'Éxito', 'FALLIDO' => 'Fallido', 'BLOQUEADO' => 'Bloqueado', 'LOGOUT' => 'Cerró sesión', 'INACTIVIDAD' => 'Inactividad'];
@endphp

<div class="ptitle">Bitácora de accesos</div>
<div class="psub">Registro de cada intento de inicio de sesión, cierre de sesión y bloqueo</div>

<div class="stat-grid">
  <div class="sc sc-green"><div><div class="sc-val">{{ $exitosHoy }}</div><div class="sc-lbl">Accesos exitosos hoy</div></div><i class="fas fa-right-to-bracket sc-icon"></i></div>
  <div class="sc sc-amber"><div><div class="sc-val">{{ $fallidosHoy }}</div><div class="sc-lbl">Intentos fallidos hoy</div></div><i class="fas fa-triangle-exclamation sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $bloqueosHoy }}</div><div class="sc-lbl">Bloqueos hoy</div></div><i class="fas fa-lock sc-icon"></i></div>
</div>

<div class="fcard" style="margin-bottom:20px">
  <form method="get" action="{{ route('admin.bitacora.index') }}">
    <div class="fgrid fgrid-4">
      <div class="fg"><label>Usuario</label>
        <select class="fc" name="tipo">
          <option value="">Todos</option>
          <option value="ADMIN" @selected(($filtros['tipo'] ?? '') === 'ADMIN')>Empleados</option>
          <option value="CLIENTE" @selected(($filtros['tipo'] ?? '') === 'CLIENTE')>Clientes</option>
        </select>
      </div>
      <div class="fg"><label>Resultado</label>
        <select class="fc" name="resultado">
          <option value="">Todos</option>
          @foreach($resultados as $r)<option value="{{ $r }}" @selected(($filtros['resultado'] ?? '') === $r)>{{ $textos[$r] }}</option>@endforeach
        </select>
      </div>
      <div class="fg"><label>Desde</label><input class="fc" type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"></div>
      <div class="fg"><label>Hasta</label><input class="fc" type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}"></div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
    <a href="{{ route('admin.bitacora.index') }}" class="btn btn-outline"><i class="fas fa-eraser"></i> Limpiar</a>
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-clipboard-list" style="color:var(--accent2)"></i> Registros ({{ $registros->total() }})</div></div>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Teléfono</th><th>Nombre</th><th>Resultado</th><th>IP</th></tr></thead>
    <tbody>
      @forelse($registros as $r)
      @php $c = $colores[$r->Resultado] ?? '#7a8099'; @endphp
      <tr>
        <td>{{ $r->Creado_en->format('d/m/Y H:i:s') }}</td>
        <td>{{ $r->Tipo === 'ADMIN' ? 'Empleado' : 'Cliente' }}</td>
        <td>{{ $r->Telefono ?: '—' }}</td>
        <td>{{ $nombres[$r->Tipo][$r->Id_usuario] ?? '—' }}</td>
        <td><span style="background:{{ $c }}22;color:{{ $c }};padding:3px 12px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase">{{ $textos[$r->Resultado] ?? $r->Resultado }}</span></td>
        <td title="{{ $r->Navegador }}">{{ $r->Ip }}</td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">No hay registros con esos filtros.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
  @if($registros->hasPages())
  <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;font-size:12.5px;color:var(--muted)">
    <span>Página {{ $registros->currentPage() }} de {{ $registros->lastPage() }}</span>
    <span>
      @if(!$registros->onFirstPage())<a href="{{ $registros->previousPageUrl() }}" class="btn btn-sm btn-outline"><i class="fas fa-chevron-left"></i> Anterior</a>@endif
      @if($registros->hasMorePages())<a href="{{ $registros->nextPageUrl() }}" class="btn btn-sm btn-outline">Siguiente <i class="fas fa-chevron-right"></i></a>@endif
    </span>
  </div>
  @endif
</div>
@endsection
