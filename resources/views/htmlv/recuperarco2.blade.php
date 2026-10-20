@extends('htmlv.auth')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
<form action="{{ route('tienda.recuperar.guardar') }}" method="post" class="auth-card">
  @csrf
  <h2>Cuenta encontrada: {{ $cliente->Nombre }}</h2>
  <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'clave'])
    <div><label for="nueva">Nueva contraseña</label><input type="password" id="nueva" name="nueva" minlength="6" maxlength="100" autocomplete="new-password" required autofocus>
      <div class="ayuda">Mínimo 6 caracteres</div></div></div>
  <div class="auth-fila">@include('htmlv.partials.icono', ['i' => 'clave'])
    <div><label for="confirmar">Confirma tu contraseña</label><input type="password" id="confirmar" name="confirmar" minlength="6" maxlength="100" autocomplete="new-password" required></div></div>
  <div style="text-align:center;margin-top:20px">
    <button type="submit" class="btn-auth redondo">Guardar contraseña</button><br>
    <a class="auth-volver" href="{{ route('tienda.recuperar') }}" style="font-size:15px">← Volver</a>
  </div>
</form>
@endsection
