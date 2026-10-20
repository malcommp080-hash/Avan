@extends('htmla.layout')

@section('titulo', 'Validación de pago')

@push('head')
<style>
  .pestanas { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
  .pestanas form { margin-left:auto; display:flex; gap:6px; }
  .pago-lista { display:grid; gap:14px; }
  .pago { display:grid; grid-template-columns: 150px 1fr auto; gap:18px; align-items:start; padding:16px; border-radius:16px; border:1px solid rgba(127,127,127,.22); background:rgba(127,127,127,.05); }
  .ticket { width:150px; height:180px; border-radius:12px; overflow:hidden; background:rgba(127,127,127,.15); display:flex; align-items:center; justify-content:center; color:var(--muted); text-align:center; font-size:12px; text-decoration:none; position:relative; }
  .ticket img { width:100%; height:100%; object-fit:cover; }
  .ticket .lupa { position:absolute; right:6px; bottom:6px; background:rgba(0,0,0,.6); color:#fff; border-radius:8px; padding:3px 7px; font-size:11px; }
  .pago h4 { margin:0 0 4px; font-size:16px; color:var(--text); }
  .pago .monto { font-size:22px; font-weight:800; color:var(--text); }
  .pago .dato { font-size:12.5px; color:var(--muted); margin-top:3px; }
  .pago .dato b { color:var(--text); font-weight:600; }
  .pago ul { margin:8px 0 0; padding-left:18px; font-size:12.5px; color:var(--muted); }
  .pago .acciones { display:flex; flex-direction:column; gap:8px; min-width:190px; }
  .pago .acciones textarea { width:100%; min-height:60px; resize:vertical; }
  .etq { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase; }
  .etq.sin { background:#f9731622; color:#f97316; }
  .etq.APROBADO { background:#22c55e22; color:#22c55e; }
  .etq.RECHAZADO { background:#ef444422; color:#ef4444; }
  #visor { position:fixed; inset:0; background:rgba(0,0,0,.8); display:none; align-items:center; justify-content:center; z-index:9999; padding:20px; cursor:zoom-out; }
  #visor img { max-width:100%; max-height:100%; border-radius:8px; }
  @media (max-width: 760px) { .pago { grid-template-columns: 1fr; } .ticket { width:100%; height:220px; } .pago .acciones { min-width:0; } }
</style>
@endpush

@section('contenido')
@php
  $n = fn ($e) => (int) ($conteo[$e]->n ?? 0);
  $pendSinTicket = $estado === 'PENDIENTE' ? $pagos->getCollection()->whereNull('Comprobante')->count() : 0;
@endphp

<div class="ptitle">Validación de pago</div>
<div class="psub">{{ $saludo }} — Revisa el ticket de cada compra en línea y apruébala o recházala</div>

<div class="stat-grid" style="margin-bottom:20px">
  <div class="sc sc-amber"><div><div class="sc-val">{{ $n('PENDIENTE') }}</div><div class="sc-lbl">Pendientes · ${{ number_format($conteo['PENDIENTE']->total ?? 0, 2) }}</div></div><i class="fas fa-hourglass-half sc-icon"></i></div>
  <div class="sc sc-green"><div><div class="sc-val">{{ $n('APROBADO') }}</div><div class="sc-lbl">Aprobados · ${{ number_format($conteo['APROBADO']->total ?? 0, 2) }}</div></div><i class="fas fa-check-circle sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $n('RECHAZADO') }}</div><div class="sc-lbl">Rechazados</div></div><i class="fas fa-times-circle sc-icon"></i></div>
</div>

<div class="pestanas">
  @foreach(['PENDIENTE' => ['Pendientes', 'fa-clock'], 'APROBADO' => ['Aprobados', 'fa-check'], 'RECHAZADO' => ['Rechazados', 'fa-ban']] as $val => [$txt, $ico])
  <a href="{{ route('admin.validacion-pago.index', ['estado' => $val]) }}" class="btn btn-sm {{ $estado === $val ? 'btn-primary' : 'btn-outline' }}"><i class="fas {{ $ico }}"></i> {{ $txt }} ({{ $n($val) }})</a>
  @endforeach
  <form method="get" action="{{ route('admin.validacion-pago.index') }}">
    <input type="hidden" name="estado" value="{{ $estado }}">
    <input class="fc" type="search" name="buscar" placeholder="Folio, cliente o teléfono" value="{{ $filtros['buscar'] ?? '' }}" style="min-width:210px">
    <button class="btn btn-sm btn-outline" type="submit"><i class="fas fa-search"></i></button>
  </form>
</div>

@unless($puedeValidar)
  <div class="alert alert-warn"><i class="fas fa-lock"></i> Sólo administradores y contadores pueden aprobar o rechazar pagos.</div>
@endunless

<div class="pago-lista">
  @forelse($pagos as $p)
  @php $v = $p->venta; $esPdf = $p->Comprobante && str_ends_with(strtolower($p->Comprobante), '.pdf'); @endphp
  <div class="pago">
    @if($p->comprobante_url)
      <a class="ticket" href="{{ $p->comprobante_url }}" target="_blank" rel="noopener" @unless($esPdf) data-visor="{{ $p->comprobante_url }}" @endunless title="Ver ticket">
        @if($esPdf)<div><i class="fas fa-file-pdf" style="font-size:40px;color:#ef4444"></i><br>Abrir PDF</div>@else<img src="{{ $p->comprobante_url }}" alt="Ticket {{ $p->Folio }}" loading="lazy"><span class="lupa"><i class="fas fa-search-plus"></i> Ampliar</span>@endif
      </a>
    @else
      <div class="ticket"><div><i class="fas fa-receipt" style="font-size:30px"></i><br>El cliente aún no sube su ticket</div></div>
    @endif

    <div style="min-width:0">
      <h4>{{ $p->Folio }}
        @if($p->Estado === 'PENDIENTE' && !$p->Comprobante)<span class="etq sin">Sin ticket</span>@endif
        @if($p->Estado !== 'PENDIENTE')<span class="etq {{ $p->Estado }}">{{ \App\Models\Pago::ESTADOS[$p->Estado] }}</span>@endif
      </h4>
      <div class="monto">${{ number_format($p->Monto, 2) }}</div>
      <div class="dato">Cliente: <b>{{ $v?->cliente?->nombre_completo ?? '—' }}</b> · Tel. <b>{{ $v?->cliente?->Telefono ?? '—' }}</b></div>
      <div class="dato">Pedido: <b>{{ $v?->fecha_texto ?? '—' }}</b>@if($p->Comprobante_en) · Ticket enviado: <b>{{ $p->Comprobante_en->format('d/m/Y H:i') }}</b>@endif</div>
      @if($p->Referencia)<div class="dato">Referencia: <b>{{ $p->Referencia }}</b></div>@endif
      @if($v)<div class="dato">Entrega: <b>{{ $v->Direccion_entrega }}, C.P. {{ $v->CP_entrega }}</b></div>@endif
      @if($v && $v->detalles->isNotEmpty())
      <ul>@foreach($v->detalles as $d)<li>{{ $d->Cantidad }} × {{ $d->producto?->Nombre ?? 'Producto' }} ({{ $d->Talla === 'UNI' ? 'única' : $d->Talla }}) — ${{ number_format($d->Subtotal, 2) }}</li>@endforeach</ul>
      @endif
      @if($p->Estado !== 'PENDIENTE')
        <div class="dato" style="margin-top:8px">{{ $p->Estado === 'APROBADO' ? 'Aprobó' : 'Rechazó' }}: <b>{{ $p->validador ? $p->validador->Nombre . ' ' . $p->validador->Ap_paterno : '—' }}</b> el <b>{{ $p->Validado_en?->format('d/m/Y H:i') }}</b></div>
        @if($p->Motivo_rechazo)<div class="dato">Motivo: <b>{{ $p->Motivo_rechazo }}</b></div>@endif
      @endif
    </div>

    <div class="acciones">
      @if($v)<a href="{{ route('admin.ventas.show', $v) }}" class="btn btn-sm btn-outline"><i class="fas fa-receipt"></i> Ver detalle</a>@endif
      @if($p->Estado === 'PENDIENTE' && $puedeValidar)
        <form action="{{ route('admin.validacion-pago.aprobar', $p) }}" method="post" onsubmit="return confirm('¿Aprobar el pago {{ $p->Folio }} por ${{ number_format($p->Monto, 2) }}?{{ $p->Comprobante ? '' : ' El cliente todavía no sube su ticket.' }} Ya no podrá cambiarse.')">
          @csrf @method('PUT')
          <button type="submit" class="btn btn-success" style="width:100%"><i class="fas fa-check"></i> Aprobar</button>
        </form>
        <details>
          <summary class="btn btn-danger" style="width:100%;list-style:none;text-align:center;cursor:pointer"><i class="fas fa-times"></i> Rechazar</summary>
          <form action="{{ route('admin.validacion-pago.rechazar', $p) }}" method="post" style="margin-top:8px" onsubmit="return confirm('¿Rechazar el pago {{ $p->Folio }}? Las piezas regresarán al inventario y ya no podrá cambiarse.')">
            @csrf @method('PUT')
            <textarea class="fc" name="motivo" maxlength="255" required placeholder="Motivo (lo verá el cliente), p. ej. el monto no coincide"></textarea>
            <button type="submit" class="btn btn-sm btn-danger" style="width:100%;margin-top:6px">Confirmar rechazo</button>
          </form>
        </details>
      @endif
    </div>
  </div>
  @empty
  <div class="tcard" style="text-align:center;color:var(--muted);padding:30px">No hay pagos {{ ['PENDIENTE' => 'pendientes', 'APROBADO' => 'aprobados', 'RECHAZADO' => 'rechazados'][$estado] }}@if(!empty($filtros['buscar'])) que coincidan con «{{ $filtros['buscar'] }}»@endif.</div>
  @endforelse
</div>

@if($pagos->hasPages())
<div style="display:flex;justify-content:flex-end;gap:6px;margin-top:14px">
  @if(!$pagos->onFirstPage())<a href="{{ $pagos->previousPageUrl() }}" class="btn btn-sm btn-outline"><i class="fas fa-chevron-left"></i> Anterior</a>@endif
  @if($pagos->hasMorePages())<a href="{{ $pagos->nextPageUrl() }}" class="btn btn-sm btn-outline">Siguiente <i class="fas fa-chevron-right"></i></a>@endif
</div>
@endif

<div id="visor" role="dialog" aria-label="Ticket ampliado"><img alt="Ticket"></div>
@endsection

@push('scripts')
<script>
  // Ver el ticket en grande sin salir de la página (Esc o clic para cerrar)
  const visor = document.getElementById('visor');
  document.querySelectorAll('[data-visor]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault(); visor.querySelector('img').src = a.dataset.visor; visor.style.display = 'flex';
  }));
  visor.addEventListener('click', () => visor.style.display = 'none');
  document.addEventListener('keydown', e => { if (e.key === 'Escape') visor.style.display = 'none'; });
</script>
@endpush
