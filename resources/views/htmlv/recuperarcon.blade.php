@extends('htmlv.auth')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
<form action="{{ route('tienda.recuperar.buscar') }}" method="post" style="display:contents">
  @csrf
  <div class="auth-card">
    <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'tel'])
      <div><label for="telefono">Número de teléfono</label>
        <input type="tel" id="telefono" name="telefono" inputmode="numeric" maxlength="10" placeholder="El teléfono de tu cuenta" value="{{ old('telefono') }}" required autofocus></div></div>
    <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'ubicacion'])
      <div><label for="cp">Código postal registrado</label>
        <input id="cp" name="cp" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" title="5 dígitos" value="{{ old('cp') }}" required>
        <div class="ayuda">Lo pedimos para confirmar que la cuenta es tuya.</div></div></div>
  </div>

  <div class="auth-botones" style="margin-top:-22px">
    <button type="submit" class="btn-auth cuenta">Buscar tu cuenta</button>
  </div>
</form>
<a class="auth-volver" href="{{ route('tienda.login') }}">← Volver al inicio de sesión</a>
@endsection
