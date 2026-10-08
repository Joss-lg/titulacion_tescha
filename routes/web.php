<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\ImportadorDocenteController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\SinodalController;
use App\Http\Controllers\HistorialController;
use App\Models\Examen;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Docentes ──────────────────────────────────────────────────
    // Nuevo semestre ANTES del resource para evitar conflicto con {docente}
    Route::delete('docentes/nuevo-semestre', [DocenteController::class, 'nuevoSemestre'])
        ->name('docentes.nuevo-semestre');

    Route::resource('docentes', DocenteController::class);

    Route::put('docentes/{docente}/horarios/{horario}', [DocenteController::class, 'updateHorario'])
        ->name('docentes.horarios.update');
    Route::post('docentes/{docente}/horarios', [DocenteController::class, 'storeHorario'])
        ->name('docentes.horarios.store');
    Route::delete('docentes/{docente}/horarios/{horario}', [DocenteController::class, 'destroyHorario'])
        ->name('docentes.horarios.destroy');
    Route::delete('docentes/{docente}/horarios', [DocenteController::class, 'limpiarHorarios'])
        ->name('docentes.horarios.limpiar');

    Route::get('docentes/{docente}/importar-horarios',  [ImportadorDocenteController::class, 'form'])
        ->name('docentes.importar.form');
    Route::post('docentes/{docente}/importar-horarios', [ImportadorDocenteController::class, 'procesar'])
        ->name('docentes.importar.procesar');
    Route::post('docentes/{docente}/importar-horarios/guardar', [ImportadorDocenteController::class, 'guardar'])
        ->name('docentes.importar.guardar');

    // ── Alumnos ───────────────────────────────────────────────────
    Route::resource('alumnos', AlumnoController::class);
    Route::post('alumnos/{alumno}/documentos', [AlumnoController::class, 'updateDocumentos'])
        ->name('alumnos.documentos.update');

    // ── Exámenes (binding manual por pluralización) ───────────────
    Route::get('examenes', [ExamenController::class, 'index'])->name('examenes.index');
    Route::get('examenes/create', [ExamenController::class, 'create'])->name('examenes.create');
    Route::post('examenes', [ExamenController::class, 'store'])->name('examenes.store');

    Route::get('examenes/{id}', function ($id) {
        $examen = Examen::findOrFail($id);
        return app(ExamenController::class)->show($examen);
    })->name('examenes.show');

    Route::get('examenes/{id}/edit', function ($id) {
        $examen  = Examen::findOrFail($id);
        $alumnos = \App\Models\Alumno::orderBy('apellido_paterno')->get();
        return view('examenes.edit', compact('examen', 'alumnos'));
    })->name('examenes.edit');

    Route::put('examenes/{id}', function ($id, \Illuminate\Http\Request $request) {
        $examen = Examen::findOrFail($id);
        return app(ExamenController::class)->update($request, $examen);
    })->name('examenes.update');

    Route::delete('examenes/{id}', function ($id) {
        $examen = Examen::findOrFail($id);
        return app(ExamenController::class)->destroy($examen);
    })->name('examenes.destroy');

    Route::get('examenes/{id}/sinodales', function ($id) {
        $examen = Examen::findOrFail($id);
        return app(ExamenController::class)->sinodales($examen);
    })->name('examenes.sinodales');

    Route::post('examenes/{id}/sinodales', function ($id, \Illuminate\Http\Request $request) {
        $examen = Examen::findOrFail($id);
        return app(ExamenController::class)->storeSinodales($request, $examen);
    })->name('examenes.sinodales.store');

    Route::get('examenes/{id}/hoja', function ($id) {
        $examen = Examen::with('alumno', 'sinodales.docente')->findOrFail($id);
        return view('examenes.hoja', compact('examen'));
    })->name('examenes.hoja');

    Route::get('historial', [HistorialController::class, 'index'])->name('historial.index');
    Route::post('alumnos/{alumno}/titular', [AlumnoController::class, 'titular'])->name('alumnos.titular');

    Route::get('documentos', [DocumentoController::class, 'index'])->name('documentos.index');


    Route::get('sinodales', [SinodalController::class, 'index'])->name('sinodales.index');

    // ── Profile ───────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';