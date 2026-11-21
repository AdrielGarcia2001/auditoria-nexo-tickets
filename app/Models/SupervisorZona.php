<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['supervisor_id', 'zona_id'])]
class SupervisorZona extends Model
{
    protected $table = 'supervisor_zonas';

    protected $casts = [
        'asignado_en' => 'datetime',
    ];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }
}
