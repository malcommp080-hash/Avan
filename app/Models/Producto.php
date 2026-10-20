<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'registroproductos';
    protected $primaryKey = 'Id_producto';
    public $timestamps = false;

    public const SEGMENTOS = ['CABALLERO' => 'Caballero', 'DAMA' => 'Dama', 'NINO' => 'Niño', 'NINA' => 'Niña', 'UNISEX' => 'Unisex'];

    protected $fillable = [
        'Nombre', 'Stock', 'Id_categoria', 'Segmento', 'Color', 'Descripcion',
        'Preciopro', 'Precioneto', 'Precio_mayoreo', 'Min_mayoreo', 'Imagen', 'Activo',
    ];

    protected $casts = [
        'Preciopro' => 'float', 'Precioneto' => 'float', 'Precio_mayoreo' => 'float',
        'Min_mayoreo' => 'integer', 'Activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'Id_categoria', 'Id_categoria');
    }

    public function tallas()
    {
        return $this->hasMany(ProductoTalla::class, 'Id_producto', 'Id_producto');
    }

    /** Tallas ordenadas CH, M, G… para mostrarlas. */
    public function getTallasOrdenadasAttribute()
    {
        return $this->tallas->sortBy(fn ($t) => ProductoTalla::orden($t->Talla))->values();
    }

    public function getSegmentoTextoAttribute(): string
    {
        return self::SEGMENTOS[$this->Segmento] ?? $this->Segmento;
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->Imagen ? asset('img/productos/' . $this->Imagen) : null;
    }

    public function getTieneMayoreoAttribute(): bool
    {
        return $this->Precio_mayoreo !== null && $this->Precio_mayoreo > 0 && $this->Min_mayoreo > 0;
    }

    /** Precio unitario según la cantidad total de este producto (todas las tallas): mayoreo desde Min_mayoreo piezas. */
    public function precioPara(int $cantidad): float
    {
        return ($this->tiene_mayoreo && $cantidad >= $this->Min_mayoreo) ? $this->Precio_mayoreo : $this->Precioneto;
    }

    /** Mantiene registroproductos.Stock como la suma de las tallas. */
    public function recalcularStock(): void
    {
        $this->Stock = (int) $this->tallas()->sum('Stock');
        $this->save();
    }
}
