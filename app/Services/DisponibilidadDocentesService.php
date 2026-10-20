<?php

namespace App\Services;

use App\Models\Docente;
use App\Models\Examen;
use App\Models\Sinodal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Determina qué docentes pueden ser sinodales en una fecha y horario,
 * y sortea secretario y vocal repartiendo la carga entre los disponibles.
 */
class DisponibilidadDocentesService
{
    private const DIAS = [
        0 => 'domingo',
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
    ];

    /**
     * @return array{disponibles: list<array{docente: Docente, horario: mixed}>, no_disponibles: list<array{docente: Docente, razon: string}>}
     */
    public function candidatosParaExamen(Examen $examen): array
    {
        return $this->candidatos($examen->fecha, $examen->hora_inicio, $examen->hora_fin, $examen->id);
    }

    /**
     * @return array{disponibles: list<array{docente: Docente, horario: mixed}>, no_disponibles: list<array{docente: Docente, razon: string}>}
     */
    public function candidatos(mixed $fecha, string $horaInicio, string $horaFin, ?int $excluirExamenId = null): array
    {
        $fecha = Carbon::parse($fecha);
        $dia = self::DIAS[$fecha->dayOfWeek];
        $horaInicio = $this->hora($horaInicio);
        $horaFin = $this->hora($horaFin);

        $docentes = Docente::where('activo', true)
            ->with(['horarios' => fn ($q) => $q->where('dia', $dia)])
            ->get();

        // Docentes que ya son sinodales de otro examen programado que se empalma
        $ocupados = Sinodal::with('examen.alumnos')
            ->whereHas('examen', function ($q) use ($excluirExamenId, $fecha, $horaInicio, $horaFin) {
                $q->when($excluirExamenId, fn ($q) => $q->where('id', '!=', $excluirExamenId))
                    ->where('estado', 'programado')
                    ->whereDate('fecha', $fecha->toDateString())
                    ->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->get()
            ->keyBy('docente_id');

        $disponibles = [];
        $noDisponibles = [];

        foreach ($docentes as $docente) {
            $razon = $this->razonNoDisponible($docente, $ocupados->get($docente->id), $horaInicio, $horaFin);

            if ($razon) {
                $noDisponibles[] = ['docente' => $docente, 'razon' => $razon];
            } else {
                $disponibles[] = ['docente' => $docente, 'horario' => $docente->horarios->first()];
            }
        }

        return [
            'disponibles' => $disponibles,
            'no_disponibles' => $noDisponibles,
        ];
    }

    /**
     * Sortea docentes entre los disponibles, prefiriendo a quienes tienen menos
     * asignaciones como sinodal; entre empatados el orden es aleatorio.
     *
     * @param  list<array{docente: Docente}>  $disponibles
     * @param  list<int>  $excluirIds
     * @return Collection<int, Docente>
     */
    public function sortear(array $disponibles, array $excluirIds = [], int $cantidad = 2): Collection
    {
        $docentes = collect($disponibles)
            ->pluck('docente')
            ->reject(fn (Docente $docente) => in_array($docente->id, $excluirIds, true));

        $asignaciones = Sinodal::whereIn('docente_id', $docentes->pluck('id'))
            ->selectRaw('docente_id, count(*) as total')
            ->groupBy('docente_id')
            ->pluck('total', 'docente_id');

        return $docentes
            ->shuffle()
            ->sortBy(fn (Docente $docente) => (int) ($asignaciones[$docente->id] ?? 0))
            ->take($cantidad)
            ->values();
    }

    private function razonNoDisponible(Docente $docente, ?Sinodal $ocupado, string $horaInicio, string $horaFin): ?string
    {
        if ($ocupado) {
            $otro = $ocupado->examen;

            return 'Ya es sinodal de otro examen de '.substr($otro->hora_inicio, 0, 5)
                .' a '.substr($otro->hora_fin, 0, 5)." ({$otro->nombres_alumnos})";
        }

        $horario = $docente->horarios->first();

        if (! $horario) {
            return 'No trabaja ese día';
        }

        if ($horaInicio < $this->hora($horario->hora_entrada) || $horaFin > $this->hora($horario->hora_salida)) {
            return "Su horario es {$horario->hora_entrada} - {$horario->hora_salida}";
        }

        if ($horario->tiene_bloque_muerto && $horario->bloque_muerto_inicio
            && $this->seEmpalma($horaInicio, $horaFin, $horario->bloque_muerto_inicio, $horario->bloque_muerto_fin)) {
            return "Tiene bloque muerto de {$horario->bloque_muerto_inicio} a {$horario->bloque_muerto_fin}";
        }

        if ($docente->esPtc() && $horario->comida_inicio
            && $this->seEmpalma($horaInicio, $horaFin, $horario->comida_inicio, $horario->comida_fin)) {
            return "Es su hora de comida ({$horario->comida_inicio} - {$horario->comida_fin})";
        }

        return null;
    }

    private function seEmpalma(string $inicio, string $fin, string $bloqueInicio, string $bloqueFin): bool
    {
        return $inicio < $this->hora($bloqueFin) && $fin > $this->hora($bloqueInicio);
    }

    /**
     * Normaliza "10:00" y "10:00:00" a "10:00:00" para compararlas como texto.
     */
    private function hora(string $hora): string
    {
        return Carbon::parse($hora)->format('H:i:s');
    }
}
