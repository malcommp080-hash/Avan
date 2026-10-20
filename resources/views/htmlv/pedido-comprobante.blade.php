@extends('htmlv.layout')

@section('titulo', 'Confirmación de pago')
@section('sinPie', true)

@section('contenido')
<div class="panel">
  @include('htmlv.partials.barra', ['titulo' => 'Confirmación de pago', 'volver' => route('tienda.pedido.pago', $venta), 'volverTexto' => 'Cerrar'])

  <div class="contenido">
    @include('htmlv.partials.avisos')

    <form action="{{ route('tienda.pedido.comprobante.subir', $venta) }}" method="post" enctype="multipart/form-data" id="formTicket">
      @csrf
      <div class="dos-col" style="grid-template-columns:1.3fr 1fr;align-items:center">
        <label class="subir" id="zona" for="ticket">
          <input type="file" name="ticket" id="ticket" accept="image/jpeg,image/png,image/webp,application/pdf" required>
          <div class="vista" id="vista">
            @if($venta->pago->comprobante_url)<img src="{{ $venta->pago->comprobante_url }}" alt="Ticket enviado">@endif
          </div>
          <div class="ttl" id="ttlSubir">Sube tu ticket de pago <span aria-hidden="true">📤</span></div>
          <div style="font-size:13px;color:var(--tenue)">Toca para elegir una foto o PDF (máx. 5 MB) o arrástralo aquí</div>
        </label>

        <div style="display:flex;flex-direction:column;gap:24px;align-items:stretch">
          <div class="campo-pildora"><label for="referencia">Referencia o núm. de operación (opcional)</label>
            <input id="referencia" name="referencia" maxlength="100" value="{{ old('referencia', $venta->pago->Referencia) }}"></div>
          <button type="submit" class="pildora" style="min-height:60px;font-size:19px">✅ Enviar pago</button>
          <a href="{{ route('tienda.pedido.pago', $venta) }}" class="pildora" style="min-height:60px;font-size:19px">✖ Cancelar</a>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const inp = document.getElementById('ticket'), zona = document.getElementById('zona'), vista = document.getElementById('vista'), ttl = document.getElementById('ttlSubir');
  function mostrar(f) {
    if (!f) return;
    ttl.textContent = f.name;
    vista.innerHTML = f.type.startsWith('image/') ? `<img src="${URL.createObjectURL(f)}" alt="Vista previa">` : '<div style="font-size:60px">📄</div>';
  }
  inp.addEventListener('change', () => mostrar(inp.files[0]));
  ['dragenter', 'dragover'].forEach(ev => zona.addEventListener(ev, e => { e.preventDefault(); zona.classList.add('arrastrando'); }));
  ['dragleave', 'drop'].forEach(ev => zona.addEventListener(ev, e => { e.preventDefault(); zona.classList.remove('arrastrando'); }));
  zona.addEventListener('drop', e => { if (e.dataTransfer.files.length) { inp.files = e.dataTransfer.files; mostrar(inp.files[0]); } });
})();
</script>
@endpush
