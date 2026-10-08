@extends('layouts.app')
@section('title', 'Exámenes')
@section('subtitle', 'Exámenes de titulación programados')

@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-400">{{ $examenes->count() }} examen(es) registrado(s)</p>
    <a href="{{ route('examenes.create') }}"
       class="flex items-center gap-2 bg-vino-900 hover:bg-vino-800 text-white text-sm font-medium
              px-4 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Programar examen
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    @if($examenes->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Sin exámenes programados</p>
            <p class="text-gray-300 text-sm mt-1">Programa el primer examen de titulación</p>
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
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha y hora</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Sinodales</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($examenes as $examen)
                @php
                    $sinodalesCount = $examen->sinodales->count();
                    $estadoColor = match($examen->estado) {
                        'programado' => 'text-blue-700 bg-blue-50',
                        'realizado'  => 'text-emerald-700 bg-emerald-50',
                        'cancelado'  => 'text-red-600 bg-red-50',
                        default      => 'text-gray-600 bg-gray-100',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-vino-100 text-vino-800 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($examen->alumno->nombre, 0, 1) . substr($examen->alumno->apellido_paterno, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $examen->alumno->nombre_completo }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $examen->alumno->matricula }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($examen->fecha)->isoFormat('dddd D [de] MMMM, YYYY') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ \Carbon\Carbon::parse($examen->hora_inicio)->format('H:i') }} —
                            {{ \Carbon\Carbon::parse($examen->hora_fin)->format('H:i') }}
                            @if($examen->salon)
                                · {{ $examen->salon }}
                            @endif
                        </p>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        @if($sinodalesCount === 3)
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                <span class="text-xs text-emerald-600 font-medium">Completos (3/3)</span>
                            </div>
                        @elseif($sinodalesCount > 0)
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 bg-amber-400 rounded-full"></span>
                                <span class="text-xs text-amber-600 font-medium">Parcial ({{ $sinodalesCount }}/3)</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></span>
                                <span class="text-xs text-red-500 font-medium">Sin asignar</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $estadoColor }}">
                            {{ ucfirst($examen->estado) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('examenes.show', $examen) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-vino-700 hover:bg-vino-50 transition-colors"
                               title="Ver detalle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('examenes.sinodales', $examen) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-purple-600 hover:bg-purple-50 transition-colors"
                               title="Asignar sinodales">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('examenes.destroy', $examen) }}"
                                  onsubmit="return confirm('¿Eliminar este examen?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection