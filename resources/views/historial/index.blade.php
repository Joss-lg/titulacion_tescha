@extends('layouts.app')
@section('title', 'Historial de titulaciones')
@section('subtitle', 'Registro histórico de todos los alumnos titulados')

@section('content')

{{-- Estadísticas --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-vino-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-vino-800">{{ $stats['total'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Total titulados</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-emerald-700">{{ $stats['aprobados'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Aprobados</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-red-600">{{ $stats['no_aprobados'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">No aprobados</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-amber-700">{{ $stats['aplazados'] }}</span>
            </div>
            <p class="text-xs font-medium text-gray-500">Aplazados</p>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('historial.index') }}"
          class="flex items-center gap-3 flex-wrap">

        <div class="flex-1 min-w-40">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   placeholder="Buscar por nombre o matrícula..."
                   class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400"/>
        </div>

        @if($periodos->count() > 0)
        <select name="periodo"
                class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
            <option value="">Todos los periodos</option>
            @foreach($periodos as $p)
                <option value="{{ $p }}" {{ request('periodo') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
        @endif

        <select name="modalidad"
                class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
            <option value="">Todas las modalidades</option>
            <option value="tesis"                     {{ request('modalidad') === 'tesis' ? 'selected' : '' }}>Tesis</option>
            <option value="residencia_profesional"    {{ request('modalidad') === 'residencia_profesional' ? 'selected' : '' }}>Residencia</option>
            <option value="proyecto_de_investigacion" {{ request('modalidad') === 'proyecto_de_investigacion' ? 'selected' : '' }}>Investigación</option>
            <option value="memoria_de_experiencia"    {{ request('modalidad') === 'memoria_de_experiencia' ? 'selected' : '' }}>Memoria</option>
            <option value="titulacion_integral"       {{ request('modalidad') === 'titulacion_integral' ? 'selected' : '' }}>Integral</option>
        </select>

        <select name="resultado"
                class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
            <option value="">Todos los resultados</option>
            <option value="aprobado"    {{ request('resultado') === 'aprobado'    ? 'selected' : '' }}>Aprobado</option>
            <option value="no_aprobado" {{ request('resultado') === 'no_aprobado' ? 'selected' : '' }}>No aprobado</option>
            <option value="aplazado"    {{ request('resultado') === 'aplazado'    ? 'selected' : '' }}>Aplazado</option>
        </select>

        <button type="submit"
            class="px-4 py-2.5 bg-vino-900 hover:bg-vino-800 text-white text-sm font-medium rounded-xl transition-all">
            Filtrar
        </button>

        @if(request()->hasAny(['buscar','periodo','modalidad','resultado']))
            <a href="{{ route('historial.index') }}"
               class="px-4 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-xl hover:border-gray-300 transition-all">
                Limpiar
            </a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    @if($titulaciones->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Sin titulaciones registradas</p>
            <p class="text-gray-300 text-sm mt-1">
                @if(request()->hasAny(['buscar','periodo','modalidad','resultado']))
                    No hay resultados para los filtros aplicados.
                @else
                    Aquí aparecerán los alumnos conforme se vayan titulando.
                @endif
            </p>
        </div>
    @else
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Alumno</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Modalidad</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Fecha examen</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Jurado</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell">Folio oficio</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Periodo</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Resultado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($titulaciones as $t)
                @php
                    $resultadoConfig = [
                        'aprobado'    => ['label' => 'Aprobado',     'class' => 'text-emerald-700 bg-emerald-50'],
                        'no_aprobado' => ['label' => 'No aprobado',  'class' => 'text-red-600 bg-red-50'],
                        'aplazado'    => ['label' => 'Aplazado',     'class' => 'text-amber-700 bg-amber-50'],
                    ];
                    $res = $resultadoConfig[$t->resultado] ?? ['label' => $t->resultado, 'class' => 'text-gray-600 bg-gray-100'];
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors">

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($t->nombre_completo, 0, 1) . (strpos($t->nombre_completo, ' ') !== false ? substr($t->nombre_completo, strpos($t->nombre_completo, ' ') + 1, 1) : '')) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $t->nombre_completo }}</p>
                                <p class="text-xs font-mono text-gray-400">{{ $t->matricula }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 hidden md:table-cell">
                        <span class="text-xs text-gray-500">
                            {{ str_replace('_', ' ', ucfirst($t->modalidad)) }}
                        </span>
                    </td>

                    <td class="px-6 py-4 hidden lg:table-cell">
                        @if($t->fecha_examen)
                            <p class="text-sm text-gray-600">
                                {{ $t->fecha_examen->isoFormat('D [de] MMMM, YYYY') }}
                            </p>
                            @if($t->hora_inicio)
                                <p class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($t->hora_inicio)->format('H:i') }} —
                                    {{ \Carbon\Carbon::parse($t->hora_fin)->format('H:i') }}
                                    @if($t->salon) · {{ $t->salon }} @endif
                                </p>
                            @endif
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 hidden lg:table-cell">
                        @if($t->presidente)
                            <div class="space-y-0.5">
                                <p class="text-xs text-gray-500">
                                    <span class="font-semibold text-vino-600">P:</span> {{ $t->presidente }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    <span class="font-semibold text-blue-600">S:</span> {{ $t->secretario }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    <span class="font-semibold text-purple-600">V:</span> {{ $t->vocal }}
                                </p>
                            </div>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 hidden xl:table-cell">
                        @if($t->folio_oficio)
                            <span class="text-xs font-mono text-gray-700">{{ $t->folio_oficio }}</span>
                            @if($t->fecha_oficio)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $t->fecha_oficio->isoFormat('D MMM YYYY') }}
                                </p>
                            @endif
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 hidden md:table-cell">
                        <span class="text-xs text-gray-500">{{ $t->periodo ?? '—' }}</span>
                    </td>

                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $res['class'] }}">
                            {{ $res['label'] }}
                        </span>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
            <p class="text-xs text-gray-400">
                Mostrando {{ $titulaciones->count() }} registro(s)
            </p>
        </div>
    @endif

</div>

@endsection