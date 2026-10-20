<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCotizacion extends Model
{
    protected $table = 'detallecotizacion';
    protected $primaryKey = 'Id_detallec';
    public $timestamps = false;

    protected $fillable = ['Id_cotizacion', 'Id_producto', 'Talla', 'Cantidad', 'Precio_unitario', 'Subtotal'];

    protected $casts = ['Precio_unitario' => 'float', 'Subtotal' => 'float'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Id_producto', 'Id_producto');
    }
}
