<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nombre_archivo', 'hash_archivo', 'usuario_id', 'total_filas', 'importados', 'importado_at'])]
class ImportacionExcel extends Model
{
    protected $table = 'importaciones_excel';

    protected $casts = [
        'total_filas' => 'integer',
        'importados' => 'integer',
        'importado_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Verifica si ya se importó este archivo (por hash). */
    public static function yaImportado(string $hash): ?self
    {
        return self::where('hash_archivo', $hash)->first();
    }
}
