<?php

return [
    // RF014: minutos sin actividad antes de cerrar la sesión automáticamente
    'inactividad_minutos' => (int) env('DAV_INACTIVIDAD_MINUTOS', 15),

    // RF001: intentos fallidos consecutivos antes de bloquear y duración del bloqueo
    'max_intentos'     => (int) env('DAV_MAX_INTENTOS', 5),
    'bloqueo_minutos'  => (int) env('DAV_BLOQUEO_MINUTOS', 5),
];
