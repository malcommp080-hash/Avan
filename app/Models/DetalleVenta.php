<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalleventa';
    protected $primaryKey = 'Id_detallev';
    public $timestamps = false;

    protected $fillable = ['Id_venta', 'Id_producto', 'Talla', 'Cantidad', 'Precio_unitario', 'Subtotal'];

    protected $casts = ['Precio_unitario' => 'float', 'Subtotal' => 'float'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Id_producto', 'Id_producto');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'Id_venta', 'Id_venta');
    }
}
