<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuracion';
    protected $fillable = ['clave', 'valor'];

    const CREATED_AT = 'creado_el';
    const UPDATED_AT = 'actualizado_el';

    public static function valor(string $clave, ?string $default = null): ?string
    {
        try {
            if (!Schema::hasTable('configuracion')) {
                return $default;
            }

            $row = static::where('clave', $clave)->first();
            return $row ? $row->valor : $default;
        } catch (QueryException $e) {
            return $default;
        }
    }

    public static function guardar(string $clave, string $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }
}
