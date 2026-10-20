@extends('htmla.layout')

@section('titulo', 'Productos')

@push('head')
<style>
  .talla-row { display:grid; grid-template-columns: 110px 110px 40px; gap:8px; align-items:center; margin-bottom:8px; }
  .img-prev { width:96px; height:96px; border-radius:12px; object-fit:cover; background:rgba(127,127,127,.15); display:flex; align-items:center; justify-content:center; color:var(--muted); font-size:28px; overflow:hidden; }
  .img-prev img { width:100%; height:100%; object-fit:cover; }
  .prod-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap:16px; padding:16px; }
  .prod-card { display:flex; gap:14px; padding:14px; border-radius:16px; border:1px solid rgba(127,127,127,.2); background:rgba(127,127,127,.05); position:relative; }
  .prod-card.inactivo { opacity:.55; }
  .prod-card .foto { width:92px; height:110px; flex:none; border-radius:12px; overflow:hidden; background:rgba(127,127,127,.15); display:flex; align-items:center; justify-content:center; color:var(--muted); font-size:26px; }
  .prod-card .foto img { width:100%; height:100%; object-fit:cover; }
  .prod-card h4 { margin:0 0 2px; font-size:14px; color:var(--text); line-height:1.25; }
  .prod-card .meta { font-size:11.5px; color:var(--muted); margin-bottom:6px; }
  .prod-card .precio { font-size:18px; font-weight:800; color:var(--text); }
  .prod-card .mayoreo { font-size:11.5px; color:var(--green, #22c55e); font-weight:600; }
  .chips { display:flex; flex-wrap:wrap; gap:4px; margin-top:8px; }
  .chip { font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px; background:rgba(127,127,127,.18); color:var(--text); }
  .chip.bajo { background:#f9731633; color:#f97316; }
  .chip.cero { background:#ef444433; color:#ef4444; text-decoration:line-through; }
  .prod-card .acciones { position:absolute; top:10px; right:10px; display:flex; gap:4px; }
  .filtros { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
  .filtros .fc { width:auto; min-width:150px; }
</style>
@endpush

@section('contenido')
@php
  $e = $editar ?? null;
  $tallasForm = old('tallas', $e ? $e->tallas_ordenadas->map(fn ($t) => ['talla' => $t->Talla, 'stock' => $t->Stock])->all() : [['talla' => 'CH', 'stock' => 0], ['talla' => 'M', 'stock' => 0], ['talla' => 'G', 'stock' => 0]]);
@endphp

<div class="ptitle">Productos</div>
<div class="psub">{{ $saludo }} — Modelos del catálogo, precios y stock por talla</div>

<div class="stat-grid" style="margin-bottom:20px">
  <div class="sc sc-teal"><div><div class="sc-val">{{ $totalProductos }}</div><div class="sc-lbl">Modelos registrados</div></div><i class="fas fa-box-open sc-icon"></i></div>
  <div class="sc sc-amber"><div><div class="sc-val">{{ $totalStock }}</div><div class="sc-lbl">Piezas en stock</div></div><i class="fas fa-warehouse sc-icon"></i></div>
  <div class="sc sc-red"><div><div class="sc-val">{{ $stockBajo }}</div><div class="sc-lbl">Modelos con alguna talla baja (&lt;5)</div></div><i class="fas fa-exclamation-triangle sc-icon"></i></div>
</div>

<div class="fcard" style="margin-bottom:20px" id="formulario">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-{{ $e ? 'pen' : 'plus-circle' }}" style="color:var(--accent)"></i> {{ $e ? 'Modificar modelo: ' . $e->Nombre : 'Agregar nuevo modelo' }}</div>
  <form action="{{ $e ? route('admin.productos.update', $e) : route('admin.productos.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    @if($e) @method('PUT') @endif

    <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;margin-bottom:14px">
      <div class="img-prev" id="imgPrev">@if($e?->imagen_url)<img src="{{ $e->imagen_url }}" alt="">@else<i class="fas fa-image"></i>@endif</div>
      <div class="fg" style="flex:1;min-width:220px">
        <label>Imagen del modelo (JPG, PNG o WEBP, máx. 4 MB)</label>
        <input class="fc" type="file" name="imagen" id="inpImagen" accept="image/jpeg,image/png,image/webp">
        @if($e?->Imagen)<label style="margin-top:6px;font-weight:500;text-transform:none;letter-spacing:0"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la imagen actual</label>@endif
      </div>
    </div>

    <div class="fgrid">
      <div class="fg"><label>Nombre del modelo *</label><input class="fc" type="text" name="nombre" maxlength="60" placeholder="Ej. Chamarra acolchada" value="{{ old('nombre', $e->Nombre ?? '') }}" required></div>
      <div class="fg"><label>Categoría *</label>
        <select class="fc" name="id_categoria" required><option value="">— Categoría —</option>
          @foreach($categorias as $cat)<option value="{{ $cat->Id_categoria }}" @selected(old('id_categoria', $e->Id_categoria ?? '') == $cat->Id_categoria)>{{ $cat->Nombre }}</option>@endforeach
        </select>
      </div>
      <div class="fg"><label>Para *</label>
        <select class="fc" name="segmento" required>
          @foreach($segmentos as $val => $txt)<option value="{{ $val }}" @selected(old('segmento', $e->Segmento ?? 'CABALLERO') === $val)>{{ $txt }}</option>@endforeach
        </select>
      </div>
      <div class="fg"><label>Color</label><input class="fc" type="text" name="color" maxlength="30" placeholder="Ej. Negro" value="{{ old('color', $e->Color ?? '') }}"></div>
      <div class="fg"><label>Precio proveedor ($) *</label><input class="fc" type="number" step="0.01" min="0" name="preciopro" placeholder="0.00" value="{{ old('preciopro', $e->Preciopro ?? '') }}" required></div>
      <div class="fg"><label>Precio de venta ($) *</label><input class="fc" type="number" step="0.01" min="0" name="precioneto" placeholder="0.00" value="{{ old('precioneto', $e->Precioneto ?? '') }}" required></div>
      <div class="fg"><label>Precio mayoreo ($)</label><input class="fc" type="number" step="0.01" min="0" name="precio_mayoreo" placeholder="Opcional" value="{{ old('precio_mayoreo', $e->Precio_mayoreo ?? '') }}"></div>
      <div class="fg"><label>Mayoreo desde (piezas)</label><input class="fc" type="number" min="2" name="min_mayoreo" value="{{ old('min_mayoreo', $e->Min_mayoreo ?? 5) }}"></div>
    </div>
    <div class="fg"><label>Descripción</label><input class="fc" type="text" name="descripcion" maxlength="255" placeholder="Material, corte, detalles…" value="{{ old('descripcion', $e->Descripcion ?? '') }}"></div>

    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);margin:10px 0 8px">Tallas y stock *</div>
    <div class="talla-row" style="margin-bottom:4px;font-size:11px;color:var(--muted)"><span>Talla</span><span>Piezas</span><span></span></div>
    <div id="tallas">
      @foreach($tallasForm as $i => $t)
      <div class="talla-row">
        <input class="fc inp-talla" type="text" name="tallas[{{ $i }}][talla]" maxlength="5" list="tallasSugeridas" value="{{ $t['talla'] }}" required>
        <input class="fc inp-stock" type="number" min="0" name="tallas[{{ $i }}][stock]" value="{{ $t['stock'] }}" required>
        <button type="button" class="ib ib-del btn-quitar-talla" title="Quitar talla"><i class="fas fa-times"></i></button>
      </div>
      @endforeach
    </div>
    <datalist id="tallasSugeridas">@foreach($sugeridas as $s)<option value="{{ $s }}">@endforeach</datalist>
    <button type="button" id="btnTalla" class="btn btn-sm btn-outline"><i class="fas fa-plus"></i> Agregar talla</button>
    <span style="font-size:12px;color:var(--muted);margin-left:8px">Total: <strong id="totalPiezas">0</strong> piezas · Usa <b>UNI</b> para talla única</span>

    <div style="margin:14px 0">
      <label style="font-weight:600;cursor:pointer"><input type="hidden" name="activo" value="0"><input type="checkbox" name="activo" value="1" @checked(old('activo', $e ? $e->Activo : true))> Visible en la tienda</label>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $e ? 'Guardar cambios' : 'Agregar modelo' }}</button>
    @if($e)<a href="{{ route('admin.productos.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancelar</a>@endif
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr" style="flex-wrap:wrap;gap:10px">
    <div class="tcard-ttl"><i class="fas fa-shirt" style="color:var(--accent)"></i> Catálogo ({{ $productos->count() }})</div>
    <form method="get" action="{{ route('admin.productos.index') }}" class="filtros">
      <input class="fc" type="search" name="buscar" placeholder="Buscar modelo…" value="{{ $filtros['buscar'] ?? '' }}">
      <select class="fc" name="categoria" onchange="this.form.submit()"><option value="">Todas las categorías</option>
        @foreach($categorias as $cat)<option value="{{ $cat->Id_categoria }}" @selected(($filtros['categoria'] ?? '') == $cat->Id_categoria)>{{ $cat->Nombre }}</option>@endforeach
      </select>
      <select class="fc" name="segmento" onchange="this.form.submit()"><option value="">Todos</option>
        @foreach($segmentos as $val => $txt)<option value="{{ $val }}" @selected(($filtros['segmento'] ?? '') === $val)>{{ $txt }}</option>@endforeach
      </select>
    </form>
  </div>

  @if($productos->isEmpty())
    <div style="text-align:center;color:var(--muted);padding:30px">No hay modelos que coincidan.</div>
  @else
  <div class="prod-grid">
    @foreach($productos as $p)
    <div class="prod-card {{ $p->Activo ? '' : 'inactivo' }}">
      <div class="foto">@if($p->imagen_url)<img src="{{ $p->imagen_url }}" alt="{{ $p->Nombre }}" loading="lazy">@else<i class="fas fa-shirt"></i>@endif</div>
      <div style="min-width:0;padding-right:54px">
        <h4>{{ $p->Nombre }}</h4>
        <div class="meta">{{ $p->categoria?->Nombre ?? '—' }} · {{ $p->segmento_texto }}@if($p->Color) · {{ $p->Color }}@endif @unless($p->Activo)· <b>Oculto</b>@endunless</div>
        <div class="precio">${{ number_format($p->Precioneto, 2) }}</div>
        @if($p->tiene_mayoreo)<div class="mayoreo">Mayoreo +{{ $p->Min_mayoreo }} pz: ${{ number_format($p->Precio_mayoreo, 2) }}</div>@endif
        <div class="chips">
          @foreach($p->tallas_ordenadas as $t)
          <span class="chip {{ $t->Stock <= 0 ? 'cero' : ($t->Stock < 5 ? 'bajo' : '') }}" title="{{ $t->Stock }} pieza(s)">{{ $t->Talla }}: {{ $t->Stock }}</span>
          @endforeach
        </div>
      </div>
      <div class="acciones">
        <a href="{{ route('admin.productos.edit', $p) }}#formulario" class="ib ib-edit" title="Modificar"><i class="fas fa-pencil-alt"></i></a>
        <form action="{{ route('admin.productos.destroy', $p) }}" method="post" onsubmit="return confirm('¿Eliminar el modelo {{ addslashes($p->Nombre) }}? Si ya tiene ventas, mejor ocúltalo desmarcando «Visible en la tienda».')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form>
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
  const cont = document.getElementById('tallas');
  function renumerar() {
    cont.querySelectorAll('.talla-row').forEach((r, i) => {
      r.querySelector('.inp-talla').name = `tallas[${i}][talla]`;
      r.querySelector('.inp-stock').name = `tallas[${i}][stock]`;
    });
    let total = 0;
    cont.querySelectorAll('.inp-stock').forEach(i => total += Math.max(0, parseInt(i.value, 10) || 0));
    document.getElementById('totalPiezas').textContent = total;
    cont.querySelectorAll('.btn-quitar-talla').forEach(b => b.style.visibility = cont.children.length > 1 ? 'visible' : 'hidden');
  }
  document.getElementById('btnTalla').addEventListener('click', () => {
    const n = cont.firstElementChild.cloneNode(true);
    n.querySelector('.inp-talla').value = '';
    n.querySelector('.inp-stock').value = 0;
    cont.appendChild(n); renumerar(); n.querySelector('.inp-talla').focus();
  });
  cont.addEventListener('click', e => {
    const b = e.target.closest('.btn-quitar-talla');
    if (b && cont.children.length > 1) { b.closest('.talla-row').remove(); renumerar(); }
  });
  cont.addEventListener('input', renumerar);
  renumerar();

  // Vista previa de la imagen elegida
  document.getElementById('inpImagen').addEventListener('change', e => {
    const f = e.target.files[0];
    if (f) document.getElementById('imgPrev').innerHTML = `<img src="${URL.createObjectURL(f)}" alt="">`;
  });
})();
</script>
@endpush
