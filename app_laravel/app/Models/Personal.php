<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';
    protected $fillable = [
        'ci',
        'nombre',
        'paterno',
        'materno',
        'correo',
        'celular',
        'estado',
    ];

    const CREATED_AT = 'creado_el';
    const UPDATED_AT = 'actualizado_el';

    public function asignaciones()
    {
        return $this->hasMany(AsignacionOficina::class, 'personal_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'personal_id');
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->paterno . ' ' . ($this->materno ?? '') . ' ' . $this->nombre);
    }
}
