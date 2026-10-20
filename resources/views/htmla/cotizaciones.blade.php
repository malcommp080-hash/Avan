@extends('htmla.layout')

@section('titulo', 'Cotizaciones')

@section('contenido')
<div class="ptitle">Cotizaciones</div>
<div class="psub">Solicitudes que los clientes generan desde su carrito</div>

<div style="display:flex;gap:8px;margin-bottom:16px">
  <a href="{{ route('admin.cotizaciones.index') }}" class="btn btn-sm {{ $estado === 'PENDIENTE' ? 'btn-primary' : 'btn-outline' }}"><i class="fas fa-clock"></i> Pendientes ({{ $pendientes }})</a>
  <a href="{{ route('admin.cotizaciones.index', ['estado' => 'ATENDIDA']) }}" class="btn btn-sm {{ $estado === 'ATENDIDA' ? 'btn-primary' : 'btn-outline' }}"><i class="fas fa-check"></i> Atendidas</a>
</div>

<div class="tcard">
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Folio</th><th>Fecha</th><th>Cliente</th><th>Teléfono</th><th>Artículos</th><th>Total</th>@if($estado === 'ATENDIDA')<th>Atendió</th>@endif<th></th></tr></thead>
    <tbody>
      @forelse($cotizaciones as $c)
      <tr>
        <td>#{{ $c->Id_cotizacion }}</td>
        <td>{{ $c->Creado_en->format('d/m/Y H:i') }}</td>
        <td>{{ $c->cliente?->nombre_completo ?? '—' }}</td>
        <td>{{ $c->cliente?->Telefono ?? '—' }}</td>
        <td>{{ $c->detalles_count }}</td>
        <td>${{ number_format($c->Total, 2) }}</td>
        @if($estado === 'ATENDIDA')<td>{{ $c->atendio ? $c->atendio->Nombre . ' ' . $c->atendio->Ap_paterno : '—' }}<div style="font-size:11px;color:var(--muted)">{{ $c->Atendida_en?->format('d/m/Y H:i') }}</div></td>@endif
        <td><a href="{{ route('admin.cotizaciones.show', $c) }}" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> Ver</a></td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No hay cotizaciones {{ $estado === 'PENDIENTE' ? 'pendientes' : 'atendidas' }}.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>
@endsection
