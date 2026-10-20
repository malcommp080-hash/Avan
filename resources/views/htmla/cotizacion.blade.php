@extends('htmla.layout')

@section('titulo', 'Cotización')

@section('contenido')
<div class="ptitle">Cotización #{{ $cot->Id_cotizacion }}</div>
<div class="psub">Generada el {{ $cot->Creado_en->format('d/m/Y \a \l\a\s H:i') }}</div>

<div class="fcard" style="margin-bottom:20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px">
  <div><div style="font-size:11px;text-transform:uppercase;color:var(--muted)">Cliente</div><strong>{{ $cot->cliente?->nombre_completo ?? '—' }}</strong></div>
  <div><div style="font-size:11px;text-transform:uppercase;color:var(--muted)">Teléfono</div><strong>@if($cot->cliente)<a href="tel:{{ $cot->cliente->Telefono }}">{{ $cot->cliente->Telefono }}</a>@else — @endif</strong></div>
  <div><div style="font-size:11px;text-transform:uppercase;color:var(--muted)">Dirección</div><strong>{{ $cot->cliente ? $cot->cliente->Direccion . ', C.P. ' . $cot->cliente->CP : '—' }}</strong></div>
  <div><div style="font-size:11px;text-transform:uppercase;color:var(--muted)">Estado</div>
    @if($cot->Estado === 'ATENDIDA')<strong style="color:#22c55e">Atendida</strong><div style="font-size:12px;color:var(--muted)">por {{ $cot->atendio ? $cot->atendio->Nombre . ' ' . $cot->atendio->Ap_paterno : '—' }}, {{ $cot->Atendida_en?->format('d/m/Y H:i') }}</div>
    @else<strong style="color:#f97316">Pendiente</strong>@endif
  </div>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-file-invoice-dollar" style="color:var(--accent2)"></i> Artículos cotizados</div></div>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Producto</th><th>Talla</th><th style="text-align:right">Cantidad</th><th style="text-align:right">Precio unitario</th><th style="text-align:right">Subtotal</th></tr></thead>
    <tbody>
      @foreach($cot->detalles as $d)
      <tr><td>{{ $d->producto?->Nombre ?? 'Producto eliminado' }}</td><td>{{ $d->Talla ?? '—' }}</td><td style="text-align:right">{{ $d->Cantidad }}</td><td style="text-align:right">${{ number_format($d->Precio_unitario, 2) }}</td><td style="text-align:right">${{ number_format($d->Subtotal, 2) }}</td></tr>
      @endforeach
      <tr><td colspan="4" style="font-weight:800">Total</td><td style="text-align:right;font-weight:800;font-size:16px">${{ number_format($cot->Total, 2) }}</td></tr>
    </tbody>
  </table>
  </div>
</div>

<div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap">
  @if($cot->Estado === 'PENDIENTE')
  <form action="{{ route('admin.cotizaciones.atender', $cot) }}" method="post">@csrf @method('PUT')
    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Marcar como atendida</button>
  </form>
  @endif
  <a href="{{ route('admin.cotizaciones.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>
@endsection
