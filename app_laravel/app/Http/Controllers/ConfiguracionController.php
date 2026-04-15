<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Oficina;
use App\Models\TipoPersonal;
use App\Models\Turno;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $data = [
            'nombre_institucion' => Configuracion::valor('nombre_institucion', 'Sistema de Asistencia'),
            'hora_tardanza' => Configuracion::valor('hora_tardanza', '08:30:00'),
        ];

        $tiposPersonal = TipoPersonal::orderBy('id', 'asc')->get();
        $oficinas = Oficina::orderBy('id', 'asc')->get();
        $turnos = Turno::orderBy('id', 'asc')->get();

        return view('configuraciones.index', compact('data', 'tiposPersonal', 'oficinas', 'turnos'));
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

    public function storeTipoPersonal(Request $request)
    {
        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:60', 'unique:tipo_personal,tipo'],
        ]);

        TipoPersonal::create([
            'tipo' => trim($validated['tipo']),
            'estado' => 1,
        ]);

        return back()->with('ok', 'Tipo de personal creado correctamente.');
    }

    public function cambiarEstadoTipoPersonal(TipoPersonal $tipoPersonal)
    {
        $tipoPersonal->estado = $tipoPersonal->estado ? 0 : 1;
        $tipoPersonal->save();

        return back()->with('ok', 'Estado de tipo de personal actualizado.');
    }

    public function storeOficina(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:oficina,nombre'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Oficina::create([
            'nombre' => trim($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => 1,
        ]);

        return back()->with('ok', 'Oficina creada correctamente.');
    }

    public function cambiarEstadoOficina(Oficina $oficina)
    {
        $oficina->estado = $oficina->estado ? 0 : 1;
        $oficina->save();

        return back()->with('ok', 'Estado de oficina actualizado.');
    }

    public function storeTurno(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:turno,nombre'],
            'hora_entrada' => ['required', 'date_format:H:i:s'],
            'hora_tardanza' => ['required', 'date_format:H:i:s'],
            'hora_salida' => ['nullable', 'date_format:H:i:s'],
        ]);

        Turno::create([
            'nombre' => trim($validated['nombre']),
            'hora_entrada' => $validated['hora_entrada'],
            'hora_tardanza' => $validated['hora_tardanza'],
            'hora_salida' => $validated['hora_salida'] ?? null,
            'estado' => 1,
        ]);

        return back()->with('ok', 'Turno creado correctamente.');
    }

    public function cambiarEstadoTurno(Turno $turno)
    {
        $turno->estado = $turno->estado ? 0 : 1;
        $turno->save();

        return back()->with('ok', 'Estado de turno actualizado.');
    }
    public function editTurno(Turno $turno)
    {
        return response()->json($turno);
    }

    public function updateTurno(Request $request, Turno $turno)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:turno,nombre,' . $turno->id],
            'hora_entrada' => ['required', 'date_format:H:i:s'],
            'hora_tardanza' => ['required', 'date_format:H:i:s'],
            'hora_salida' => ['nullable', 'date_format:H:i:s'],
        ]);

        $turno->update([
            'nombre' => trim($validated['nombre']),
            'hora_entrada' => $validated['hora_entrada'],
            'hora_tardanza' => $validated['hora_tardanza'],
            'hora_salida' => $validated['hora_salida'] ?? null,
        ]);

        return back()->with('ok', 'Turno actualizado correctamente.');
    }
}
