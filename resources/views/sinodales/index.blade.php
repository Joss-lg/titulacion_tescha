@extends('layouts.app')
@section('title', 'Sinodales')
@section('subtitle', 'Resumen de jurados asignados por examen')

@section('content')

{{-- Estadísticas --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-gray-600">{{ $stats['total_examenes'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Total exámenes</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-emerald-700">{{ $stats['con_jurado'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Jurado completo</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-amber-700">{{ $stats['jurado_incompleto'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Incompleto</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-red-600">{{ $stats['sin_jurado'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Sin jurado</p>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Tabla principal --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            @if($examenes->isEmpty())
                <div class="py-20 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 font-medium">Sin exámenes programados</p>
                    <a href="{{ route('examenes.create') }}"
                       class="inline-flex items-center gap-2 mt-4 bg-vino-900 text-white text-sm px-4 py-2 rounded-xl hover:bg-vino-800 transition-colors">
                        Programar examen
                    </a>
                </div>
            @else

                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Alumno</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Fecha</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jurado</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($examenes as $examen)
                        @php
                            $sinodales      = $examen->sinodales;
                            $presidente     = $sinodales->where('rol', 'presidente')->first();
                            $secretario     = $sinodales->where('rol', 'secretario')->first();
                            $vocal          = $sinodales->where('rol', 'vocal')->first();
                            $completo       = $sinodales->count() === 3;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors group">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-vino-100 text-vino-800 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($examen->alumno->nombre, 0, 1) . substr($examen->alumno->apellido_paterno, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ $examen->alumno->nombre_completo }}</p>
                                        <p class="text-xs font-mono text-gray-400">{{ $examen->alumno->matricula }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 hidden md:table-cell">
                                <p class="text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($examen->fecha)->isoFormat('D MMM YYYY') }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($examen->hora_inicio)->format('H:i') }} —
                                    {{ \Carbon\Carbon::parse($examen->hora_fin)->format('H:i') }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                @if($completo)
                                    <div class="space-y-1.5">
                                        @foreach(['presidente' => ['label' => 'P', 'color' => 'bg-vino-100 text-vino-700'], 'secretario' => ['label' => 'S', 'color' => 'bg-blue-100 text-blue-700'], 'vocal' => ['label' => 'V', 'color' => 'bg-purple-100 text-purple-700']] as $rol => $config)
                                        @php $sinodal = $sinodales->where('rol', $rol)->first(); @endphp
                                        @if($sinodal)
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded text-xs font-bold flex items-center justify-center flex-shrink-0 {{ $config['color'] }}">
                                                {{ $config['label'] }}
                                            </span>
                                            <span class="text-xs text-gray-600 truncate max-w-[160px]">
                                                {{ $sinodal->docente->nombre_completo }}
                                            </span>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                @elseif($sinodales->count() > 0)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                        {{ $sinodales->count() }}/3 asignados
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-500 bg-red-50 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full animate-pulse"></span>
                                        Sin jurado
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('examenes.sinodales', $examen->id) }}"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-purple-600 hover:bg-purple-50 transition-colors"
                                       title="{{ $completo ? 'Editar sinodales' : 'Asignar sinodales' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('examenes.show', $examen->id) }}"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-vino-700 hover:bg-vino-50 transition-colors"
                                       title="Ver examen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            @endif
        </div>
    </div>

    {{-- Panel lateral -- docentes más activos --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                Docentes más asignados
            </h3>

            @forelse($docentesMasActivos as $item)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0
                            {{ $item->docente->tipo === 'ptc' ? 'bg-vino-100 text-vino-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ strtoupper(substr($item->docente->nombre, 0, 1) . substr($item->docente->apellido_paterno, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $item->docente->nombre_completo }}</p>
                    <p class="text-xs text-gray-400">{{ $item->docente->tipo === 'ptc' ? 'PTC' : 'Asignatura' }}</p>
                </div>
                <span class="text-xs font-bold text-vino-700 bg-vino-50 px-2 py-1 rounded-full flex-shrink-0">
                    {{ $item->total }}x
                </span>
            </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Sin asignaciones aún.</p>
            @endforelse
        </div>

        {{-- Acceso rápido --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                Acciones rápidas
            </h3>
            <div class="space-y-2">
                <a href="{{ route('examenes.create') }}"
                   class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-vino-50 border border-gray-100 hover:border-vino-100 transition-all group">
                    <div class="w-7 h-7 bg-vino-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-vino-700 transition-colors">
                        <svg class="w-3.5 h-3.5 text-vino-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-600 group-hover:text-vino-800">Programar examen</span>
                </a>
                <a href="{{ route('examenes.index') }}"
                   class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-vino-50 border border-gray-100 hover:border-vino-100 transition-all group">
                    <div class="w-7 h-7 bg-vino-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-vino-700 transition-colors">
                        <svg class="w-3.5 h-3.5 text-vino-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-600 group-hover:text-vino-800">Ver todos los exámenes</span>
                </a>
            </div>
        </div>

    </div>

</div>

@endsection