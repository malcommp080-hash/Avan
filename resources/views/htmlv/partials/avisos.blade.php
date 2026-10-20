@if(session('ok'))
<div class="alerta ok" role="status">✅ {{ session('ok') }}
  @if(session('agregado'))<a href="{{ route('tienda.carrito') }}">Ver carrito →</a>@endif
</div>
@endif
@if(session('err'))<div class="alerta err" role="alert">⚠️ {{ session('err') }}</div>@endif
@if($errors->any())<div class="alerta err" role="alert">⚠️ {{ $errors->first() }}</div>@endif
