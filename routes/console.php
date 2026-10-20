<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Por si el único administrador olvida su contraseña: php artisan dav:password admin NuevaClave123
Artisan::command('dav:password {telefono} {contrasena}', function (string $telefono, string $contrasena) {
    $empleado = \App\Models\Empleado::where('Telefono', $telefono)->first();
    if (!$empleado) {
        $this->error("No hay ningún empleado con el teléfono {$telefono}.");
        return 1;
    }
    $empleado->Contrasena = \Illuminate\Support\Facades\Hash::make($contrasena);
    $empleado->save();
    \Illuminate\Support\Facades\RateLimiter::clear('login:ADMIN:' . mb_strtolower($telefono));
    $this->info("Contraseña de {$empleado->Nombre} {$empleado->Ap_paterno} actualizada y bloqueo eliminado.");
})->purpose('Restablece la contraseña de un empleado');
