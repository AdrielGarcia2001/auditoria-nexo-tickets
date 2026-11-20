<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'email', 'empresa', 'telefono', 'mensaje', 'ip'])]
class DemoSolicitud extends Model
{
    protected $table = 'demo_solicitudes';
}
