<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Sistema Administrativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="login-box">
    <div class="logo-area">
        <div class="logo-icon"><i class="fas fa-shield-alt" style="color:#fff"></i></div>
        <div class="logo-text">DAV <span>Admin</span></div>
    </div>

    <h1>Bienvenido</h1>
    <p class="sub">Acceso al panel de administración</p>

    @env('local')
    <div class="hint-box">
        💡 Acceso por defecto: <strong>admin</strong> / <strong>admin123</strong><br>
        O usa las credenciales de un empleado registrado.
    </div>
    @endenv

    @if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.login.post') }}" method="post">
        @csrf
        <div class="fg">
            <label>Número de teléfono</label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" name="telefono" placeholder="Teléfono o 'admin'" value="{{ old('telefono') }}" required autofocus>
            </div>
        </div>

        <div class="fg">
            <label>Contraseña</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="contrasena" placeholder="••••••••" required>
            </div>
        </div>

        <div style="text-align:right;margin:-6px 0 14px;font-size:13px">
            <a href="{{ route('admin.recuperar') }}">¿Olvidó su contraseña?</a>
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Iniciar sesión
        </button>
    </form>

    <div class="footer-note">
        ¿Eres cliente? <a href="{{ route('tienda.inicio') }}">Ir a la tienda</a>
    </div>
</div>

</body>
</html>
