<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $data = [
            'nombre_institucion' => Configuracion::valor('nombre_institucion', 'Sistema de Asistencia'),
            'hora_tardanza' => Configuracion::valor('hora_tardanza', '08:30:00'),
        ];

        return view('configuraciones.index', compact('data'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nombre_institucion' => ['required', 'string', 'max:120'],
            'hora_tardanza' => ['required', 'date_format:H:i:s'],
        ]);

        Configuracion::guardar('nombre_institucion', $validated['nombre_institucion']);
        Configuracion::guardar('hora_tardanza', $validated['hora_tardanza']);

        return back()->with('ok', 'Configuraciones guardadas correctamente.');
    }
}
