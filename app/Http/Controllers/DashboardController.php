<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Titulacion;
use App\Models\Sinodal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Métricas principales ──────────────────────────────
        $totalAlumnos     = Alumno::count();
        $totalDocentes    = Docente::where('activo', true)->count();
        $totalExamenes    = Examen::where('estado', 'programado')->count();
        $totalTitulados   = Titulacion::where('resultado', 'aprobado')->count();

        // ── Alumnos por estado ────────────────────────────────
        $alumnosPorEstado = Alumno::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // ── Próximos exámenes (los 5 más cercanos) ────────────
        $proximosExamenes = Examen::with('alumno', 'sinodales')
            ->where('estado', 'programado')
            ->where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->limit(5)
            ->get();

        // ── Exámenes sin sinodales completos ──────────────────
        $sinSinodales = Examen::with('alumno')
            ->where('estado', 'programado')
            ->withCount('sinodales')
            ->having('sinodales_count', '<', 3)
            ->orderBy('fecha')
            ->limit(5)
            ->get();

        // ── Docentes sin horarios (necesitan PDF) ─────────────
        $docentesSinHorarios = Docente::where('activo', true)
            ->doesntHave('horarios')
            ->count();

        // ── Actividad reciente ────────────────────────────────
        $actividadReciente = collect()
            ->merge(
                Alumno::orderBy('created_at', 'desc')->limit(3)->get()
                    ->map(fn($a) => [
                        'tipo'  => 'alumno',
                        'texto' => "Nuevo alumno: {$a->nombre_completo}",
                        'fecha' => $a->created_at,
                        'color' => 'blue',
                        'icon'  => 'fa-user-graduate',
                    ])
            )
            ->merge(
                Titulacion::orderBy('created_at', 'desc')->limit(3)->get()
                    ->map(fn($t) => [
                        'tipo'  => 'titulacion',
                        'texto' => "Titulado: {$t->nombre_completo}",
                        'fecha' => $t->created_at,
                        'color' => 'emerald',
                        'icon'  => 'fa-graduation-cap',
                    ])
            )
            ->merge(
                Examen::with('alumno')->orderBy('created_at', 'desc')->limit(3)->get()
                    ->map(fn($e) => [
                        'tipo'  => 'examen',
                        'texto' => "Examen programado: {$e->alumno->nombre_completo}",
                        'fecha' => $e->created_at,
                        'color' => 'purple',
                        'icon'  => 'fa-calendar-check',
                    ])
            )
            ->sortByDesc('fecha')
            ->take(6)
            ->values();

        return view('dashboard', compact(
            'totalAlumnos',
            'totalDocentes',
            'totalExamenes',
            'totalTitulados',
            'alumnosPorEstado',
            'proximosExamenes',
            'sinSinodales',
            'docentesSinHorarios',
            'actividadReciente'
        ));
    }
}