<?php

namespace App\Services;

use App\Models\BitacoraAcceso;
use Illuminate\Support\Facades\RateLimiter;

/**
 * RF001/RF014: bloqueo tras N intentos fallidos consecutivos, bitácora de accesos
 * y cierre de sesión por inactividad. Sirve para el panel (ADMIN) y la tienda (CLIENTE).
 */
class ControlAcceso
{
    public const ADMIN = 'ADMIN';
    public const CLIENTE = 'CLIENTE';

    public const MSG_BLOQUEO = 'Cuenta bloqueada, inténtelo nuevamente en 5 minutos.';

    /** Claves de sesión según el tipo de usuario. */
    private const SESION = [
        self::ADMIN   => ['id' => 'admin_id', 'actividad' => 'admin_ultima_actividad', 'otras' => ['admin_nombre', 'admin_rol']],
        self::CLIENTE => ['id' => 'cliente_id', 'actividad' => 'cliente_ultima_actividad', 'otras' => ['cliente_nombre']],
    ];

    private static function clave(string $tipo, string $telefono): string
    {
        return 'login:' . $tipo . ':' . mb_strtolower(trim($telefono));
    }

    /** ¿La cuenta está bloqueada por demasiados intentos fallidos? */
    public static function bloqueada(string $tipo, string $telefono): bool
    {
        return RateLimiter::tooManyAttempts(self::clave($tipo, $telefono), config('dav.max_intentos'));
    }

    /** Registra un intento fallido; devuelve true si con este intento la cuenta quedó bloqueada. */
    public static function fallo(string $tipo, string $telefono, ?int $idUsuario = null): bool
    {
        $clave = self::clave($tipo, $telefono);
        RateLimiter::hit($clave, config('dav.bloqueo_minutos') * 60);
        $bloqueada = RateLimiter::tooManyAttempts($clave, config('dav.max_intentos'));
        self::registrar($tipo, $telefono, $idUsuario, $bloqueada ? 'BLOQUEADO' : 'FALLIDO');
        return $bloqueada;
    }

    /** Inicio de sesión correcto: limpia el contador, registra y marca la actividad. */
    public static function exito(string $tipo, string $telefono, int $idUsuario): void
    {
        RateLimiter::clear(self::clave($tipo, $telefono));
        self::registrar($tipo, $telefono, $idUsuario, 'EXITO');
        session([self::SESION[$tipo]['actividad'] => time()]);
    }

    /** Cierra la sesión del tipo dado (sin tocar la del otro tipo) y lo deja en la bitácora. */
    public static function cerrar(string $tipo, string $motivo = 'LOGOUT'): void
    {
        $s = self::SESION[$tipo];
        if (session()->has($s['id'])) {
            self::registrar($tipo, '', (int) session($s['id']), $motivo);
        }
        session()->forget(array_merge([$s['id'], $s['actividad']], $s['otras']));
        session()->regenerateToken();
    }

    /**
     * RF014: si pasó el tiempo de inactividad cierra la sesión y devuelve true;
     * si no, renueva la marca de última actividad.
     */
    public static function expirada(string $tipo): bool
    {
        $clave  = self::SESION[$tipo]['actividad'];
        $ultima = session($clave);
        if ($ultima && time() - $ultima > config('dav.inactividad_minutos') * 60) {
            self::cerrar($tipo, 'INACTIVIDAD');
            return true;
        }
        session([$clave => time()]);
        return false;
    }

    public static function registrar(string $tipo, string $telefono, ?int $idUsuario, string $resultado): void
    {
        $req = request();
        BitacoraAcceso::create([
            'Tipo' => $tipo,
            'Telefono' => mb_substr($telefono, 0, 50),
            'Id_usuario' => $idUsuario,
            'Resultado' => $resultado,
            'Ip' => $req->ip(),
            'Navegador' => mb_substr((string) $req->userAgent(), 0, 255),
            'Creado_en' => now(),
        ]);
    }
}
