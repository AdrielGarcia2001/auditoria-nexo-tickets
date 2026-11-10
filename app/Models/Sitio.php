<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'nombre', 'calle', 'provincia_codigo', 'provincia_nombre',
    'lat', 'lng', 'notas',
])]
class Sitio extends Model
{
    protected $table = 'sitios';

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /** Tecnicos con sitio fijo en este sitio. */
    public function tecnicos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tecnicos_sitios', 'sitio_id', 'tecnico_id')
            ->withPivot('asignado_por')
            ->withTimestamps();
    }

    public function contratoSla(): HasOne
    {
        return $this->hasOne(ContratoSla::class);
    }
}
