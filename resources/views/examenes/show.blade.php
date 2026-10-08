@extends('layouts.app')
@section('title', 'Detalle del examen')
@section('subtitle', $examen->alumno->nombre_completo)

@section('content')

@php
$sinodales = $examen->sinodales;
$estadoColor = match($examen->estado) {
    'programado' => 'text-blue-700 bg-blue-50',
    'realizado'  => 'text-emerald-700 bg-emerald-50',
    'cancelado'  => 'text-red-600 bg-red-50',
    default      => 'text-gray-600 bg-gray-100',
};
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('examenes.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Todos los exámenes
    </a>

    <div class="flex items-center gap-2">
        {{-- Editar --}}
        <a href="{{ route('examenes.edit', $examen->id) }}"
           class="flex items-center gap-2 text-sm font-medium text-vino-700 bg-vino-50 hover:bg-vino-100 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>

        {{-- Sinodales --}}
        <a href="{{ route('examenes.sinodales', $examen->id) }}"
           class="flex items-center gap-2 text-sm font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ $sinodales->count() === 3 ? 'Ver/Editar sinodales' : 'Asignar sinodales' }}
        </a>

        {{-- Oficio de sinodales (solo si ya están asignados) --}}
        @if($sinodales->count() === 3)
        <a href="{{ route('examenes.oficio', $examen->id) }}"
           class="flex items-center gap-2 text-sm font-medium text-vino-700 bg-vino-50 hover:bg-vino-100 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Descargar oficio
        </a>
        @endif

        {{-- Imprimir --}}
        <a href="{{ route('examenes.hoja', $examen->id) }}"
           class="flex items-center gap-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir hoja
        </a>

        {{-- Eliminar --}}
        <form method="POST" action="{{ route('examenes.destroy', $examen->id) }}"
              onsubmit="return confirm('¿Eliminar este examen? Se eliminarán también los sinodales asignados.')">
            @csrf @method('DELETE')
            <button type="submit"
                class="flex items-center gap-2 text-sm font-medium text-red-500 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar
            </button>
        </form>
    </div>
</div>

{{-- Alerta si sinodales incompletos --}}
@if($sinodales->count() < 3)
<div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 mb-5 flex items-center gap-3">
    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <p class="text-sm text-amber-700">
        Este examen aún no tiene los 3 sinodales asignados
        <strong>({{ $sinodales->count() }}/3)</strong>.
        <a href="{{ route('examenes.sinodales', $examen->id) }}"
           class="underline font-semibold hover:text-amber-900 ml-1">
            Asignar ahora →
        </a>
    </p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Datos del examen --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
            Datos del examen
        </h3>

        <div class="space-y-4">

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-vino-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-vino-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Alumno</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $examen->alumno->nombre_completo }}</p>
                    <p class="text-xs font-mono text-gray-400">{{ $examen->alumno->matricula }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Modalidad</p>
                    <p class="text-sm text-gray-700 mt-0.5">
                        {{ str_replace('_', ' ', ucfirst($examen->alumno->modalidad)) }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Fecha</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5 capitalize">
                        {{ \Carbon\Carbon::parse($examen->fecha)->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Horario</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5">
                        {{ \Carbon\Carbon::parse($examen->hora_inicio)->format('H:i') }} —
                        {{ \Carbon\Carbon::parse($examen->hora_fin)->format('H:i') }} hrs
                    </p>
                </div>
            </div>

            @if($examen->salon)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Salón</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $examen->salon }}</p>
                </div>
            </div>
            @endif

            {{-- Datos del oficio --}}
            @if($examen->folio_oficio || $examen->fecha_oficio)
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Oficio</p>
                @if($examen->folio_oficio)
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-400">Folio</p>
                        <p class="text-sm font-mono font-semibold text-gray-700">{{ $examen->folio_oficio }}</p>
                    </div>
                </div>
                @endif
                @if($examen->fecha_oficio)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-400">Fecha del oficio</p>
                        <p class="text-sm font-semibold text-gray-700 capitalize">
                            {{ \Carbon\Carbon::parse($examen->fecha_oficio)->isoFormat('D [de] MMMM [de] YYYY') }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="pt-3 border-t border-gray-100">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full {{ $estadoColor }}">
                    {{ ucfirst($examen->estado) }}
                </span>
            </div>

        </div>
    </div>

    {{-- Sinodales --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                Jurado sinodal
            </h3>
            @if($sinodales->count() === 3)
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
                    Jurado completo
                </span>
            @endif
        </div>

        @if($sinodales->count() === 0)
            <div class="py-12 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400 font-medium">Sin sinodales asignados</p>
                <p class="text-xs text-gray-300 mt-1">Asigna el jurado para completar el examen</p>
                <a href="{{ route('examenes.sinodales', $examen->id) }}"
                   class="inline-flex items-center gap-2 mt-4 bg-vino-900 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-vino-800 transition-colors">
                    Asignar sinodales
                </a>
            </div>

        @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach([
                    'presidente' => ['label' => 'Presidente', 'bg' => 'bg-vino-100',   'text' => 'text-vino-800',   'badge' => 'text-vino-700 bg-vino-50',     'border' => 'border-vino-100'],
                    'secretario' => ['label' => 'Secretario', 'bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'badge' => 'text-blue-600 bg-blue-50',     'border' => 'border-blue-100'],
                    'vocal'      => ['label' => 'Vocal',      'bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'badge' => 'text-purple-600 bg-purple-50', 'border' => 'border-purple-100'],
                ] as $rol => $config)
                @php $sinodal = $sinodales->where('rol', $rol)->first(); @endphp

                <div class="p-5 rounded-xl border {{ $config['border'] }} bg-gray-50/30 text-center">
                    @if($sinodal)
                        <div class="w-12 h-12 rounded-xl {{ $config['bg'] }} {{ $config['text'] }}
                                    flex items-center justify-center text-sm font-bold mx-auto mb-3">
                            {{ strtoupper(substr($sinodal->docente->nombre, 0, 1) . substr($sinodal->docente->apellido_paterno, 0, 1)) }}
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider {{ $config['badge'] }} px-2.5 py-1 rounded-full">
                            {{ $config['label'] }}
                        </span>
                        <p class="text-sm font-semibold text-gray-800 mt-2.5 leading-tight">
                            {{ $sinodal->docente->nombre_completo }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $sinodal->docente->tipo === 'ptc' ? 'PTC' : 'Asignatura' }}
                        </p>
                        @if($sinodal->docente->email)
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $sinodal->docente->email }}</p>
                        @endif
                    @else
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">
                            {{ $config['label'] }}
                        </span>
                        <p class="text-xs text-gray-300 mt-3">Sin asignar</p>
                    @endif
                </div>

                @endforeach
            </div>

            {{-- Botón reasignar --}}
            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                <a href="{{ route('examenes.sinodales', $examen->id) }}"
                   class="inline-flex items-center gap-2 text-sm text-purple-600 hover:text-purple-800 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reasignar sinodales
                </a>
            </div>
        @endif
    </div>

</div>

@endsection