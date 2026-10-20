@extends('htmlv.auth')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
<div class="auth-card auth-exito">
  <h2>Contraseña actualizada</h2>
  <div class="icono" aria-hidden="true">🎉</div>
  <p>¡Contraseña actualizada!</p>
  <a href="{{ route('tienda.login') }}" class="btn-auth redondo">Iniciar sesión →</a>
</div>
@endsection
