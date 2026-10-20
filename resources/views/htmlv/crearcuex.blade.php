@extends('htmlv.auth')

@section('titulo', 'Crear cuenta')

@section('contenido')
<div class="auth-card auth-exito">
  <h2>Cuenta creada</h2>
  <div class="icono" aria-hidden="true">🎉</div>
  <p>¡Tu cuenta está lista!</p>
  <a href="{{ route('tienda.login') }}" class="btn-auth redondo">Iniciar sesión →</a>
</div>
@endsection
