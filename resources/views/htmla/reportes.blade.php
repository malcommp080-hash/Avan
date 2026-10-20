@extends('htmla.layout')

@section('titulo', 'Reportes de ventas')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
  .viz-root { --serie-1: #3987e5; --rejilla: rgba(255,255,255,.06); --eje: #7a8099; }
  .filtro-fechas { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
  .filtro-fechas .fg { margin-bottom:0; }
  .rapidos { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }
  .graf-grid { display:grid; grid-template-columns: 1fr; gap:20px; margin-bottom:20px; }
  .graf { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; }
  .graf h3 { margin:0; font-size:15px; color:var(--text); }
  .graf .sub { font-size:12px; color:var(--muted); margin:2px 0 12px; }
  .graf .lienzo { position:relative; height:280px; }
  .graf details { margin-top:12px; font-size:12.5px; }
  .graf summary { cursor:pointer; color:var(--accent); }
  .graf table { margin-top:8px; }
  .num { text-align:right; white-space:nowrap; }
  .verificacion { font-size:12.5px; padding:8px 12px; border-radius:10px; margin-bottom:20px; display:flex; gap:8px; align-items:center; }
  .verificacion.ok { background:#22c55e18; color:#22c55e; }
  .verificacion.mal { background:#ef444418; color:#ef4444; }
  @media (min-width: 1100px) { .graf-grid { grid-template-columns: 3fr 2fr; } }
</style>
@endpush

@section('contenido')
@php
  $hoy = now();
  $rapidos = [
    'Este mes'          => [$hoy->copy()->startOfMonth(), $hoy],
    'Últimos 3 meses'   => [$hoy->copy()->subMonthsNoOverflow(2)->startOfMonth(), $hoy],
    'Últimos 6 meses'   => [$hoy->copy()->subMonthsNoOverflow(5)->startOfMonth(), $hoy],
    'Este año'          => [$hoy->copy()->startOfYear(), $hoy],
    'Últimos 12 meses'  => [$hoy->copy()->subMonthsNoOverflow(11)->startOfMonth(), $hoy],
  ];
  $dinero = fn ($n) => '$' . number_format($n, 2);
@endphp

<div class="ptitle">Reportes de ventas</div>
<div class="psub">Desempeño del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} · mostrador y compras en línea aprobadas</div>

<div class="fcard" style="margin-bottom:20px">
  <form method="get" action="{{ route('admin.reportes.index') }}" class="filtro-fechas">
    <div class="fg"><label>Desde</label><input class="fc" type="date" name="desde" value="{{ $desde->toDateString() }}" max="{{ $hoy->toDateString() }}" required></div>
    <div class="fg"><label>Hasta</label><input class="fc" type="date" name="hasta" value="{{ $hasta->toDateString() }}" max="{{ $hoy->toDateString() }}" required></div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar</button>
  </form>
  <div class="rapidos">
    @foreach($rapidos as $txt => [$a, $b])
      @php $act = $a->toDateString() === $desde->toDateString() && $b->toDateString() === $hasta->toDateString(); @endphp
      <a href="{{ route('admin.reportes.index', ['desde' => $a->toDateString(), 'hasta' => $b->toDateString()]) }}" class="btn btn-sm {{ $act ? 'btn-primary' : 'btn-outline' }}">{{ $txt }}</a>
    @endforeach
  </div>
</div>

<div class="stat-grid" style="margin-bottom:14px">
  <div class="sc sc-teal"><div><div class="sc-val">{{ $dinero($total) }}</div><div class="sc-lbl">Total vendido</div></div><i class="fas fa-sack-dollar sc-icon"></i></div>
  <div class="sc sc-green"><div><div class="sc-val">{{ $numVentas }}</div><div class="sc-lbl">Ventas · {{ $piezas }} piezas</div></div><i class="fas fa-receipt sc-icon"></i></div>
  <div class="sc sc-amber"><div><div class="sc-val">{{ $dinero($promedio) }}</div><div class="sc-lbl">Ticket promedio</div></div><i class="fas fa-scale-balanced sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $dinero($enLinea) }}</div><div class="sc-lbl">De la tienda en línea</div></div><i class="fas fa-globe sc-icon"></i></div>
</div>

<div class="verificacion {{ $cuadra ? 'ok' : 'mal' }}" role="status">
  <i class="fas fa-{{ $cuadra ? 'circle-check' : 'triangle-exclamation' }}"></i>
  @if($cuadra) La suma por semanas y por meses coincide con el total registrado en la base de datos ({{ $dinero($totalSql) }}).
  @else Atención: las gráficas no cuadran con el total de la base de datos ({{ $dinero($totalSql) }}). @endif
</div>

<div class="graf-grid viz-root">
  @foreach([['semanal', 'Ventas por semana', 'Lunes a domingo; la primera y la última semana se recortan al rango', $semanas, 'Semana'], ['mensual', 'Ventas por mes', 'Mes calendario dentro del rango', $meses, 'Mes']] as [$id, $tit, $sub, $datos, $col])
  <section class="graf" aria-labelledby="t-{{ $id }}">
    <h3 id="t-{{ $id }}">{{ $tit }}</h3>
    <div class="sub">{{ $sub }}</div>
    <div class="lienzo"><canvas id="g-{{ $id }}" role="img" aria-label="{{ $tit }}: gráfica de columnas con el total vendido"></canvas></div>
    <details>
      <summary>Ver como tabla</summary>
      <div style="overflow-x:auto">
      <table>
        <thead><tr><th>{{ $col }}</th><th class="num">Ventas</th><th class="num">Piezas</th><th class="num">Total</th></tr></thead>
        <tbody>
          @foreach($datos as $r)<tr><td>{{ $r['etiqueta'] }}</td><td class="num">{{ $r['ventas'] }}</td><td class="num">{{ $r['piezas'] }}</td><td class="num">{{ $dinero($r['total']) }}</td></tr>@endforeach
          <tr><td><b>Total</b></td><td class="num"><b>{{ array_sum(array_column($datos, 'ventas')) }}</b></td><td class="num"><b>{{ array_sum(array_column($datos, 'piezas')) }}</b></td><td class="num"><b>{{ $dinero(array_sum(array_column($datos, 'total'))) }}</b></td></tr>
        </tbody>
      </table>
      </div>
    </details>
  </section>
  @endforeach
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-ranking-star" style="color:var(--accent3)"></i> Productos más vendidos en el periodo</div></div>
  <table>
    <thead><tr><th>#</th><th>Producto</th><th class="num">Piezas</th><th class="num">Importe</th><th class="num">% del total</th></tr></thead>
    <tbody>
      @forelse($top as $i => $t)
      <tr><td>{{ $i + 1 }}</td><td>{{ $t->Nombre }}</td><td class="num">{{ $t->piezas }}</td><td class="num">{{ $dinero($t->importe) }}</td><td class="num">{{ $total > 0 ? number_format($t->importe / $total * 100, 1) : 0 }}%</td></tr>
      @empty
      <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:20px">Sin ventas con detalle de productos en este periodo.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection

@push('scripts')
<script>
(function () {
  if (!window.Chart) return; // sin CDN, la tabla de cada gráfica sigue disponible
  const css = getComputedStyle(document.querySelector('.viz-root'));
  const serie = css.getPropertyValue('--serie-1').trim(), rejilla = css.getPropertyValue('--rejilla').trim(), eje = css.getPropertyValue('--eje').trim();
  const dinero = n => '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const corto = n => n >= 1000 ? '$' + (n / 1000).toLocaleString('es-MX', { maximumFractionDigits: 1 }) + ' k' : '$' + n;

  function columnas(id, datos, prefijo) {
    new Chart(document.getElementById(id), {
      type: 'bar',
      data: {
        labels: datos.map(d => d.etiqueta),
        datasets: [{
          data: datos.map(d => d.total),
          backgroundColor: serie, hoverBackgroundColor: serie,
          maxBarThickness: 24, borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'start',
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false }, // el tooltip aparece en toda la franja, no sólo sobre la barra
        plugins: {
          legend: { display: false }, // una sola serie: el título de la tarjeta la nombra
          tooltip: {
            backgroundColor: '#11131c', borderColor: '#2a2f45', borderWidth: 1, padding: 10, displayColors: false,
            titleColor: '#e4e8f0', bodyColor: '#e4e8f0',
            callbacks: {
              title: items => `${prefijo} ${items[0].label}`,
              label: item => dinero(item.raw),
              afterLabel: item => { const d = datos[item.dataIndex]; return `${d.ventas} venta(s) · ${d.piezas} pieza(s)`; },
            },
          },
        },
        scales: {
          x: { grid: { display: false }, border: { color: rejilla }, ticks: { color: eje, maxRotation: 0, autoSkip: true, autoSkipPadding: 12, font: { size: 11 } } },
          y: { beginAtZero: true, grid: { color: rejilla }, border: { display: false }, ticks: { color: eje, font: { size: 11 }, callback: v => corto(v), maxTicksLimit: 6 } },
        },
      },
    });
  }
  columnas('g-semanal', @json($semanas), 'Semana');
  columnas('g-mensual', @json($meses), '');
})();
</script>
@endpush
