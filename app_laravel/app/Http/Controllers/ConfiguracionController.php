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
        return redirect()->route('configuraciones.general');
    }

    public function general()
    {
        $data = [
            'nombre_institucion' => Configuracion::valor('nombre_institucion', 'Sistema de Asistencia'),
            'hora_tardanza' => Configuracion::valor('hora_tardanza', '08:30:00'),
        ];

        return view('configuraciones.general', compact('data'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'nombre_institucion' => ['required', 'string', 'max:120'],
            'hora_tardanza' => ['required', 'date_format:H:i:s'],
        ]);

        Configuracion::guardar('nombre_institucion', $validated['nombre_institucion']);
        Configuracion::guardar('hora_tardanza', $validated['hora_tardanza']);

        return back()->with('ok', 'Configuraciones guardadas correctamente.');
    }

    public function tiposPersonal()
    {
        $tiposPersonal = TipoPersonal::orderBy('id', 'asc')->get();

        return view('configuraciones.tipos-personal', compact('tiposPersonal'));
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

    public function oficinas()
    {
        $oficinas = Oficina::orderBy('id', 'asc')->get();

        return view('configuraciones.oficinas', compact('oficinas'));
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

    public function turnos()
    {
        $turnos = Turno::orderBy('id', 'asc')->get();

        return view('configuraciones.turnos', compact('turnos'));
    }

    public function storeTurno(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:turno,nombre'],
            'hora_entrada' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'hora_tardanza' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'hora_salida' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
        ]);

        Turno::create([
            'nombre' => trim($validated['nombre']),
            'hora_entrada' => $this->normalizarHora($validated['hora_entrada']),
            'hora_tardanza' => $this->normalizarHora($validated['hora_tardanza']),
            'hora_salida' => $this->normalizarHora($validated['hora_salida'] ?? null),
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
            'hora_entrada' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'hora_tardanza' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'hora_salida' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
        ]);

        $turno->update([
            'nombre' => trim($validated['nombre']),
            'hora_entrada' => $this->normalizarHora($validated['hora_entrada']),
            'hora_tardanza' => $this->normalizarHora($validated['hora_tardanza']),
            'hora_salida' => $this->normalizarHora($validated['hora_salida'] ?? null),
        ]);

        return back()->with('ok', 'Turno actualizado correctamente.');
    }

    private function normalizarHora(?string $hora): ?string
    {
        if ($hora === null || $hora === '') {
            return null;
        }

        return strlen($hora) === 5 ? $hora . ':00' : $hora;
    }
}
