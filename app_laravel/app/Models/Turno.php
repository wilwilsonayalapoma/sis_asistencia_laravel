<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turno';
    protected $fillable = ['nombre', 'hora_entrada', 'hora_tardanza', 'hora_salida', 'estado'];

    const CREATED_AT = 'creado_el';
    const UPDATED_AT = 'actualizado_el';

    public function asignaciones()
    {
        return $this->hasMany(AsignacionOficina::class, 'turno_id');
    }
}
