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
    public function publico()
    {
        return view('public.asistencia');
    }

    public function registrarPublico(Request $request)
    {
        $validated = $request->validate([
            'ci' => ['required', 'string', 'max:20'],
        ]);

        $personal = Personal::where('ci', $validated['ci'])
            ->where('estado', 1)
            ->first();

        if (!$personal) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se encontro un empleado activo con ese CI.',
            ], 422);
        }

        $momento = now();
        $hoy = $momento->toDateString();
        $asignacion = $this->obtenerAsignacionVigente($personal->id, $hoy);

        if (!$asignacion) {
            $ayer = $momento->copy()->subDay()->toDateString();
            $asignacion = $this->obtenerAsignacionVigente($personal->id, $ayer);
        }

        if (!$asignacion) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El empleado no tiene asignacion vigente.',
            ], 422);
        }

        $fechaLaboral = $this->obtenerFechaLaboral($momento, $asignacion);
        $resultado = $this->registrarAutomatico($personal, $asignacion, $fechaLaboral, $momento);

        return response()->json($resultado, $resultado['ok'] ? 200 : 422);
    }

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

        $asignacion = $this->obtenerAsignacionVigente($personal->id, $hoy);

        if (!$asignacion) {
            return back()->with('error', 'El empleado no tiene asignacion vigente. Debe registrar una asignacion en la tabla asignacion_oficina.');
        }

        if ($validated['accion'] === 'entrada') {
            $resultado = $this->registrarEntrada($personal, $asignacion, $hoy);

            if (!$resultado['ok']) {
                return back()->with('info', $resultado['mensaje']);
            }

            return back()->with('ok', $resultado['mensaje']);
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

    private function obtenerAsignacionVigente(int $personalId, string $hoy): ?AsignacionOficina
    {
        return AsignacionOficina::where('personal_id', $personalId)
            ->with('turno')
            ->where('estado', 1)
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    private function obtenerFechaLaboral(Carbon $momento, AsignacionOficina $asignacion): string
    {
        $turno = $asignacion->turno;

        if (!$turno || !$turno->hora_entrada || !$turno->hora_salida) {
            return $momento->toDateString();
        }

        $horaActual = $momento->format('H:i:s');
        $horaEntradaTurno = Carbon::parse($turno->hora_entrada)->format('H:i:s');
        $horaSalidaTurno = Carbon::parse($turno->hora_salida)->format('H:i:s');
        $esTurnoNocturno = $horaSalidaTurno < $horaEntradaTurno;

        if ($esTurnoNocturno && $horaActual <= $horaSalidaTurno) {
            return $momento->copy()->subDay()->toDateString();
        }

        return $momento->toDateString();
    }

    private function registrarAutomatico(Personal $personal, AsignacionOficina $asignacion, string $fechaLaboral, Carbon $momento): array
    {
        $asistencia = Asistencia::where('personal_id', $personal->id)
            ->whereDate('fecha', $fechaLaboral)
            ->first();

        if (!$asistencia) {
            return $this->registrarEntrada($personal, $asignacion, $fechaLaboral, $momento);
        }

        if ($asistencia->salida === null) {
            if (!$this->correspondeRegistrarSalida($momento, $asignacion, $fechaLaboral)) {
                return [
                    'ok' => false,
                    'tipo_registro' => 'entrada',
                    'mensaje' => 'Usuario ya registro su entrada del dia de hoy.',
                    'nombre_completo' => $personal->nombre_completo,
                    'hora_marcado' => Carbon::parse($asistencia->entrada)->format('H:i:s'),
                    'estado' => $asistencia->estado,
                    'es_tardanza' => $asistencia->estado === 'tardanza',
                ];
            }

            $asistencia->salida = $momento;
            $asistencia->save();

            return [
                'ok' => true,
                'tipo_registro' => 'salida',
                'mensaje' => 'Ha registrado su salida correctamente.',
                'nombre_completo' => $personal->nombre_completo,
                'hora_marcado' => Carbon::parse($asistencia->salida)->format('H:i:s'),
                'estado' => $asistencia->estado,
                'es_tardanza' => $asistencia->estado === 'tardanza',
            ];
        }

        return [
            'ok' => false,
            'tipo_registro' => 'entrada',
            'mensaje' => 'Usuario ya registro su entrada y salida para la jornada laboral.',
            'nombre_completo' => $personal->nombre_completo,
        ];
    }

    private function correspondeRegistrarSalida(Carbon $momento, AsignacionOficina $asignacion, string $fechaLaboral): bool
    {
        $turno = $asignacion->turno;

        if (!$turno || !$turno->hora_entrada || !$turno->hora_salida) {
            return true;
        }

        $horaEntradaTurno = Carbon::parse($turno->hora_entrada)->format('H:i:s');
        $horaSalidaTurno = Carbon::parse($turno->hora_salida)->format('H:i:s');
        $esTurnoNocturno = $horaSalidaTurno < $horaEntradaTurno;

        $momentoSalidaProgramada = Carbon::parse($fechaLaboral . ' ' . $horaSalidaTurno);
        if ($esTurnoNocturno) {
            $momentoSalidaProgramada->addDay();
        }

        return $momento->greaterThanOrEqualTo($momentoSalidaProgramada);
    }

    private function registrarEntrada(Personal $personal, AsignacionOficina $asignacion, string $hoy, ?Carbon $momento = null): array
    {
        $momento = $momento ?? now();

        $asistencia = Asistencia::firstOrCreate(
            [
                'personal_id' => $personal->id,
                'fecha' => $hoy,
            ],
            [
                'asignacion_oficina_id' => $asignacion->id,
                'entrada' => $momento,
                'estado' => 'presente',
            ]
        );

        if (!$asistencia->wasRecentlyCreated && $asistencia->entrada !== null) {
            return [
                'ok' => false,
                'mensaje' => 'La entrada ya fue registrada para hoy.',
                'tipo_registro' => 'entrada',
                'nombre_completo' => $personal->nombre_completo,
            ];
        }

        if ($asistencia->entrada === null) {
            $asistencia->asignacion_oficina_id = $asignacion->id;
            $asistencia->entrada = $momento;
        }

        $horaLimite = $asignacion->turno->hora_tardanza;
        $horaEntrada = Carbon::parse($asistencia->entrada)->format('H:i:s');
        $asistencia->estado = $horaEntrada > $horaLimite ? 'tardanza' : 'presente';
        $asistencia->save();

        return [
            'ok' => true,
            'tipo_registro' => 'entrada',
            'mensaje' => 'Ha registrado su entrada correctamente.',
            'nombre_completo' => $personal->nombre_completo,
            'hora_marcado' => $horaEntrada,
            'estado' => $asistencia->estado,
            'es_tardanza' => $asistencia->estado === 'tardanza',
        ];
    }
}
