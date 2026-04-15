<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';
    protected $fillable = [
        'personal_id',
        'asignacion_oficina_id',
        'fecha',
        'entrada',
        'salida',
        'estado',
    ];

    const CREATED_AT = 'creado_el';
    const UPDATED_AT = 'actualizado_el';

    protected $casts = [
        'fecha' => 'date',
        'entrada' => 'datetime',
        'salida' => 'datetime',
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function asignacion()
    {
        return $this->belongsTo(AsignacionOficina::class, 'asignacion_oficina_id');
    }
}
