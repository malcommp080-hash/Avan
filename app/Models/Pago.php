<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pago de una venta en línea; lo aprueba o rechaza un administrador (RF004). */
class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'Id_pago';
    public $timestamps = false;

    public const ESTADOS = ['PENDIENTE' => 'Pendiente de validar', 'APROBADO' => 'Aprobado', 'RECHAZADO' => 'Rechazado'];

    protected $fillable = ['Folio', 'Id_venta', 'Monto', 'Metodo', 'Estado', 'Referencia', 'Comprobante', 'Comprobante_en', 'Validado_por', 'Validado_en', 'Motivo_rechazo', 'Creado_en'];

    protected $casts = ['Monto' => 'float', 'Comprobante_en' => 'datetime', 'Validado_en' => 'datetime', 'Creado_en' => 'datetime'];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'Id_venta', 'Id_venta');
    }

    public function validador()
    {
        return $this->belongsTo(Empleado::class, 'Validado_por', 'Id_empleado');
    }

    public function getEstadoTextoAttribute(): string
    {
        if ($this->Estado === 'PENDIENTE' && !$this->Comprobante) return 'Esperando tu ticket de pago';
        return self::ESTADOS[$this->Estado] ?? $this->Estado;
    }

    public function getComprobanteUrlAttribute(): ?string
    {
        return $this->Comprobante ? asset('img/comprobantes/' . $this->Comprobante) : null;
    }

    /** Folio legible y único, p. ej. P-20261020-000123. */
    public static function nuevoFolio(int $idVenta): string
    {
        return 'P-' . now()->format('Ymd') . '-' . str_pad((string) $idVenta, 6, '0', STR_PAD_LEFT);
    }
}
