<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Sinodal;
use App\Models\HorarioDocente;
use App\Services\OficioSinodalService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExamenController extends Controller
{
    public function index()
    {
        $examenes = Examen::with('alumno', 'sinodales.docente')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio')
            ->get();

        return view('examenes.index', compact('examenes'));
    }

    public function create()
    {
        $alumnos = Alumno::whereDoesntHave('examenes', function($q) {
            $q->where('estado', 'programado');
        })->orderBy('apellido_paterno')->get();

        return view('examenes.create', compact('alumnos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alumno_id'   => 'required|exists:alumnos,id',
            'fecha'       => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
            'salon'       => 'nullable|string|max:50',
        ]);

        $examen = Examen::create($data);

        // Actualizar estado del alumno
        $examen->alumno->update(['estado' => 'asignado']);

        return redirect()->route('examenes.sinodales', $examen)
            ->with('success', 'Examen programado. Ahora asigna los sinodales.');
    }

    public function show(Examen $examen)
    {
        $examen->load('alumno', 'sinodales.docente');
        return view('examenes.show', compact('examen'));
    }

    public function edit(Examen $examen)
{
    $alumnos = Alumno::orderBy('apellido_paterno')->get();
    return view('examenes.edit', compact('examen', 'alumnos'));
}

public function update(Request $request, Examen $examen)
{
    $data = $request->validate([
        'fecha'       => 'required|date',
        'hora_inicio' => 'required|date_format:H:i',
        'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
        'salon'       => 'nullable|string|max:50',
        'estado'      => 'required|in:programado,realizado,cancelado',
    ]);

    $examen->update($data);

    return redirect()->route('examenes.show', $examen->id)
        ->with('success', 'Examen actualizado correctamente.');
}

    public function destroy(Examen $examen)
    {
        $examen->alumno->update(['estado' => 'documentacion']);
        $examen->delete();
        return redirect()->route('examenes.index')
            ->with('success', 'Examen eliminado.');
    }

    // ── Asignación de Sinodales ────────────────────────
    public function sinodales(Examen $examen)
    {
        $examen->load('alumno', 'sinodales.docente');

        // Obtener candidatos disponibles
        $candidatos = $this->getCandidatos($examen);

        return view('examenes.sinodales', compact('examen', 'candidatos'));
    }

    public function storeSinodales(Request $request, Examen $examen)
    {
        $request->validate([
            'presidente'   => 'required|exists:docentes,id',
            'secretario'   => 'required|exists:docentes,id|different:presidente',
            'vocal'        => 'required|exists:docentes,id|different:presidente|different:secretario',
            'folio_oficio' => 'nullable|string|max:30',
            'fecha_oficio' => 'nullable|date',
        ]);

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
                'examen_id'  => $examen->id,
                'docente_id' => $request->$rol,
                'rol'        => $rol,
            ]);
        }

        // Actualizar estado del alumno
        $examen->alumno->update(['estado' => 'asignado']);

        // Generar y descargar oficio automáticamente
        try {
            $ruta = app(OficioSinodalService::class)->generar($examen);
            return response()->download($ruta)->deleteFileAfterSend(false);
        } catch (\Throwable $e) {
            // Si falla la generación del .docx, continuar sin bloquear
            return redirect()->route('examenes.show', $examen)
                ->with('success', 'Sinodales asignados correctamente.')
                ->with('warning', 'No se pudo generar el oficio automáticamente: ' . $e->getMessage());
        }
    }

    // ── Descargar oficio de sinodales ─────────────────
    public function descargarOficio(Examen $examen)
    {
        $examen->load('alumno', 'sinodales.docente');

        if ($examen->sinodales->count() < 3) {
            return back()->with('error', 'Este examen aún no tiene los 3 sinodales asignados.');
        }

        $ruta = app(OficioSinodalService::class)->generar($examen);
        $nombre = 'Oficio_Sinodales_' . $examen->alumno->matricula . '.docx';

        return response()->download($ruta, $nombre)->deleteFileAfterSend(false);
    }

    // ── Lógica de disponibilidad ───────────────────────
    private function getCandidatos(Examen $examen): array
    {
        $fecha      = Carbon::parse($examen->fecha);
        $dia        = $this->getDiaSemana($fecha->dayOfWeek);
        $horaInicio = $examen->hora_inicio;
        $horaFin    = $examen->hora_fin;

        $docentes = Docente::where('activo', true)
            ->with(['horarios' => function($q) use ($dia) {
                $q->where('dia', $dia);
            }])
            ->get();

        $disponibles = [];
        $noDisponibles = [];

        foreach ($docentes as $docente) {
            $horarioDia = $docente->horarios->first();

            // Si no tiene horario ese día → no disponible
            if (!$horarioDia) {
                $noDisponibles[] = [
                    'docente' => $docente,
                    'razon'   => 'No trabaja ese día',
                ];
                continue;
            }

            // Verificar que el examen cae dentro de su horario
            if ($horaInicio < $horarioDia->hora_entrada || $horaFin > $horarioDia->hora_salida) {
                $noDisponibles[] = [
                    'docente' => $docente,
                    'razon'   => "Su horario es {$horarioDia->hora_entrada} - {$horarioDia->hora_salida}",
                ];
                continue;
            }

            // Verificar bloque muerto
            if ($horarioDia->tiene_bloque_muerto) {
                $bloqueMuertoOk = $this->verificarSolapamiento(
                    $horaInicio, $horaFin,
                    $horarioDia->bloque_muerto_inicio,
                    $horarioDia->bloque_muerto_fin
                );

                if (!$bloqueMuertoOk) {
                    $noDisponibles[] = [
                        'docente' => $docente,
                        'razon'   => "Tiene bloque muerto de {$horarioDia->bloque_muerto_inicio} a {$horarioDia->bloque_muerto_fin}",
                    ];
                    continue;
                }
            }

            // Verificar hora de comida (solo PTC)
            if ($docente->esPtc() && $horarioDia->comida_inicio) {
                $comidaOk = $this->verificarSolapamiento(
                    $horaInicio, $horaFin,
                    $horarioDia->comida_inicio,
                    $horarioDia->comida_fin
                );

                if (!$comidaOk) {
                    $noDisponibles[] = [
                        'docente' => $docente,
                        'razon'   => "Es su hora de comida ({$horarioDia->comida_inicio} - {$horarioDia->comida_fin})",
                    ];
                    continue;
                }
            }

            // ✅ Docente disponible
            $disponibles[] = [
                'docente'  => $docente,
                'horario'  => $horarioDia,
            ];
        }

        return [
            'disponibles'    => $disponibles,
            'no_disponibles' => $noDisponibles,
        ];
    }

    // Retorna true si NO hay solapamiento (el examen NO cae en ese bloque)
    private function verificarSolapamiento(
        string $examenInicio, string $examenFin,
        string $bloqueInicio, string $bloqueFin
    ): bool {
        return $examenFin <= $bloqueInicio || $examenInicio >= $bloqueFin;
    }

    private function getDiaSemana(int $dayOfWeek): string
    {
        return [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        ][$dayOfWeek];
    }
}