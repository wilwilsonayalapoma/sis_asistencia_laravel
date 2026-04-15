<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    use HasFactory;

    protected $table = 'oficina';
    protected $fillable = ['nombre', 'descripcion', 'estado'];

    const CREATED_AT = 'creado_el';
    const UPDATED_AT = 'actualizado_el';

    public function asignaciones()
    {
        return $this->hasMany(AsignacionOficina::class, 'oficina_id');
    }
}
