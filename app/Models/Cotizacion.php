<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** RF009: cotización que el cliente genera desde su carrito sin pagar. */
class Cotizacion extends Model
{
    protected $table = 'cotizacion';
    protected $primaryKey = 'Id_cotizacion';
    public $timestamps = false;

    protected $fillable = ['Id_cliente', 'Total', 'Estado', 'Atendida_por', 'Atendida_en', 'Creado_en'];

    protected $casts = ['Total' => 'float', 'Atendida_en' => 'datetime', 'Creado_en' => 'datetime'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'Id_cliente', 'Id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class, 'Id_cotizacion', 'Id_cotizacion');
    }

    public function atendio()
    {
        return $this->belongsTo(Empleado::class, 'Atendida_por', 'Id_empleado');
    }
}
