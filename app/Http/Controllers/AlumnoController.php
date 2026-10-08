<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\DocumentoTitulacion;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function index()
    {
        $alumnos = Alumno::with('documentos')
            ->orderBy('apellido_paterno')
            ->get();

        return view('alumnos.index', compact('alumnos'));
    }

    public function create()
    {
        return view('alumnos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'matricula'        => 'required|string|max:20|unique:alumnos,matricula',
            'modalidad'        => 'required|in:tesis,residencia_profesional,proyecto_de_investigacion,memoria_de_experiencia,titulacion_integral',
            'estado'           => 'required|in:en_proceso,documentacion,asignado,titulado',
        ]);

        $alumno = Alumno::create($data);

        // Crear checklist de documentos vacío automáticamente
        DocumentoTitulacion::create(['alumno_id' => $alumno->id]);

        return redirect()->route('alumnos.show', $alumno)
            ->with('success', 'Alumno registrado correctamente.');
    }

    public function show(Alumno $alumno)
    {
        $alumno->load('documentos', 'examenActual.sinodales.docente');
        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno)
    {
        return view('alumnos.edit', compact('alumno'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'matricula'        => 'required|string|max:20|unique:alumnos,matricula,' . $alumno->id,
            'modalidad'        => 'required|in:tesis,residencia_profesional,proyecto_de_investigacion,memoria_de_experiencia,titulacion_integral',
            'estado'           => 'required|in:en_proceso,documentacion,asignado,titulado',
        ]);

        $alumno->update($data);

        return redirect()->route('alumnos.show', $alumno)
            ->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();
        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno eliminado.');
    }

    // Actualizar checklist de documentos
public function updateDocumentos(Request $request, Alumno $alumno)
{
    $docs = $alumno->documentos ?? DocumentoTitulacion::create(['alumno_id' => $alumno->id]);

    $campos = ['anexo_31','anexo_32','anexo_33','hoja_asignacion','autorizacion_asesor','anexo_plagio'];

    foreach ($campos as $campo) {
        $entregado  = $request->has($campo);
        $docs->$campo = $entregado;
        $fechaCampo = $campo . '_fecha';

        if ($entregado && !$docs->$fechaCampo) {
            $docs->$fechaCampo = now()->toDateString();
        } elseif (!$entregado) {
            $docs->$fechaCampo = null;
        }
    }

    $docs->notas = $request->input('notas');
    $docs->save();

    // Actualizar estado automáticamente
    if ($docs->esta_completo) {
        $alumno->update(['estado' => 'asignado']);
    } elseif ($docs->total_entregados > 0) {
        $alumno->update(['estado' => 'documentacion']);
    }

    return redirect()->route('alumnos.show', $alumno)
        ->with('success', 'Documentos actualizados correctamente.');
}

// Método para titular manualmente
public function titular(Request $request, Alumno $alumno)
{
    $request->validate([
        'resultado'     => 'required|in:aprobado,no_aprobado,aplazado',
        'periodo'       => 'nullable|string|max:50',
        'observaciones' => 'nullable|string',
    ]);

    // Actualizar estado del alumno
    $alumno->update(['estado' => 'titulado']);

    // Obtener examen y sinodales
    $examen = $alumno->examenActual;

    // Crear snapshot en historial
    \App\Models\Titulacion::updateOrCreate(
        ['alumno_id' => $alumno->id],
        [
            'examen_id'      => $examen?->id,
            'nombre_completo'=> $alumno->nombre_completo,
            'matricula'      => $alumno->matricula,
            'carrera'        => $alumno->carrera,
            'modalidad'      => $alumno->modalidad,
            'fecha_examen'   => $examen?->fecha,
            'hora_inicio'    => $examen?->hora_inicio,
            'hora_fin'       => $examen?->hora_fin,
            'salon'          => $examen?->salon,
            'folio_oficio'   => $examen?->folio_oficio,
            'fecha_oficio'   => $examen?->fecha_oficio,
            'presidente'     => $examen?->sinodales->where('rol','presidente')->first()?->docente->nombre_completo,
            'secretario'     => $examen?->sinodales->where('rol','secretario')->first()?->docente->nombre_completo,
            'vocal'          => $examen?->sinodales->where('rol','vocal')->first()?->docente->nombre_completo,
            'resultado'      => $request->resultado,
            'observaciones'  => $request->observaciones,
            'periodo'        => $request->periodo,
            'fecha_registro' => now()->toDateString(),
        ]
    );

    return redirect()->route('historial.index')
        ->with('success', "¡{$alumno->nombre_completo} registrado como titulado correctamente!");
}
}