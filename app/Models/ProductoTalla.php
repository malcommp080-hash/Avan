<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoTalla extends Model
{
    protected $table = 'producto_talla';
    protected $primaryKey = 'Id_pt';
    public $timestamps = false;

    protected $fillable = ['Id_producto', 'Talla', 'Stock'];

    /** Tallas sugeridas, en el orden en que se muestran. Se aceptan otras (p. ej. 28, 30, 32 para jeans). */
    public const SUGERIDAS = ['XCH', 'CH', 'M', 'G', 'XG', 'XXG', 'UNI'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Id_producto', 'Id_producto');
    }

    /** Clave para ordenar: primero las sugeridas en su orden, luego números, luego el resto. */
    public static function orden(string $talla): string
    {
        $i = array_search(strtoupper($talla), self::SUGERIDAS, true);
        if ($i !== false) return sprintf('0%02d', $i);
        if (is_numeric($talla)) return sprintf('1%05.1f', (float) $talla);
        return '2' . $talla;
    }
}
