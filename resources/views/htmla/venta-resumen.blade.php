@extends('htmla.layout')

@section('titulo', 'Resumen de Venta')

@push('head')
<style>
  .resumen-datos { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:6px; }
  .resumen-datos div span { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); margin-bottom:3px; }
  .resumen-datos div strong { font-size:14px; color:var(--text); }
  .resumen-total td { font-size:16px; font-weight:800; border-top:2px solid rgba(127,127,127,.35); }
  .num { text-align:right; white-space:nowrap; }
  @media print {
    #sb, .topbar, .no-print, .alert { display:none !important; }
    #main { margin:0 !important; }
    body, .fcard, .tcard { background:#fff !important; color:#000 !important; box-shadow:none !important; }
    .resumen-datos div strong, td, th { color:#000 !important; }
  }
</style>
@endpush

@section('contenido')
<div class="ptitle">Venta #{{ $venta->Id_venta }}</div>
<div class="psub">Resumen detallado de los artículos y cargos</div>

<div class="fcard" style="margin-bottom:20px">
  <div class="resumen-datos">
    <div><span>Fecha y hora</span><strong>{{ $venta->fecha_texto }}</strong></div>
    <div><span>Cliente</span><strong>{{ $venta->cliente?->nombre_completo ?? '—' }}</strong></div>
    <div><span>Forma de pago</span><strong>{{ $venta->forma_pago_texto }}</strong></div>
    <div><span>Registró</span><strong>{{ $venta->empleado ? $venta->empleado->Nombre . ' ' . $venta->empleado->Ap_paterno : '—' }}</strong></div>
  </div>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-receipt" style="color:var(--accent2)"></i> Artículos vendidos</div></div>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Precio unitario</th><th class="num">Subtotal</th></tr></thead>
    <tbody>
      @forelse($venta->detalles as $d)
      <tr>
        <td>{{ $d->producto?->Nombre ?? 'Producto eliminado' }}</td>
        <td class="num">{{ $d->Cantidad }}</td>
        <td class="num">${{ number_format($d->Precio_unitario, 2) }}</td>
        <td class="num">${{ number_format($d->Subtotal, 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:20px">Esta venta se registró antes del módulo por producto y no tiene detalle de artículos ({{ $venta->Total_piezas }} pieza(s)).</td></tr>
      @endforelse
      <tr class="resumen-total">
        <td>Total</td>
        <td class="num">{{ $venta->Total_piezas }} pza.</td>
        <td></td>
        <td class="num">${{ number_format($venta->Total, 2) }}</td>
      </tr>
    </tbody>
  </table>
  </div>
</div>

<div class="no-print" style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap">
  <a href="{{ route('admin.ventas.index') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar otra venta</a>
  <button type="button" class="btn btn-outline" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
</div>
@endsection
