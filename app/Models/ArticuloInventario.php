<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre_articulo', 'codigo', 'stock_actual', 'stock_minimo', 'precio_unitario', 'activo'])]
class ArticuloInventario extends Model
{
    protected $table = 'articulos_inventario';

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function consumibles(): HasMany
    {
        return $this->hasMany(ConsumibleTicket::class, 'articulo_id');
    }

    public function getSubtotalesAttribute(): float
    {
        return (float) $this->consumibles()->sum('cantidad_utilizada');
    }
}
