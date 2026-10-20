@extends('htmlv.auth')

@section('titulo', 'Inicio de sesión')

@section('contenido')
<form action="{{ route('tienda.login.post') }}" method="post" style="display:contents">
  @csrf
  <div class="auth-card">
    <div class="auth-fila">
      @include('htmlv.partials.icono', ['i' => 'tel'])
      <div><label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" inputmode="numeric" maxlength="10" value="{{ old('telefono') }}" autocomplete="tel" required autofocus></div>
    </div>
    <div class="auth-fila">
      @include('htmlv.partials.icono', ['i' => 'clave'])
      <div><label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required></div>
    </div>
    <a class="auth-link" href="{{ route('tienda.recuperar') }}">¿Olvidaste tu contraseña?</a>
  </div>

  <div class="auth-botones">
    <button type="submit" class="btn-auth continuar">Continuar</button>
    <a href="{{ route('tienda.inicio') }}" class="btn-auth cancelar">Cancelar</a>
  </div>
  <div class="auth-botones" style="margin-top:24px">
    <a href="{{ route('tienda.registro') }}" class="btn-auth cuenta">Crear cuenta</a>
  </div>
</form>
@endsection
