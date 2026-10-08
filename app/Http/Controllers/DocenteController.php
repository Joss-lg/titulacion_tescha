<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\HorarioDocente;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    public function index()
    {
        $docentes = Docente::withCount('horarios')
            ->orderBy('apellido_paterno')
            ->get();

        return view('docentes.index', compact('docentes'));
    }

    public function create()
    {
        return view('docentes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo'             => 'required|in:asignatura,ptc',
            'email'            => 'nullable|email|unique:docentes,email',
            'telefono'         => 'nullable|string|max:20',
        ]);

        Docente::create($data);

        return redirect()->route('docentes.index')
            ->with('success', 'Docente registrado correctamente.');
    }

    public function show(Docente $docente)
    {
        $horarios = $docente->horarios()->orderByRaw("FIELD(dia,'lunes','martes','miercoles','jueves','viernes','sabado')")->get();
        return view('docentes.show', compact('docente', 'horarios'));
    }

    public function edit(Docente $docente)
    {
        return view('docentes.edit', compact('docente'));
    }

    public function updateHorario(Request $request, Docente $docente, HorarioDocente $horario)
{
    $data = $request->validate([
        'hora_entrada'         => 'required|date_format:H:i',
        'hora_salida'          => 'required|date_format:H:i|after:hora_entrada',
        'tiene_bloque_muerto'  => 'boolean',
        'bloque_muerto_inicio' => 'nullable|date_format:H:i',
        'bloque_muerto_fin'    => 'nullable|date_format:H:i|after:bloque_muerto_inicio',
        'comida_inicio'        => 'nullable|date_format:H:i',
        'comida_fin'           => 'nullable|date_format:H:i|after:comida_inicio',
    ]);

    $data['tiene_bloque_muerto'] = $request->has('tiene_bloque_muerto');

    if ($docente->tipo !== 'ptc') {
        $data['comida_inicio'] = null;
        $data['comida_fin']    = null;
    }

    $horario->update($data);

    return redirect()->route('docentes.show', $docente)
        ->with('success', 'Horario actualizado correctamente.');
}

    public function update(Request $request, Docente $docente)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo'             => 'required|in:asignatura,ptc',
            'email'            => 'nullable|email|unique:docentes,email,' . $docente->id,
            'telefono'         => 'nullable|string|max:20',
            'activo'           => 'boolean',
        ]);

        $data['activo'] = $request->has('activo');
        $docente->update($data);

        return redirect()->route('docentes.show', $docente)
            ->with('success', 'Docente actualizado correctamente.');
    }

    public function destroy(Docente $docente)
    {
        $docente->delete();
        return redirect()->route('docentes.index')
            ->with('success', 'Docente eliminado.');
    }

    // ── Horarios ──────────────────────────────────────
    public function storeHorario(Request $request, Docente $docente)
    {
        $data = $request->validate([
            'dia'                  => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado',
            'hora_entrada'         => 'required|date_format:H:i',
            'hora_salida'          => 'required|date_format:H:i|after:hora_entrada',
            'tiene_bloque_muerto'  => 'boolean',
            'bloque_muerto_inicio' => 'nullable|date_format:H:i',
            'bloque_muerto_fin'    => 'nullable|date_format:H:i|after:bloque_muerto_inicio',
            'comida_inicio'        => 'nullable|date_format:H:i',
            'comida_fin'           => 'nullable|date_format:H:i|after:comida_inicio',
        ]);

        $data['tiene_bloque_muerto'] = $request->has('tiene_bloque_muerto');
        $data['docente_id'] = $docente->id;

        // Solo PTC puede tener hora de comida
        if ($docente->tipo !== 'ptc') {
            $data['comida_inicio'] = null;
            $data['comida_fin']    = null;
        }

        HorarioDocente::create($data);

        return redirect()->route('docentes.show', $docente)
            ->with('success', 'Horario agregado correctamente.');
    }

    public function destroyHorario(Docente $docente, HorarioDocente $horario)
    {
        $horario->delete();
        return redirect()->route('docentes.show', $docente)
            ->with('success', 'Horario eliminado.');
    }

    // Limpiar horarios de UN docente
public function limpiarHorarios(Docente $docente)
{
    $docente->horarios()->delete();

    return redirect()->route('docentes.show', $docente)
        ->with('success', "Horarios de {$docente->nombre_completo} eliminados. Ya puedes importar el nuevo PDF.");
}

// Limpiar horarios de TODOS los docentes (nuevo semestre)
public function nuevoSemestre()
{
    HorarioDocente::truncate();

    return redirect()->route('docentes.index')
        ->with('success', 'Horarios de todos los docentes eliminados. Listo para el nuevo semestre.');
}
}