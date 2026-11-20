<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'codigo', 'descripcion', 'activo'])]
class Zona extends Model
{
    protected $table = 'zonas';

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class);
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->where('activo', true);
    }
}
