<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Cliente para probar la tienda en local: php artisan db:seed --class=ClientePruebaSeeder */
class ClientePruebaSeeder extends Seeder
{
    public const TELEFONO = '5500000001';
    public const CONTRASENA = 'prueba123';

    public function run(): void
    {
        Cliente::updateOrCreate(['Telefono' => self::TELEFONO], [
            'Nombre' => 'Cliente', 'Ap_paterno' => 'Prueba', 'Contrasena' => Hash::make(self::CONTRASENA),
            'Direccion' => 'Av. Hidalgo 123, Centro', 'CP' => '74000',
        ]);
    }
}
