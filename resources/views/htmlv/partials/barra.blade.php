{{-- Barra blanca de título con "Regresar"/"Cerrar" a la derecha, como en Figma --}}
<div class="barra-titulo">
  <div class="dentro">
    <h1>{{ $titulo }}</h1>
    @isset($volver)<a class="regresar" href="{{ $volver }}">{{ $volverTexto ?? 'Regresar' }}</a>@endisset
  </div>
</div>
