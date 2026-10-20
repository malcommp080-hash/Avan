<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraAcceso extends Model
{
    protected $table = 'bitacora_acceso';
    protected $primaryKey = 'Id_bitacora';
    public $timestamps = false;

    protected $fillable = ['Tipo', 'Telefono', 'Id_usuario', 'Resultado', 'Ip', 'Navegador', 'Creado_en'];

    protected $casts = ['Creado_en' => 'datetime'];
}
