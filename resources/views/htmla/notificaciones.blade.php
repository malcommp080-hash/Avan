@extends('htmla.layout')

@section('titulo', 'Notificaciones')

@section('contenido')
@php
  $iconos = ['RECUPERAR_PASSWORD' => 'fa-key', 'COTIZACION' => 'fa-file-invoice-dollar', 'PAGO' => 'fa-money-check-dollar'];
@endphp

<div class="ptitle">Notificaciones</div>
<div class="psub">Avisos del sistema para ti</div>

<div class="tcard">
  <div class="tcard-hdr">
    <div class="tcard-ttl"><i class="fas fa-bell" style="color:var(--accent2)"></i> Bandeja</div>
    @if($notificaciones->contains('Leida', false))
    <form action="{{ route('admin.notificaciones.leer-todas') }}" method="post">@csrf
      <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-check-double"></i> Marcar todas como leídas</button>
    </form>
    @endif
  </div>
  <table>
    <thead><tr><th></th><th>Mensaje</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
      @forelse($notificaciones as $n)
      <tr style="{{ $n->Leida ? 'opacity:.6' : 'font-weight:600' }}">
        <td style="width:36px"><i class="fas {{ $iconos[$n->Tipo] ?? 'fa-circle-info' }}" style="color:var(--accent)"></i></td>
        <td>{{ $n->Mensaje }}</td>
        <td style="white-space:nowrap">{{ $n->Creado_en->format('d/m/Y H:i') }}</td>
        <td><a href="{{ route('admin.notificaciones.abrir', $n) }}" class="btn btn-sm btn-primary">{{ $n->Url ? 'Atender' : 'Marcar leída' }}</a></td>
      </tr>
      @empty
      <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No tienes notificaciones.</td></tr>
      @endforelse
    </tbody>
  </table>
  @if($notificaciones->hasPages())
  <div style="display:flex;justify-content:flex-end;gap:6px;padding:12px 16px">
    @if(!$notificaciones->onFirstPage())<a href="{{ $notificaciones->previousPageUrl() }}" class="btn btn-sm btn-outline"><i class="fas fa-chevron-left"></i> Anterior</a>@endif
    @if($notificaciones->hasMorePages())<a href="{{ $notificaciones->nextPageUrl() }}" class="btn btn-sm btn-outline">Siguiente <i class="fas fa-chevron-right"></i></a>@endif
  </div>
  @endif
</div>
@endsection
