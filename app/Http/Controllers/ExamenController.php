<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Sinodal;
use App\Models\Titulacion;
use App\Services\DisponibilidadDocentesService;
use App\Services\OficioSinodalService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExamenController extends Controller
{
    public function __construct(private DisponibilidadDocentesService $disponibilidad) {}

    public function index()
    {
        $examenes = Examen::with('alumnos', 'sinodales.docente')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio')
            ->get();

        return view('examenes.index', compact('examenes'));
    }

    public function create()
    {
        $alumnos = Alumno::with('presidente')->whereDoesntHave('examenes', function ($q) {
            $q->where('estado', 'programado');
        })->orderBy('apellido_paterno')->get();

        $bloques = Examen::bloquesHorario();

        return view('examenes.create', compact('alumnos', 'bloques'));
    }

    public function store(Request $request)
    {
        // El examen puede ser individual o en equipo; se ignoran los lugares vacíos del formulario
        $request->merge([
            'alumnos' => array_values(array_filter((array) $request->input('alumnos', []))),
        ]);

        $data = $request->validate([
            'alumnos' => 'required|array|min:1|max:3',
            'alumnos.*' => [
                'distinct',
                'exists:alumnos,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    $tieneProgramado = Alumno::whereKey($value)
                        ->whereHas('examenes', fn ($q) => $q->where('estado', 'programado'))
                        ->exists();

                    if ($tieneProgramado) {
                        $fail('Uno de los alumnos ya tiene un examen programado.');
                    }
                },
            ],
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => ['required', Rule::in(array_keys(Examen::bloquesHorario()))],
            'salon' => 'nullable|string|max:50',
        ], [
            'alumnos.required' => 'Selecciona al menos un alumno.',
            'alumnos.max' => 'Un examen admite máximo 3 alumnos.',
            'alumnos.*.distinct' => 'No repitas alumnos en el equipo.',
        ]);

        // Hora fin siempre = hora inicio + 1 hora, sin importar el tamaño del equipo
        $data['hora_fin'] = Carbon::createFromFormat('H:i', $data['hora_inicio'])
            ->addHour()
            ->format('H:i');

        // El presidente viene definido desde el registro del alumno; en equipo debe ser el mismo
        $alumnos = Alumno::with('presidente')->whereKey($data['alumnos'])->get();

        if ($sinPresidente = $alumnos->firstWhere('presidente_id', null)) {
            return back()->withInput()->withErrors([
                'alumnos' => "{$sinPresidente->nombre_completo} no tiene presidente asignado. Edita al alumno para definirlo.",
            ]);
        }

        if ($alumnos->pluck('presidente_id')->unique()->count() > 1) {
            return back()->withInput()->withErrors([
                'alumnos' => 'Los integrantes del equipo tienen presidentes distintos. Deben compartir el mismo presidente.',
            ]);
        }

        $presidente = $alumnos->first()->presidente;
        $candidatos = $this->disponibilidad->candidatos($data['fecha'], $data['hora_inicio'], $data['hora_fin']);
        $noDisponible = collect($candidatos['no_disponibles'])->firstWhere('docente.id', $presidente->id);

        if ($noDisponible || ! $presidente->activo) {
            return back()->withInput()->withErrors([
                'hora_inicio' => "El presidente {$presidente->nombre_completo} no está disponible en ese horario: "
                    .($noDisponible['razon'] ?? 'está inactivo.'),
            ]);
        }

        $examen = DB::transaction(function () use ($data, $presidente) {
            $examen = Examen::create(collect($data)->except('alumnos')->all());
            $examen->alumnos()->attach($data['alumnos']);
            $examen->sinodales()->create(['docente_id' => $presidente->id, 'rol' => 'presidente']);
            Alumno::whereKey($data['alumnos'])->update(['estado' => 'asignado']);

            return $examen;
        });

        return redirect()->route('examenes.sinodales', $examen)
            ->with('success', 'Examen programado con su presidente. El sistema sorteó secretario y vocal; revísalos y guarda.');
    }

    public function show(Examen $examen)
    {
        $examen->load('alumnos', 'sinodales.docente');

        return view('examenes.show', compact('examen'));
    }

    public function edit(Examen $examen)
    {
        $alumnos = Alumno::orderBy('apellido_paterno')->get();
        $bloques = $this->bloquesParaEditar($examen);

        return view('examenes.edit', compact('examen', 'alumnos', 'bloques'));
    }

    public function update(Request $request, Examen $examen)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => ['required', Rule::in(array_keys($this->bloquesParaEditar($examen)))],
            'salon' => 'nullable|string|max:50',
            'estado' => 'required|in:programado,realizado,cancelado',
        ]);

        // El examen siempre dura una hora
        $data['hora_fin'] = Carbon::createFromFormat('H:i', $data['hora_inicio'])->addHour()->format('H:i');

        $examen->update($data);

        return redirect()->route('examenes.show', $examen->id)
            ->with('success', 'Examen actualizado correctamente.');
    }

    public function destroy(Examen $examen)
    {
        // El historial de titulación conserva la referencia al examen (llave foránea sin cascada)
        if (Titulacion::where('examen_id', $examen->id)->exists()) {
            return back()->with('error', 'No se puede eliminar este examen porque ya tiene un resultado registrado en el historial.');
        }

        Alumno::whereKey($examen->alumnos()->pluck('alumnos.id'))->update(['estado' => 'documentacion']);
        $examen->delete();

        return redirect()->route('examenes.index')
            ->with('success', 'Examen eliminado.');
    }

    // ── Asignación de Sinodales ────────────────────────
    public function sinodales(Request $request, Examen $examen)
    {
        $examen->load('alumnos', 'sinodales.docente');

        $candidatos = $this->disponibilidad->candidatosParaExamen($examen);
        $presidenteId = $this->presidenteDelExamen($examen);

        // Se sortea al entrar sin jurado completo o al pedir "Volver a sortear"
        $asignados = $examen->sinodales->pluck('docente_id', 'rol');
        $sugeridos = [
            'presidente' => $presidenteId ?? $asignados['presidente'] ?? null,
            'secretario' => $asignados['secretario'] ?? null,
            'vocal' => $asignados['vocal'] ?? null,
        ];

        if ($request->boolean('sortear') || ! $sugeridos['secretario'] || ! $sugeridos['vocal']) {
            $sorteados = $this->disponibilidad->sortear($candidatos['disponibles'], array_filter([$sugeridos['presidente']]));
            $sugeridos['secretario'] = $sorteados->get(0)?->id;
            $sugeridos['vocal'] = $sorteados->get(1)?->id;
        }

        $presidenteFijo = $presidenteId ? Docente::find($presidenteId) : null;

        return view('examenes.sinodales', compact('examen', 'candidatos', 'sugeridos', 'presidenteFijo'));
    }

    public function storeSinodales(Request $request, Examen $examen)
    {
        // El presidente definido en el alumno no se puede cambiar desde aquí
        if ($presidenteId = $this->presidenteDelExamen($examen)) {
            $request->merge(['presidente' => $presidenteId]);
        }

        $request->validate([
            'presidente' => 'required|exists:docentes,id',
            'secretario' => 'required|exists:docentes,id|different:presidente',
            'vocal' => 'required|exists:docentes,id|different:presidente|different:secretario',
            'folio_oficio' => 'nullable|string|max:30',
            'fecha_oficio' => 'nullable|date',
        ]);

        // Revalidar en servidor que cada docente siga disponible (horario y empalmes)
        $candidatos = $this->disponibilidad->candidatosParaExamen($examen);
        $idsDisponibles = collect($candidatos['disponibles'])->pluck('docente.id')->all();
        $razones = collect($candidatos['no_disponibles'])->mapWithKeys(
            fn ($c) => [$c['docente']->id => $c['razon']]
        );

        $errores = [];
        foreach (['presidente', 'secretario', 'vocal'] as $rol) {
            $docenteId = (int) $request->$rol;
            if (! in_array($docenteId, $idsDisponibles, true)) {
                $errores[$rol] = 'El docente no está disponible: '.($razones[$docenteId] ?? 'docente inactivo.');
            }
        }

        if ($errores) {
            return back()->withErrors($errores)->withInput();
        }

        DB::transaction(function () use ($request, $examen) {
            // Guardar folio y fecha del oficio en el examen
            $examen->update([
                'folio_oficio' => $request->folio_oficio,
                'fecha_oficio' => $request->fecha_oficio,
            ]);

            // Eliminar sinodales anteriores si los hay
            $examen->sinodales()->delete();

            // Crear los tres sinodales
            foreach (['presidente', 'secretario', 'vocal'] as $rol) {
                Sinodal::create([
                    'examen_id' => $examen->id,
                    'docente_id' => $request->$rol,
                    'rol' => $rol,
                ]);
            }

            // Actualizar estado del alumno
            Alumno::whereKey($examen->alumnos()->pluck('alumnos.id'))->update(['estado' => 'asignado']);
        });

        // Recargar desde BD para asegurar folio/fecha frescos
        $examen->refresh();

        // Generar y descargar oficio automáticamente
        try {
            $ruta = app(OficioSinodalService::class)->generar($examen);

            return response()->download($ruta)->deleteFileAfterSend(false);
        } catch (\Throwable $e) {
            return redirect()->route('examenes.show', $examen)
                ->with('success', 'Sinodales asignados correctamente.')
                ->with('warning', 'No se pudo generar el oficio automáticamente: '.$e->getMessage());
        }
    }

    // ── Descargar oficio de sinodales ─────────────────
    public function descargarOficio(Examen $examen)
    {
        $examen->load('alumnos', 'sinodales.docente');

        if ($examen->sinodales->count() < 3) {
            return back()->with('error', 'Este examen aún no tiene los 3 sinodales asignados.');
        }

        $ruta = app(OficioSinodalService::class)->generar($examen);
        $nombre = 'Oficio_Sinodales_'.$examen->alumnos->pluck('matricula')->implode('_').'.docx';

        return response()->download($ruta, $nombre)->deleteFileAfterSend(false);
    }

    /**
     * Bloques de una hora; incluye el horario actual si el examen se programó fuera de ellos.
     *
     * @return array<string, string>
     */
    private function bloquesParaEditar(Examen $examen): array
    {
        $bloques = Examen::bloquesHorario();
        $actual = substr($examen->hora_inicio, 0, 5);
        $bloques[$actual] ??= $actual.' a '.substr($examen->hora_fin, 0, 5);
        ksort($bloques);

        return $bloques;
    }

    /**
     * Presidente definido en los alumnos del examen (null en exámenes anteriores al cambio).
     */
    private function presidenteDelExamen(Examen $examen): ?int
    {
        return $examen->loadMissing('alumnos')->presidenteDelEquipoId();
    }
}
