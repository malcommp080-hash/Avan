<?php

return [
    // RF014: minutos sin actividad antes de cerrar la sesión automáticamente
    'inactividad_minutos' => (int) env('DAV_INACTIVIDAD_MINUTOS', 15),

    // RF001: intentos fallidos consecutivos antes de bloquear y duración del bloqueo
    'max_intentos'     => (int) env('DAV_MAX_INTENTOS', 5),
    'bloqueo_minutos'  => (int) env('DAV_BLOQUEO_MINUTOS', 5),

    // RF003: datos que ve el cliente para depositar o transferir ("Información de pago" en Figma)
    'banco' => [
        'banco'   => env('DAV_BANCO_NOMBRE', 'BBVA Bancomer'),
        'titular' => env('DAV_BANCO_TITULAR', 'Carlos Morales Juarez'),
        'cuenta'  => env('DAV_BANCO_CUENTA', '222222 222222222'),
        'clabe'   => env('DAV_BANCO_CLABE', '222222'),
    ],

    // Dirección de la tienda (pie de página)
    'direccion' => env('DAV_DIRECCION', 'C. Reforma, San Lucas Atoyatenco, 74053 San Martín Texmelucan de Labastida, Puebla'),
];
