<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre_empresa', 'contacto', 'telefono', 'email', 'activo'])]
class Cliente extends Model
{
    protected $table = 'clientes';

    protected $casts = ['activo' => 'boolean'];

    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class);
    }
}
