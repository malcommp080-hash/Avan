<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Sistema DAV</title>
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
      <div class="tb-title">Dashboard</div>
      <div style="font-size:11.5px;color:var(--muted)">
        Home / <span style="color:var(--accent)">Dashboard</span>
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

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div>
    <div class="ptitle" style="margin-bottom:2px">Dashboard</div>
    <div class="psub" style="margin-bottom:0">{{ $saludo }} — Resumen general del sistema</div>
  </div>
  <div style="font-size:11.5px;color:var(--muted)">Home / <span style="color:var(--accent)">Dashboard</span></div>
</div>

<div class="stat-grid">
  <div class="sc sc-teal">
    <div><div class="sc-val">{{ $totalClientes }}</div><div class="sc-lbl">Clientes Registrados</div>
      <a href="{{ route('admin.clientes.index') }}" class="sc-more" style="color:rgba(255,255,255,.85)">Ver clientes <i class="fas fa-arrow-right"></i></a></div>
    <i class="fas fa-users sc-icon"></i>
  </div>
  <div class="sc sc-green">
    <div><div class="sc-val">{{ $totalVentas }}</div><div class="sc-lbl">Ventas Totales</div>
      <a href="{{ route('admin.ventas.index') }}" class="sc-more" style="color:rgba(255,255,255,.85)">Ver ventas <i class="fas fa-arrow-right"></i></a></div>
    <i class="fas fa-chart-bar sc-icon"></i>
  </div>
  <div class="sc sc-amber">
    <div><div class="sc-val">{{ $totalPrendas }}</div><div class="sc-lbl">Prendas</div>
      <a href="{{ route('admin.prendas.index') }}" class="sc-more" style="color:rgba(255,255,255,.85)">Ver prendas <i class="fas fa-arrow-right"></i></a></div>
    <i class="fas fa-tshirt sc-icon"></i>
  </div>
  <div class="sc sc-red">
    <div><div class="sc-val">${{ number_format($montoTotal, 2) }}</div><div class="sc-lbl">Monto Total Ventas</div>
      <a href="{{ route('admin.ventas.index') }}" class="sc-more" style="color:rgba(255,255,255,.85)">Más info <i class="fas fa-arrow-right"></i></a></div>
    <i class="fas fa-dollar-sign sc-icon"></i>
  </div>
</div>

<div class="chart-row">
  <div class="chart-card">
    <div class="ch-hdr">
      <div class="ch-ttl"><i class="fas fa-chart-bar" style="color:var(--accent)"></i> Ventas por Mes</div>
      <div class="ch-btns">
        <button class="ch-btn act" onclick="switchChart('bar',this)">Barras</button>
        <button class="ch-btn" onclick="switchChart('line',this)">Línea</button>
      </div>
    </div>
    <canvas id="graficaVentas" height="200"></canvas>
  </div>
  <div class="chart-card">
    <div class="ch-hdr"><div class="ch-ttl"><i class="fas fa-pie-chart" style="color:var(--accent2)"></i> Resumen</div></div>
    <canvas id="graficaDonut" height="160"></canvas>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:14px;text-align:center">
      <div><div style="font-size:10px;color:var(--muted)">Clientes</div><div style="font-size:20px;font-weight:800;color:var(--accent)">{{ $totalClientes }}</div></div>
      <div><div style="font-size:10px;color:var(--muted)">Ventas</div><div style="font-size:20px;font-weight:800;color:var(--accent2)">{{ $totalVentas }}</div></div>
      <div><div style="font-size:10px;color:var(--muted)">Prendas</div><div style="font-size:20px;font-weight:800;color:var(--accent3)">{{ $totalPrendas }}</div></div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="tcard">
    <div class="tcard-hdr">
      <div class="tcard-ttl"><i class="fas fa-user-clock" style="color:var(--accent)"></i> Últimos 5 Clientes</div>
      <a href="{{ route('admin.clientes.index') }}" class="btn btn-sm btn-primary">Ver todos</a>
    </div>
    <table>
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Dirección</th></tr></thead>
      <tbody>
        @forelse($ultimosClientes as $c)
        <tr><td>{{ $c->Id_cliente }}</td><td>{{ $c->nombre_completo }}</td><td>{{ $c->Telefono }}</td><td>{{ $c->Direccion }}</td></tr>
        @empty
        <tr><td colspan="4" style="text-align:center;color:var(--muted)">Sin clientes.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="tcard">
    <div class="tcard-hdr">
      <div class="tcard-ttl"><i class="fas fa-receipt" style="color:var(--accent2)"></i> Últimas 5 Ventas</div>
      <a href="{{ route('admin.ventas.index') }}" class="btn btn-sm btn-success">Ver todas</a>
    </div>
    <table>
      <thead><tr><th>ID</th><th>Cliente</th><th>Total</th><th>Fecha</th></tr></thead>
      <tbody>
        @forelse($ultimasVentas as $v)
        <tr><td>#{{ $v->Id_venta }}</td><td>{{ $v->cliente?->nombre_completo ?? '—' }}</td><td>${{ number_format($v->Total, 2) }}</td><td>{{ $v->Fecha->format('Y-m-d') }}</td></tr>
        @empty
        <tr><td colspan="4" style="text-align:center;color:var(--muted)">Sin ventas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
const commonOpts = {
  responsive:true,
  plugins:{legend:{labels:{color:'#e4e8f0',font:{size:11}}}},
  scales:{
    x:{ticks:{color:'#7a8099'},grid:{color:'rgba(255,255,255,.05)'}},
    y:{ticks:{color:'#7a8099'},grid:{color:'rgba(255,255,255,.05)'}}
  }
};
const labelsData = @json($labels);
const totalesData = @json($totales);
let salesChart;
function buildChart(type){
  const ctx = document.getElementById('graficaVentas').getContext('2d');
  if(salesChart) salesChart.destroy();
  salesChart = new Chart(ctx,{
    type,
    data:{ labels: labelsData, datasets:[{
      label:'Total Ventas ($)', data: totalesData,
      backgroundColor: type==='bar' ? 'rgba(79,142,247,.55)' : 'rgba(79,142,247,.2)',
      borderColor:'#4f8ef7', borderWidth:2, borderRadius: type==='bar' ? 8 : 0,
      fill: type==='line', tension:.4
    }]},
    options: commonOpts
  });
}
function switchChart(type, btn){
  document.querySelectorAll('.ch-btn').forEach(b=>b.classList.remove('act'));
  btn.classList.add('act'); buildChart(type);
}
buildChart('bar');
new Chart(document.getElementById('graficaDonut').getContext('2d'),{
  type:'doughnut',
  data:{ labels:['Clientes','Ventas','Prendas'],
    datasets:[{ data:[{{ $totalClientes }},{{ $totalVentas }},{{ $totalPrendas }}], backgroundColor:['#4f8ef7','#22d3a5','#f7c948'], borderWidth:0 }]},
  options:{plugins:{legend:{position:'bottom',labels:{color:'#e4e8f0',font:{size:11}}}}}
});
</script>

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
