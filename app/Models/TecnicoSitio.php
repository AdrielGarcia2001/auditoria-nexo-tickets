<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sitio_id', 'tecnico_id', 'asignado_por'])]
class TecnicoSitio extends Model
{
    protected $table = 'tecnicos_sitios';

    public function sitio(): BelongsTo
    {
        return $this->belongsTo(Sitio::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
}
