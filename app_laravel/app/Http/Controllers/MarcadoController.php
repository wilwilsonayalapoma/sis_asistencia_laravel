<?php

namespace App\Http\Controllers;

use App\Models\AsignacionOficina;
use App\Models\Asistencia;
use App\Models\Configuracion;
use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MarcadoController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today()->toDateString();
        $ordenListado = $request->get('orden', 'desc');
        $apellidoBusqueda = trim((string) $request->get('apellido', ''));

        if (!in_array($ordenListado, ['asc', 'desc'], true)) {
            $ordenListado = 'desc';
        }

        $registros = Asistencia::with(['personal', 'asignacion.oficina'])
            ->whereDate('fecha', $hoy)
            ->when($apellidoBusqueda !== '', function ($query) use ($apellidoBusqueda) {
                $query->whereHas('personal', function ($q) use ($apellidoBusqueda) {
                    $q->where('paterno', 'like', "%{$apellidoBusqueda}%")
                        ->orWhere('materno', 'like', "%{$apellidoBusqueda}%");
                });
            })
            ->orderBy('actualizado_el', $ordenListado)
            ->orderBy('id', $ordenListado)
            ->get();

        return view('marcado.index', compact('registros', 'ordenListado', 'apellidoBusqueda'));
    }

    public function procesar(Request $request)
    {
        $validated = $request->validate([
            'ci' => ['required', 'string', 'max:20'],
            'accion' => ['required', 'in:entrada,salida'],
        ]);

        $personal = Personal::where('ci', $validated['ci'])
            ->where('estado', 1)
            ->first();

        if (!$personal) {
            return back()->with('error', 'No se encontro un empleado activo con ese CI.');
        }

        $hoy = Carbon::today()->toDateString();

        $asignacion = AsignacionOficina::where('personal_id', $personal->id)
            ->where('estado', 1)
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$asignacion) {
            return back()->with('error', 'El empleado no tiene asignacion vigente. Debe registrar una asignacion en la tabla asignacion_oficina.');
        }

        if ($validated['accion'] === 'entrada') {
            $asistencia = Asistencia::firstOrCreate(
                [
                    'personal_id' => $personal->id,
                    'fecha' => $hoy,
                ],
                [
                    'asignacion_oficina_id' => $asignacion->id,
                    'entrada' => now(),
                    'estado' => 'presente',
                ]
            );

            if (!$asistencia->wasRecentlyCreated && $asistencia->entrada !== null) {
                return back()->with('info', 'La entrada ya fue registrada para hoy.');
            }

            if ($asistencia->entrada === null) {
                $asistencia->asignacion_oficina_id = $asignacion->id;
                $asistencia->entrada = now();
            }

            $horaLimite = Configuracion::valor('hora_tardanza', '08:30:00');
            $horaEntrada = Carbon::parse($asistencia->entrada)->format('H:i:s');
            $asistencia->estado = $horaEntrada > $horaLimite ? 'tardanza' : 'presente';
            $asistencia->save();

            return back()->with('ok', 'Entrada registrada correctamente.');
        }

        $asistencia = Asistencia::where('personal_id', $personal->id)
            ->whereDate('fecha', $hoy)
            ->first();

        if (!$asistencia) {
            return back()->with('error', 'Primero se debe registrar la entrada del dia.');
        }

        if ($asistencia->salida !== null) {
            return back()->with('info', 'La salida ya fue registrada para hoy.');
        }

        $asistencia->salida = now();
        $asistencia->save();

        return back()->with('ok', 'Salida registrada correctamente.');
    }
}
