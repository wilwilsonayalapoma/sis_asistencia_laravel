<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionOficina extends Model
{
    use HasFactory;

    protected $table = 'asignacion_oficina';
    protected $fillable = [
        'personal_id',
        'oficina_id',
        'tipo_personal_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    const CREATED_AT = 'creado_el';
    const UPDATED_AT = 'actualizado_el';

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function tipoPersonal()
    {
        return $this->belongsTo(TipoPersonal::class, 'tipo_personal_id');
    }
}
