<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'venta';
    protected $primaryKey = 'Id_venta';
    public $timestamps = false;

    public const FORMAS_PAGO = ['EFECTIVO' => 'Efectivo', 'TARJETA' => 'Tarjeta', 'TRANSFERENCIA' => 'Transferencia'];

    // Las compras en línea se pagan por depósito o transferencia a la cuenta de la tienda
    public const PAGO_EN_LINEA = 'DEPOSITO';

    protected $fillable = ['Fecha', 'Fecha_hora', 'Realizacion', 'Total', 'Forma_pago', 'Origen', 'Direccion_entrega', 'CP_entrega', 'Id_cliente', 'Id_empleado', 'Total_piezas'];

    protected $casts = ['Fecha' => 'date', 'Fecha_hora' => 'datetime', 'Total' => 'float'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'Id_cliente', 'Id_cliente');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'Id_empleado', 'Id_empleado');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'Id_venta', 'Id_venta');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'Id_venta', 'Id_venta');
    }

    /** Las ventas reales: excluye los pedidos a proveedor (se guardan en esta misma tabla con 'PROV:'). */
    public function scopeReales($q)
    {
        return $q->where('Realizacion', 'not like', 'PROV:%');
    }

    /** Fecha con hora si se registró (ventas nuevas); sólo fecha en las ventas anteriores a RF002. */
    public function getFechaTextoAttribute(): string
    {
        return $this->Fecha_hora ? $this->Fecha_hora->format('d/m/Y H:i') : $this->Fecha->format('d/m/Y');
    }

    public function getFormaPagoTextoAttribute(): string
    {
        return (self::FORMAS_PAGO + [self::PAGO_EN_LINEA => 'Depósito / transferencia'])[$this->Forma_pago] ?? '—';
    }
}
