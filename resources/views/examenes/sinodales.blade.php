@extends('layouts.app')
@section('title', 'Asignar sinodales')
@section('subtitle', $examen->alumno->nombre_completo . ' · ' . \Carbon\Carbon::parse($examen->fecha)->isoFormat('D [de] MMMM, YYYY'))

@section('content')

<div class="flex items-center justify-between mb-6">
    <a href="{{ route('examenes.show', $examen) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Regresar al examen
    </a>
</div>

{{-- Info del examen --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-center gap-4 flex-wrap">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-sm font-semibold text-gray-700">{{ $examen->alumno->nombre_completo }}</span>
            <span class="text-xs text-gray-400 font-mono">{{ $examen->alumno->matricula }}</span>
        </div>
        <div class="w-px h-4 bg-gray-200 hidden sm:block"></div>
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm text-gray-600">
                {{ \Carbon\Carbon::parse($examen->fecha)->isoFormat('dddd D [de] MMMM, YYYY') }}
            </span>
        </div>
        <div class="w-px h-4 bg-gray-200 hidden sm:block"></div>
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm text-gray-600 font-medium">
                {{ \Carbon\Carbon::parse($examen->hora_inicio)->format('H:i') }} —
                {{ \Carbon\Carbon::parse($examen->hora_fin)->format('H:i') }}
            </span>
        </div>
        @if($examen->salon)
            <div class="w-px h-4 bg-gray-200 hidden sm:block"></div>
            <span class="text-sm text-gray-500">Salón {{ $examen->salon }}</span>
        @endif

        <div class="ml-auto">
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full">
                {{ count($candidatos['disponibles']) }} docente(s) disponibles
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Formulario de asignación --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
            Asignar sinodales
        </h3>
        <p class="text-xs text-gray-400 mb-5 ml-4">
            Cualquier docente activo y disponible puede ocupar cualquier rol.
        </p>

        @if(count($candidatos['disponibles']) < 3)
            <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-4 flex items-start gap-3">
                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-red-600">
                    Solo hay <strong>{{ count($candidatos['disponibles']) }}</strong> docente(s) disponibles.
                    Se necesitan al menos 3 para completar el jurado.
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('examenes.sinodales.store', $examen) }}" class="space-y-4">
            @csrf

            @foreach([
                'presidente' => ['label' => 'Presidente', 'color' => 'vino',   'ring' => 'focus:ring-vino-400'],
                'secretario' => ['label' => 'Secretario', 'color' => 'blue',   'ring' => 'focus:ring-blue-400'],
                'vocal'      => ['label' => 'Vocal',      'color' => 'purple', 'ring' => 'focus:ring-purple-400'],
            ] as $rol => $config)

            @php
                $sinodalActual = $examen->sinodales->where('rol', $rol)->first();
                $textColor = match($config['color']) {
                    'vino'   => 'text-vino-700',
                    'blue'   => 'text-blue-600',
                    'purple' => 'text-purple-600',
                    default  => 'text-gray-600',
                };
                $bgColor = match($config['color']) {
                    'vino'   => 'bg-vino-50 border-vino-100',
                    'blue'   => 'bg-blue-50 border-blue-100',
                    'purple' => 'bg-purple-50 border-purple-100',
                    default  => 'bg-gray-50 border-gray-100',
                };
            @endphp

            <div class="p-4 rounded-xl border {{ $bgColor }}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider {{ $textColor }}">
                        {{ $config['label'] }}
                    </span>
                    <span class="text-xs text-gray-400">— Docente o PTC</span>
                </div>
                <select name="{{ $rol }}" required
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl
                               focus:outline-none {{ $config['ring'] }} focus:ring-2 bg-white transition-all">
                    <option value="">Seleccionar docente...</option>
                    @foreach($candidatos['disponibles'] as $candidato)
                        <option value="{{ $candidato['docente']->id }}"
                            {{ $sinodalActual && $sinodalActual->docente_id == $candidato['docente']->id ? 'selected' : '' }}>
                            {{ $candidato['docente']->nombre_completo }}
                            ({{ $candidato['docente']->tipo === 'ptc' ? 'PTC' : 'Asignatura' }})
                        </option>
                    @endforeach
                </select>
                @error($rol)
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            @endforeach

            {{-- Datos del oficio --}}
            <div class="border-t border-gray-100 pt-4 mt-2">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                    Datos del oficio
                    <span class="font-normal text-gray-400 normal-case">(asignados por control escolar)</span>
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Número de folio</label>
                        <input type="text"
                               name="folio_oficio"
                               value="{{ old('folio_oficio', $examen->folio_oficio) }}"
                               placeholder="Ej. ISC/2026/042"
                               maxlength="30"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl
                                      focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all">
                        @error('folio_oficio')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Fecha del oficio</label>
                        <input type="date"
                               name="fecha_oficio"
                               value="{{ old('fecha_oficio', $examen->fecha_oficio?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl
                                      focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all">
                        @error('fecha_oficio')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-vino-900 hover:bg-vino-800
                       text-white font-semibold py-3 rounded-xl transition-all shadow-sm text-sm mt-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Confirmar asignación y generar oficio
            </button>
        </form>
    </div>

    {{-- Panel de disponibilidad --}}
    <div class="space-y-4">

        {{-- Disponibles --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                Disponibles ({{ count($candidatos['disponibles']) }})
            </h3>

            @forelse($candidatos['disponibles'] as $c)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0
                            {{ $c['docente']->tipo === 'ptc' ? 'bg-vino-100 text-vino-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ strtoupper(substr($c['docente']->nombre, 0, 1) . substr($c['docente']->apellido_paterno, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">
                        {{ $c['docente']->nombre_completo }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $c['docente']->tipo === 'ptc' ? 'PTC' : 'Asignatura' }}
                        · {{ \Carbon\Carbon::parse($c['horario']->hora_entrada)->format('H:i') }} -
                          {{ \Carbon\Carbon::parse($c['horario']->hora_salida)->format('H:i') }}
                    </p>
                </div>
                <span class="w-5 h-5 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">
                    Ningún docente disponible para este horario.
                </p>
            @endforelse
        </div>

        {{-- No disponibles --}}
        @if(count($candidatos['no_disponibles']) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                No disponibles ({{ count($candidatos['no_disponibles']) }})
            </h3>
            <div class="max-h-56 overflow-y-auto space-y-0">
                @foreach($candidatos['no_disponibles'] as $c)
                <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400
                                flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($c['docente']->nombre, 0, 1) . substr($c['docente']->apellido_paterno, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500 truncate">{{ $c['docente']->nombre_completo }}</p>
                        <p class="text-xs text-red-400">{{ $c['razon'] }}</p>
                    </div>
                    <span class="w-5 h-5 bg-red-50 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@endsection