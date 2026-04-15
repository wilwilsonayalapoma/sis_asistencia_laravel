<?php

namespace App\Http\Controllers;

use App\Models\AsignacionOficina;
use App\Models\Oficina;
use App\Models\Personal;
use App\Models\TipoPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->get('q');
        $ordenListado = $request->get('orden', 'az');

        if (!in_array($ordenListado, ['az', 'za'], true)) {
            $ordenListado = 'az';
        }

        $direccion = $ordenListado === 'za' ? 'desc' : 'asc';

        $empleados = Personal::query()
            ->when($busqueda, function ($query) use ($busqueda) {
                $query->where('ci', 'like', "%{$busqueda}%")
                    ->orWhere('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('paterno', 'like', "%{$busqueda}%")
                    ->orWhere('materno', 'like', "%{$busqueda}%");
            })
            ->orderBy('paterno', $direccion)
            ->orderBy('materno', $direccion)
            ->orderBy('nombre', $direccion)
            ->paginate(12)
            ->withQueryString();

        return view('empleados.index', compact('empleados', 'busqueda', 'ordenListado'));
    }

    public function create()
    {
        $oficinas = Oficina::where('estado', 1)->orderBy('nombre')->get();
        $tiposPersonal = TipoPersonal::where('estado', 1)->orderBy('tipo')->get();

        return view('empleados.create', compact('oficinas', 'tiposPersonal'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'paterno' => ['required', 'string', 'max:60'],
            'materno' => ['nullable', 'string', 'max:60'],
            'ci' => ['required', 'string', 'max:20', 'unique:personal,ci'],
            'correo' => ['nullable', 'email', 'max:120'],
            'celular' => ['nullable', 'string', 'max:20'],
            'estado' => ['required', 'boolean'],
            'oficina_id' => ['required', 'integer', 'exists:oficina,id'],
            'tipo_personal_id' => ['required', 'integer', 'exists:tipo_personal,id'],
            'fecha_inicio' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($data) {
            $empleado = Personal::create([
                'nombre' => $data['nombre'],
                'paterno' => $data['paterno'],
                'materno' => $data['materno'] ?? null,
                'ci' => $data['ci'],
                'correo' => $data['correo'] ?? null,
                'celular' => $data['celular'] ?? null,
                'estado' => $data['estado'],
            ]);

            AsignacionOficina::create([
                'personal_id' => $empleado->id,
                'oficina_id' => $data['oficina_id'],
                'tipo_personal_id' => $data['tipo_personal_id'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => null,
                'estado' => 1,
            ]);
        });

        return redirect()->route('empleados.index')->with('ok', 'Empleado creado correctamente con asignacion vigente.');
    }

    public function edit(Personal $empleado)
    {
        return view('empleados.edit', compact('empleado'));
    }

    public function update(Request $request, Personal $empleado)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'paterno' => ['required', 'string', 'max:60'],
            'materno' => ['nullable', 'string', 'max:60'],
            'ci' => ['required', 'string', 'max:20', 'unique:personal,ci,' . $empleado->id],
            'correo' => ['nullable', 'email', 'max:120'],
            'celular' => ['nullable', 'string', 'max:20'],
            'estado' => ['required', 'boolean'],
        ]);

        $empleado->update($data);

        return redirect()->route('empleados.index')->with('ok', 'Empleado actualizado correctamente.');
    }

    public function cambiarEstado(Personal $empleado)
    {
        $empleado->estado = $empleado->estado ? 0 : 1;
        $empleado->save();

        return back()->with('ok', 'Estado del empleado actualizado.');
    }
}
