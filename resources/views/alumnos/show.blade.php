@extends('layouts.app')
@section('title', $alumno->nombre_completo)
@section('subtitle', 'Matrícula: ' . $alumno->matricula . ' · ISC')

@section('content')

@php
$estadoConfig = [
    'en_proceso'    => ['label' => 'En proceso',    'class' => 'text-blue-700 bg-blue-50'],
    'documentacion' => ['label' => 'Documentación', 'class' => 'text-amber-700 bg-amber-50'],
    'asignado'      => ['label' => 'Asignado',      'class' => 'text-purple-700 bg-purple-50'],
    'titulado'      => ['label' => 'Titulado',      'class' => 'text-emerald-700 bg-emerald-50'],
];
$est  = $estadoConfig[$alumno->estado] ?? ['label' => $alumno->estado, 'class' => 'text-gray-600 bg-gray-100'];
$docs = $alumno->documentos;
$pct  = $docs ? round(($docs->total_entregados / 6) * 100) : 0;

$documentosList = [
    'anexo_31'            => 'Anexo 31',
    'anexo_32'            => 'Anexo 32',
    'anexo_33'            => 'Anexo 33',
    'hoja_asignacion'     => 'Hoja de asignación',
    'autorizacion_asesor' => 'Autorización del asesor',
    'anexo_plagio'        => 'Anexo de plagio',
];
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('alumnos.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Todos los alumnos
    </a>
    <a href="{{ route('alumnos.edit', $alumno) }}"
       class="flex items-center gap-2 text-sm font-medium text-vino-700 bg-vino-50 hover:bg-vino-100 px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Editar
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Columna izquierda --}}
    <div class="space-y-4">

        {{-- Info del alumno --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col items-center text-center mb-5">
                <div class="w-16 h-16 rounded-2xl {{ $alumno->estado === 'titulado' ? 'bg-emerald-100 text-emerald-700' : 'bg-vino-100 text-vino-800' }}
                            flex items-center justify-center text-xl font-bold mb-3">
                    {{ strtoupper(substr($alumno->nombre, 0, 1) . substr($alumno->apellido_paterno, 0, 1)) }}
                </div>
                <h2 class="font-bold text-gray-800 text-sm leading-tight">{{ $alumno->nombre_completo }}</h2>
                <p class="text-xs text-gray-400 font-mono mt-1">{{ $alumno->matricula }}</p>
                <span class="mt-2 text-xs font-semibold px-3 py-1 rounded-full {{ $est['class'] }}">
                    {{ $est['label'] }}
                </span>
            </div>

            <div class="space-y-2.5 border-t border-gray-100 pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-400">Carrera</span>
                    <span class="text-xs font-medium text-gray-700">ISC</span>
                </div>
                <div class="flex justify-between items-start gap-2">
                    <span class="text-xs text-gray-400 flex-shrink-0">Modalidad</span>
                    <span class="text-xs font-medium text-gray-700 text-right">
                        {{ str_replace('_', ' ', ucfirst($alumno->modalidad)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Progreso documentos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-600">Progreso documentación</p>
                <span class="text-xs font-bold {{ $pct === 100 ? 'text-emerald-600' : 'text-vino-700' }}">
                    {{ $pct }}%
                </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                <div class="h-full rounded-full transition-all duration-700
                    {{ $pct === 100 ? 'bg-emerald-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-vino-500') }}"
                    style="width: {{ $pct }}%">
                </div>
            </div>
            <p class="text-xs text-gray-400">
                {{ $docs ? $docs->total_entregados : 0 }} de 6 documentos entregados
            </p>
        </div>

        {{-- Sinodales asignados --}}
        @if($alumno->examenActual && $alumno->examenActual->sinodales->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-semibold text-gray-600 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                    Jurado sinodal
                </h3>
                <a href="{{ route('examenes.show', $alumno->examenActual->id) }}"
                   class="text-xs text-vino-600 hover:text-vino-800 font-medium transition-colors">
                    Ver examen →
                </a>
            </div>

            <div class="space-y-2">
                @foreach([
                    'presidente' => ['label' => 'Presidente', 'bg' => 'bg-vino-100',   'text' => 'text-vino-800'],
                    'secretario' => ['label' => 'Secretario', 'bg' => 'bg-blue-100',   'text' => 'text-blue-800'],
                    'vocal'      => ['label' => 'Vocal',      'bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                ] as $rol => $config)
                @php $sinodal = $alumno->examenActual->sinodales->where('rol', $rol)->first(); @endphp
                @if($sinodal)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-8 h-8 rounded-lg {{ $config['bg'] }} {{ $config['text'] }}
                                flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($sinodal->docente->nombre, 0, 1) . substr($sinodal->docente->apellido_paterno, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $config['label'] }}</p>
                        <p class="text-xs font-semibold text-gray-700 truncate">
                            {{ $sinodal->docente->nombre_completo }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $sinodal->docente->tipo === 'ptc' ? 'PTC' : 'Asignatura' }}
                        </p>
                    </div>
                </div>
                @endif
                @endforeach
            </div>

            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-400">
                    <span class="font-medium text-gray-600">
                        {{ \Carbon\Carbon::parse($alumno->examenActual->fecha)->isoFormat('D [de] MMMM, YYYY') }}
                    </span>
                    ·
                    {{ \Carbon\Carbon::parse($alumno->examenActual->hora_inicio)->format('H:i') }} —
                    {{ \Carbon\Carbon::parse($alumno->examenActual->hora_fin)->format('H:i') }} hrs
                    @if($alumno->examenActual->salon)
                        · Salón {{ $alumno->examenActual->salon }}
                    @endif
                </p>
            </div>
        </div>
        @endif

    </div>

    {{-- Columna derecha --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Checklist de documentos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-5 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                Checklist de documentos
            </h3>

            <form method="POST" action="{{ route('alumnos.documentos.update', $alumno) }}">
                @csrf

                <div class="space-y-3 mb-5">
                    @foreach($documentosList as $campo => $etiqueta)
                    @php
                        $entregado  = $docs && $docs->$campo;
                        $fechaCampo = $campo . '_fecha';
                        $fecha      = $docs && $docs->$fechaCampo ? $docs->$fechaCampo->format('d/m/Y') : null;
                    @endphp
                    <label class="flex items-center gap-4 p-3.5 rounded-xl border cursor-pointer transition-all duration-150
                                  {{ $entregado
                                      ? 'bg-emerald-50 border-emerald-200'
                                      : 'bg-gray-50 border-gray-100 hover:border-gray-200' }}">
                        <input type="checkbox" name="{{ $campo }}" value="1"
                               {{ $entregado ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-500 rounded border-gray-300 focus:ring-emerald-400"/>
                        <div class="flex-1">
                            <p class="text-sm font-medium {{ $entregado ? 'text-emerald-700' : 'text-gray-600' }}">
                                {{ $etiqueta }}
                            </p>
                            @if($fecha)
                                <p class="text-xs text-emerald-500 mt-0.5">Entregado el {{ $fecha }}</p>
                            @endif
                        </div>
                        @if($entregado)
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </label>
                    @endforeach
                </div>

                {{-- Notas --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Notas</label>
                    <textarea name="notas" rows="2"
                              placeholder="Observaciones del proceso..."
                              class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl
                                     focus:outline-none focus:ring-2 focus:ring-vino-400 resize-none">{{ $docs ? $docs->notas : '' }}</textarea>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 bg-vino-900 hover:bg-vino-800
                           text-white font-semibold py-2.5 rounded-xl transition-all shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar cambios en documentos
                </button>
            </form>
        </div>

        {{-- Registrar titulación --}}
        @if($alumno->estado !== 'titulado')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                Registrar titulación
            </h3>

            @if($pct < 100)
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 mb-4 flex items-center gap-3">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-xs text-amber-700">
                    El expediente no está completo ({{ $docs ? $docs->total_entregados : 0 }}/6 documentos).
                    Puedes registrar la titulación de todas formas si es necesario.
                </p>
            </div>
            @endif

            <form method="POST" action="{{ route('alumnos.titular', $alumno) }}"
                  onsubmit="return confirm('¿Registrar la titulación de {{ $alumno->nombre_completo }}?\n\nEsto lo moverá al historial de titulados.')">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
                            Resultado *
                        </label>
                        <select name="resultado" required
                                class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl
                                       focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            <option value="aprobado">✅ Aprobado</option>
                            <option value="no_aprobado">❌ No aprobado</option>
                            <option value="aplazado">⏸ Aplazado</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
                            Periodo escolar
                        </label>
                        <input type="text" name="periodo"
                               placeholder="Ej. Sep 2026 - Feb 2027"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl
                                      focus:outline-none focus:ring-2 focus:ring-emerald-400"/>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
                        Observaciones
                    </label>
                    <textarea name="observaciones" rows="2"
                              placeholder="Notas adicionales del examen..."
                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl
                                     focus:outline-none focus:ring-2 focus:ring-emerald-400 resize-none"></textarea>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700
                           text-white font-semibold py-2.5 rounded-xl transition-all shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Registrar titulación
                </button>
            </form>
        </div>

        @else
        {{-- Ya está titulado --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-emerald-700">¡Alumno titulado!</p>
                <p class="text-xs text-emerald-600 mt-0.5">
                    Este alumno ya fue registrado en el historial de titulaciones.
                </p>
                <a href="{{ route('historial.index') }}"
                   class="text-xs text-emerald-600 hover:text-emerald-800 underline font-medium mt-1 inline-block">
                    Ver en historial →
                </a>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection