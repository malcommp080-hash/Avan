@extends('htmlv.auth')

@section('titulo', 'Crear cuenta')

@section('contenido')
<form action="{{ route('tienda.registro.post') }}" method="post" style="display:contents">
  @csrf
  <div class="auth-card ancha">
    <div class="auth-dos">
      <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'persona'])
        <div><label for="nombre">Nombre</label><input id="nombre" name="nombre" maxlength="20" value="{{ old('nombre') }}" autocomplete="given-name" required autofocus></div></div>
      <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'apellido'])
        <div><label for="paterno">Apellido paterno</label><input id="paterno" name="paterno" maxlength="20" value="{{ old('paterno') }}" autocomplete="family-name" required></div></div>
    </div>

    <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'tel'])
      <div><label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" title="10 dígitos" value="{{ old('telefono') }}" autocomplete="tel" required>
        <div class="ayuda">10 dígitos. Lo usarás para iniciar sesión.</div></div></div>

    <div class="auth-dos">
      <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'clave'])
        <div><label for="contrasena">Contraseña</label><input type="password" id="contrasena" name="contrasena" minlength="6" maxlength="100" autocomplete="new-password" required>
          <div class="ayuda">Mínimo 6 caracteres</div></div></div>
      <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'clave'])
        <div><label for="confirmar">Confirmar contraseña</label><input type="password" id="confirmar" name="confirmar" minlength="6" maxlength="100" autocomplete="new-password" required></div></div>
    </div>

    <div class="auth-dos">
      <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'casa'])
        <div><label for="direccion">Dirección</label><input id="direccion" name="direccion" maxlength="100" value="{{ old('direccion') }}" autocomplete="street-address" required></div></div>
      <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'ubicacion'])
        <div><label for="cp">Código postal</label><input id="cp" name="cp" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" title="5 dígitos" value="{{ old('cp') }}" autocomplete="postal-code" required></div></div>
    </div>
    <a class="auth-link" href="{{ route('tienda.login') }}">¿Ya tienes cuenta? Inicia sesión</a>
  </div>

  <div class="auth-botones">
    <button type="submit" class="btn-auth continuar">Continuar</button>
    <a href="{{ route('tienda.inicio') }}" class="btn-auth cancelar">Cancelar</a>
  </div>
</form>
@endsection
