<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña — Sistema Administrativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="login-box">
    <div class="logo-area">
        <div class="logo-icon"><i class="fas fa-key" style="color:#fff"></i></div>
        <div class="logo-text">DAV <span>Admin</span></div>
    </div>

    <h1>¿Olvidó su contraseña?</h1>
    <p class="sub">Escribe tu número de teléfono y avisaremos al administrador para que la restablezca.</p>

    @if(session('ok'))
    <div class="hint-box">✅ {{ session('ok') }}</div>
    @endif

    @if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.recuperar.post') }}" method="post">
        @csrf
        <div class="fg">
            <label>Número de teléfono</label>
            <div class="input-wrap">
                <i class="fas fa-phone"></i>
                <input type="text" name="telefono" placeholder="Teléfono registrado" value="{{ old('telefono') }}" required autofocus>
            </div>
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-paper-plane"></i> Enviar solicitud
        </button>
    </form>

    <div class="footer-note">
        <a href="{{ route('admin.login') }}"><i class="fas fa-arrow-left"></i> Volver a iniciar sesión</a>
    </div>
</div>

</body>
</html>
