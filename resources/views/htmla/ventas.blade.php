@extends('htmla.layout')

@section('titulo', 'Registro de Ventas')

@push('head')
<style>
  .renglon { display:grid; grid-template-columns: minmax(180px,3fr) minmax(80px,1fr) minmax(100px,1fr) minmax(100px,1fr) 40px; gap:10px; align-items:end; margin-bottom:10px; }
  .renglon .fg { margin-bottom:0; }
  .renglon .lectura { background:transparent; border-style:dashed; }
  .total-venta { display:flex; justify-content:flex-end; align-items:baseline; gap:12px; margin:16px 0; font-size:15px; color:var(--muted); }
  .total-venta strong { font-size:26px; color:var(--text); }
  @media (max-width: 720px) {
    .renglon { grid-template-columns: 1fr 1fr; padding-bottom:10px; border-bottom:1px dashed rgba(127,127,127,.3); }
    .renglon .fg:first-child { grid-column: 1 / -1; }
  }
</style>
@endpush

@section('contenido')
<div class="ptitle">Registro de Ventas</div>
<div class="psub">Selecciona los productos, la cantidad y la forma de pago; el total se calcula solo</div>

@php
  $items = old('items', [['id_producto' => '', 'cantidad' => 1]]);
@endphp

<div class="fcard" style="margin-bottom:20px">
  <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"><i class="fas fa-cash-register" style="color:var(--accent2)"></i> Nueva venta</div>

  @if($productos->isEmpty())
    <div class="alert alert-warn"><i class="fas fa-triangle-exclamation"></i> No hay productos registrados. <a href="{{ route('admin.productos.index') }}">Registra productos</a> antes de vender.</div>
  @endif

  <form id="formVenta" action="{{ route('admin.ventas.store') }}" method="post" novalidate>
    @csrf
    <div class="fgrid">
      <div class="fg"><label>Cliente *</label>
        <select class="fc" name="id_cliente" data-requerido>
          <option value="">-- Selecciona cliente --</option>
          @foreach($clientes as $c)
          <option value="{{ $c->Id_cliente }}" @selected(old('id_cliente') == $c->Id_cliente)>{{ $c->nombre_completo }} ({{ $c->Telefono }})</option>
          @endforeach
        </select>
      </div>
      <div class="fg"><label>Forma de pago *</label>
        <select class="fc" name="forma_pago" data-requerido>
          <option value="">-- Selecciona --</option>
          @foreach($formas as $val => $txt)
          <option value="{{ $val }}" @selected(old('forma_pago') === $val)>{{ $txt }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);margin:6px 0 10px">Productos</div>
    <div id="renglones">
      @foreach($items as $i => $it)
      <div class="renglon">
        <div class="fg"><label>Producto *</label>
          <select class="fc sel-producto" name="items[{{ $i }}][id_producto]" data-requerido>
            <option value="">-- Selecciona producto --</option>
            @foreach($productos as $p)
            <option value="{{ $p->Id_producto }}" data-precio="{{ $p->Precioneto }}" data-stock="{{ $p->Stock }}" @selected(($it['id_producto'] ?? '') == $p->Id_producto) @disabled($p->Stock < 1)>{{ $p->Nombre }} — ${{ number_format($p->Precioneto, 2) }} ({{ $p->Stock }} disp.)</option>
            @endforeach
          </select>
        </div>
        <div class="fg"><label>Cantidad *</label><input class="fc inp-cantidad" type="number" min="1" step="1" name="items[{{ $i }}][cantidad]" value="{{ $it['cantidad'] ?? 1 }}" data-requerido></div>
        <div class="fg"><label>Precio unitario</label><input class="fc lectura inp-precio" type="text" value="$0.00" readonly tabindex="-1"></div>
        <div class="fg"><label>Subtotal</label><input class="fc lectura inp-subtotal" type="text" value="$0.00" readonly tabindex="-1"></div>
        <button type="button" class="ib ib-del btn-quitar" title="Quitar producto"><i class="fas fa-times"></i></button>
      </div>
      @endforeach
    </div>

    <button type="button" id="btnAgregar" class="btn btn-sm btn-outline"><i class="fas fa-plus"></i> Agregar otro producto</button>

    <div class="total-venta">Total de la venta <strong id="totalVenta">$0.00</strong></div>

    <div id="avisoCliente" class="alert alert-err" style="display:none"><i class="fas fa-exclamation-circle"></i> <span></span></div>

    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar venta</button>
  </form>
</div>

<div class="tcard">
  <div class="tcard-hdr"><div class="tcard-ttl"><i class="fas fa-list" style="color:var(--green)"></i> Ventas registradas</div></div>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Folio</th><th>Fecha</th><th>Cliente</th><th>Piezas</th><th>Forma de pago</th><th>Total</th><th>Resumen</th><th>Eliminar</th></tr></thead>
    <tbody>
      @forelse($ventas as $v)
      <tr>
        <td>#{{ $v->Id_venta }}</td>
        <td>{{ $v->fecha_texto }}</td>
        <td>{{ $v->cliente?->nombre_completo ?? '—' }}</td>
        <td>{{ $v->Total_piezas }}</td>
        <td>{{ $v->forma_pago_texto }}</td>
        <td>${{ number_format($v->Total, 2) }}</td>
        <td><a href="{{ route('admin.ventas.show', $v) }}" class="ib ib-edit" title="Ver resumen"><i class="fas fa-receipt"></i></a></td>
        <td><form action="{{ route('admin.ventas.destroy', $v) }}" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar la venta #{{ $v->Id_venta }}? Las piezas regresarán al inventario.')">@csrf @method('DELETE')<button type="submit" class="ib ib-del" title="Eliminar"><i class="fas fa-times"></i></button></form></td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No hay ventas registradas.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const cont = document.getElementById('renglones');
  const form = document.getElementById('formVenta');
  const aviso = document.getElementById('avisoCliente');
  const dinero = n => '$' + n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function recalcular() {
    let total = 0;
    cont.querySelectorAll('.renglon').forEach(r => {
      const op = r.querySelector('.sel-producto').selectedOptions[0];
      const precio = op && op.dataset.precio ? parseFloat(op.dataset.precio) : 0;
      const cant = Math.max(0, parseInt(r.querySelector('.inp-cantidad').value, 10) || 0);
      const sub = Math.round(precio * cant * 100) / 100;
      r.querySelector('.inp-precio').value = dinero(precio);
      r.querySelector('.inp-subtotal').value = dinero(sub);
      if (op && op.dataset.stock) r.querySelector('.inp-cantidad').max = op.dataset.stock;
      total += sub;
    });
    document.getElementById('totalVenta').textContent = dinero(total);
    cont.querySelectorAll('.btn-quitar').forEach(b => b.style.visibility = cont.children.length > 1 ? 'visible' : 'hidden');
  }

  // Los nombres items[N][...] se renumeran para que no queden huecos
  function renumerar() {
    cont.querySelectorAll('.renglon').forEach((r, i) => {
      r.querySelector('.sel-producto').name = `items[${i}][id_producto]`;
      r.querySelector('.inp-cantidad').name = `items[${i}][cantidad]`;
    });
  }

  document.getElementById('btnAgregar').addEventListener('click', () => {
    const nuevo = cont.firstElementChild.cloneNode(true);
    nuevo.querySelector('.sel-producto').value = '';
    nuevo.querySelector('.inp-cantidad').value = 1;
    cont.appendChild(nuevo);
    renumerar(); recalcular();
    nuevo.querySelector('.sel-producto').focus();
  });

  cont.addEventListener('click', e => {
    const b = e.target.closest('.btn-quitar');
    if (b && cont.children.length > 1) { b.closest('.renglon').remove(); renumerar(); recalcular(); }
  });
  cont.addEventListener('input', recalcular);
  cont.addEventListener('change', recalcular);

  // RF002.4: mismo mensaje en el navegador antes de enviar (el servidor también lo valida)
  form.addEventListener('submit', e => {
    let msg = '';
    const vacio = [...form.querySelectorAll('[data-requerido]')].some(c => !c.value || (c.type === 'number' && parseInt(c.value, 10) < 1));
    if (vacio) msg = 'Complete todos los campos antes de guardar.';
    if (!msg) {
      const ids = [...cont.querySelectorAll('.sel-producto')].map(s => s.value);
      if (new Set(ids).size !== ids.length) msg = 'Un producto aparece dos veces; junta las cantidades en un solo renglón.';
    }
    if (!msg) {
      cont.querySelectorAll('.renglon').forEach(r => {
        const op = r.querySelector('.sel-producto').selectedOptions[0];
        const cant = parseInt(r.querySelector('.inp-cantidad').value, 10);
        if (!msg && op && cant > parseInt(op.dataset.stock, 10)) msg = `Sólo hay ${op.dataset.stock} pieza(s) de "${op.text.split(' — ')[0]}" en inventario.`;
      });
    }
    if (msg) {
      e.preventDefault();
      aviso.querySelector('span').textContent = msg;
      aviso.style.display = 'flex';
    }
  });

  recalcular();
})();
</script>
@endpush
