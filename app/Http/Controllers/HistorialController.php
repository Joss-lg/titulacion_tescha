<?php

namespace App\Http\Controllers;

use App\Models\Titulacion;
use App\Models\Alumno;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $query = Titulacion::with('alumno')
            ->orderBy('fecha_examen', 'desc');

        // Filtro por búsqueda
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->buscar . '%')
                  ->orWhere('matricula', 'like', '%' . $request->buscar . '%');
            });
        }

        // Filtro por periodo
        if ($request->filled('periodo')) {
            $query->where('periodo', $request->periodo);
        }

        // Filtro por modalidad
        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }

        // Filtro por resultado
        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        }

        $titulaciones = $query->get();

        // Estadísticas
        $stats = [
            'total'       => Titulacion::count(),
            'aprobados'   => Titulacion::where('resultado', 'aprobado')->count(),
            'no_aprobados'=> Titulacion::where('resultado', 'no_aprobado')->count(),
            'aplazados'   => Titulacion::where('resultado', 'aplazado')->count(),
        ];

        // Periodos únicos para el filtro
        $periodos = Titulacion::whereNotNull('periodo')
            ->distinct()
            ->pluck('periodo');

        return view('historial.index', compact('titulaciones', 'stats', 'periodos'));
    }
}