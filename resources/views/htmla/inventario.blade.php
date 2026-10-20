@extends('htmla.layout')

@section('titulo', 'Inventario')

@push('head')
<style>
  .tallas-form { display:flex; flex-wrap:wrap; gap:6px; align-items:flex-end; }
  .tallas-form label { display:flex; flex-direction:column; font-size:10.5px; font-weight:700; color:var(--muted); gap:2px; text-align:center; }
  .tallas-form input { width:58px; padding:5px 6px; font-size:13px; text-align:center; }
  .tallas-form input.bajo { border-color:#f97316; }
  .tallas-form input.cero { border-color:#ef4444; }
</style>
@endpush

@section('contenido')
<div class="ptitle">Inventario</div>
<div class="psub">{{ $saludo }} — Stock por talla; ajusta las piezas y guarda</div>

<div class="stat-grid" style="margin-bottom:20px">
  <div class="sc sc-teal"><div><div class="sc-val">{{ $totalProductos }}</div><div class="sc-lbl">Modelos</div></div><i class="fas fa-boxes sc-icon"></i></div>
  <div class="sc sc-green"><div><div class="sc-val">{{ $totalStock }}</div><div class="sc-lbl">Piezas totales</div></div><i class="fas fa-warehouse sc-icon"></i></div>
  <div class="sc sc-amber"><div><div class="sc-val">${{ number_format($valor, 2) }}</div><div class="sc-lbl">Valor del inventario</div></div><i class="fas fa-dollar-sign sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $stockBajo }}</div><div class="sc-lbl">Con alguna talla baja (&lt;5)</div></div><i class="fas fa-exclamation-triangle sc-icon"></i></div>
</div>

<div class="tcard">
  <div class="tcard-hdr">
    <div class="tcard-ttl"><i class="fas fa-warehouse" style="color:var(--accent3)"></i> Estado del inventario</div>
    <small style="color:var(--muted)">Menor stock primero</small>
  </div>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Modelo</th><th>Categoría</th><th>Total</th><th>Nivel</th><th>Valor stock</th><th>Stock por talla</th></tr></thead>
    <tbody>
      @forelse($productos as $p)
      @php
        if ($p->Stock <= 0) { $c = '#ef4444'; $n = 'Agotado'; }
        elseif ($p->Stock < 10) { $c = '#f97316'; $n = 'Bajo'; }
        else { $c = '#22c55e'; $n = 'Bueno'; }
      @endphp
      <tr>
        <td><strong>{{ $p->Nombre }}</strong><div style="font-size:11px;color:var(--muted)">{{ $p->segmento_texto }}@if($p->Color) · {{ $p->Color }}@endif</div></td>
        <td>{{ $p->categoria?->Nombre ?? '—' }}</td>
        <td><span style="font-size:18px;font-weight:800;color:{{ $c }}">{{ $p->Stock }}</span></td>
        <td><span style="background:{{ $c }}22;color:{{ $c }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700">{{ $n }}</span></td>
        <td>${{ number_format($p->Stock * $p->Precioneto, 2) }}</td>
        <td>
          <form action="{{ route('admin.inventario.update', $p) }}" method="post" class="tallas-form">
            @csrf @method('PUT')
            @foreach($p->tallas_ordenadas as $t)
            <label>{{ $t->Talla }}<input class="fc {{ $t->Stock <= 0 ? 'cero' : ($t->Stock < 5 ? 'bajo' : '') }}" type="number" name="tallas[{{ $t->Id_pt }}]" value="{{ $t->Stock }}" min="0"></label>
            @endforeach
            <button type="submit" class="btn btn-sm btn-primary" title="Guardar stock"><i class="fas fa-save"></i></button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">No hay productos en inventario.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>
@endsection
