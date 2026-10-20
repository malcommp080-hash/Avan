<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificacion';
    protected $primaryKey = 'Id_notificacion';
    public $timestamps = false;

    protected $fillable = ['Id_empleado', 'Tipo', 'Mensaje', 'Url', 'Leida', 'Creado_en'];

    protected $casts = ['Leida' => 'boolean', 'Creado_en' => 'datetime'];

    /** Notificaciones visibles para un empleado: las suyas y las generales (Id_empleado NULL). */
    public function scopePara($q, int $idEmpleado)
    {
        return $q->where(fn ($w) => $w->where('Id_empleado', $idEmpleado)->orWhereNull('Id_empleado'));
    }

    /** Crea una notificación para cada empleado con alguno de los roles dados. */
    public static function aRoles(array $roles, string $tipo, string $mensaje, ?string $url = null): void
    {
        foreach (Empleado::whereIn('rol', $roles)->pluck('Id_empleado') as $id) {
            static::create([
                'Id_empleado' => $id, 'Tipo' => $tipo, 'Mensaje' => $mensaje,
                'Url' => $url, 'Leida' => false, 'Creado_en' => now(),
            ]);
        }
    }
}
