@extends('layouts.app')
@section('title', $docente->nombre_completo)
@section('subtitle', $docente->tipo === 'ptc' ? 'Profesor de Tiempo Completo' : 'Docente de asignatura')

@section('content')

<div class="flex items-center justify-between mb-6">
    <a href="{{ route('docentes.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Todos los docentes
    </a>

    <div class="flex items-center gap-2">

        {{-- Importar PDF --}}
        <a href="{{ route('docentes.importar.form', $docente) }}"
           class="flex items-center gap-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Importar PDF
        </a>

        {{-- Limpiar horarios --}}
        @if($horarios->count() > 0)
        <form method="POST" action="{{ route('docentes.horarios.limpiar', $docente) }}"
              onsubmit="return confirm('¿Eliminar TODOS los horarios de {{ $docente->nombre_completo }}?\n\nEsto se hace al cambiar de semestre para importar el nuevo PDF.')">
            @csrf @method('DELETE')
            <button type="submit"
                class="flex items-center gap-2 text-sm font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 px-4 py-2 rounded-xl transition-colors border border-amber-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Limpiar horarios
            </button>
        </form>
        @endif

        {{-- Editar --}}
        <a href="{{ route('docentes.edit', $docente) }}"
           class="flex items-center gap-2 text-sm font-medium text-vino-700 bg-vino-50 hover:bg-vino-100 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Info principal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col items-center text-center mb-5">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-xl font-bold mb-3
                        {{ $docente->tipo === 'ptc' ? 'bg-vino-100 text-vino-800' : 'bg-gray-100 text-gray-600' }}">
                {{ strtoupper(substr($docente->nombre, 0, 1) . substr($docente->apellido_paterno, 0, 1)) }}
            </div>
            <h2 class="font-bold text-gray-800">{{ $docente->nombre_completo }}</h2>
            <div class="mt-2">
                @if($docente->tipo === 'ptc')
                    <span class="text-xs font-semibold text-vino-700 bg-vino-50 px-3 py-1 rounded-full">PTC</span>
                @else
                    <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-3 py-1 rounded-full">Asignatura</span>
                @endif
            </div>
        </div>

        <div class="space-y-3 text-sm border-t border-gray-100 pt-4">
            <div class="flex items-center gap-3 text-gray-500">
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="truncate text-xs">{{ $docente->email ?? 'Sin correo registrado' }}</span>
            </div>
            <div class="flex items-center gap-3 text-gray-500">
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span class="text-xs">{{ $docente->telefono ?? 'Sin teléfono' }}</span>
            </div>
            <div class="flex items-center gap-3">
                @if($docente->activo)
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-emerald-600 text-sm">Activo</span>
                @else
                    <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                    <span class="text-gray-400 text-sm">Inactivo</span>
                @endif
            </div>
        </div>

        {{-- Resumen horarios --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">Días registrados</span>
                <span class="text-xs font-bold {{ $horarios->count() > 0 ? 'text-emerald-600' : 'text-amber-500' }}">
                    {{ $horarios->count() }} / 6
                </span>
            </div>
            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-vino-400 rounded-full transition-all"
                     style="width: {{ ($horarios->count() / 6) * 100 }}%"></div>
            </div>
        </div>
    </div>

    {{-- Horarios --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Lista con edición inline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                Horarios registrados
                @if($horarios->count() > 0)
                    <span class="ml-auto text-xs text-gray-400 font-normal">
                        Clic en ✏️ para editar
                    </span>
                @endif
            </h3>

            @if($horarios->isEmpty())
                <div class="py-8 text-center">
                    <p class="text-sm text-gray-400">Sin horarios registrados.</p>
                    <p class="text-xs text-gray-300 mt-1">Agrégalos manualmente o importa el PDF de asignación académica.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($horarios as $h)
                    <div class="rounded-xl border border-gray-100 overflow-hidden" id="horario-{{ $h->id }}">

                        {{-- Vista normal --}}
                        <div class="flex items-center justify-between p-3 bg-gray-50 horario-view-{{ $h->id }}">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="w-24 text-xs font-semibold text-vino-700 bg-vino-50 px-2 py-1 rounded-lg text-center capitalize">
                                    {{ $h->dia }}
                                </span>
                                <span class="text-sm text-gray-700 font-medium">
                                    {{ \Carbon\Carbon::parse($h->hora_entrada)->format('H:i') }} —
                                    {{ \Carbon\Carbon::parse($h->hora_salida)->format('H:i') }}
                                </span>
                                @if($h->tiene_bloque_muerto)
                                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">
                                        Bloque: {{ \Carbon\Carbon::parse($h->bloque_muerto_inicio)->format('H:i') }}-{{ \Carbon\Carbon::parse($h->bloque_muerto_fin)->format('H:i') }}
                                    </span>
                                @endif
                                @if($h->comida_inicio)
                                    <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                                        Comida: {{ \Carbon\Carbon::parse($h->comida_inicio)->format('H:i') }}-{{ \Carbon\Carbon::parse($h->comida_fin)->format('H:i') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" onclick="toggleEditHorario({{ $h->id }})"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all"
                                    title="Editar horario">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('docentes.horarios.destroy', [$docente, $h]) }}"
                                      onsubmit="return confirm('¿Eliminar el horario del {{ ucfirst($h->dia) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all"
                                        title="Eliminar horario">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Form edición inline (oculto) --}}
                        <div class="horario-edit-{{ $h->id }} hidden p-4 bg-blue-50/40 border-t border-blue-100">
                            <form method="POST" action="{{ route('docentes.horarios.update', [$docente, $h]) }}">
                                @csrf @method('PUT')

                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Entrada *</label>
                                        <input type="time" name="hora_entrada"
                                               value="{{ \Carbon\Carbon::parse($h->hora_entrada)->format('H:i') }}"
                                               required
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"/>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Salida *</label>
                                        <input type="time" name="hora_salida"
                                               value="{{ \Carbon\Carbon::parse($h->hora_salida)->format('H:i') }}"
                                               required
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"/>
                                    </div>
                                </div>

                                {{-- Bloque muerto --}}
                                <div class="p-3 bg-amber-50 rounded-lg border border-amber-100 mb-3">
                                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                                        <input type="checkbox" name="tiene_bloque_muerto"
                                               id="bloque-edit-{{ $h->id }}" value="1"
                                               {{ $h->tiene_bloque_muerto ? 'checked' : '' }}
                                               onchange="document.getElementById('bloque-edit-fields-{{ $h->id }}').classList.toggle('hidden', !this.checked)"
                                               class="w-4 h-4 text-amber-500 rounded border-amber-300"/>
                                        <span class="text-xs font-medium text-amber-700">Tiene bloque muerto</span>
                                    </label>
                                    <div id="bloque-edit-fields-{{ $h->id }}"
                                         class="{{ $h->tiene_bloque_muerto ? '' : 'hidden' }} grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-amber-600 mb-1">Inicio</label>
                                            <input type="time" name="bloque_muerto_inicio"
                                                   value="{{ $h->bloque_muerto_inicio ? \Carbon\Carbon::parse($h->bloque_muerto_inicio)->format('H:i') : '' }}"
                                                   class="w-full px-2 py-1.5 text-sm border border-amber-200 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-amber-300"/>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-amber-600 mb-1">Fin</label>
                                            <input type="time" name="bloque_muerto_fin"
                                                   value="{{ $h->bloque_muerto_fin ? \Carbon\Carbon::parse($h->bloque_muerto_fin)->format('H:i') : '' }}"
                                                   class="w-full px-2 py-1.5 text-sm border border-amber-200 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-amber-300"/>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hora de comida solo PTC --}}
                                @if($docente->tipo === 'ptc')
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 mb-3">
                                    <p class="text-xs font-medium text-blue-700 mb-2">Hora de comida (PTC)</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-blue-600 mb-1">Inicio</label>
                                            <input type="time" name="comida_inicio"
                                                   value="{{ $h->comida_inicio ? \Carbon\Carbon::parse($h->comida_inicio)->format('H:i') : '' }}"
                                                   class="w-full px-2 py-1.5 text-sm border border-blue-200 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-blue-300"/>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-blue-600 mb-1">Fin</label>
                                            <input type="time" name="comida_fin"
                                                   value="{{ $h->comida_fin ? \Carbon\Carbon::parse($h->comida_fin)->format('H:i') : '' }}"
                                                   class="w-full px-2 py-1.5 text-sm border border-blue-200 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-blue-300"/>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="toggleEditHorario({{ $h->id }})"
                                        class="px-4 py-1.5 text-xs font-medium text-gray-500 rounded-lg border border-gray-200 hover:border-gray-300 transition-all">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all">
                                        Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Agregar horario --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-vino-700 rounded-full"></span>
                Agregar horario
            </h3>

            <form method="POST" action="{{ route('docentes.horarios.store', $docente) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Día *</label>
                        <select name="dia" required
                                class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
                            <option value="">Día...</option>
                            @foreach(['lunes','martes','miercoles','jueves','viernes','sabado'] as $dia)
                                <option value="{{ $dia }}" {{ old('dia') === $dia ? 'selected' : '' }}>
                                    {{ ucfirst($dia) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Entrada *</label>
                        <input type="time" name="hora_entrada" value="{{ old('hora_entrada') }}" required
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Salida *</label>
                        <input type="time" name="hora_salida" value="{{ old('hora_salida') }}" required
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400"/>
                    </div>
                </div>

                {{-- Bloque muerto --}}
                <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="tiene_bloque_muerto" id="chk-bloque" value="1"
                               {{ old('tiene_bloque_muerto') ? 'checked' : '' }}
                               class="w-4 h-4 text-amber-500 rounded border-amber-300 focus:ring-amber-400"
                               onchange="document.getElementById('bloque-fields').classList.toggle('hidden', !this.checked)"/>
                        <span class="text-sm font-medium text-amber-700">Tiene bloque muerto</span>
                        <span class="text-xs text-amber-500">(horas no pagadas)</span>
                    </label>
                    <div id="bloque-fields" class="{{ old('tiene_bloque_muerto') ? '' : 'hidden' }} grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-xs text-amber-600 mb-1">Inicio bloque</label>
                            <input type="time" name="bloque_muerto_inicio" value="{{ old('bloque_muerto_inicio') }}"
                                   class="w-full px-3 py-2 text-sm border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300 bg-white"/>
                        </div>
                        <div>
                            <label class="block text-xs text-amber-600 mb-1">Fin bloque</label>
                            <input type="time" name="bloque_muerto_fin" value="{{ old('bloque_muerto_fin') }}"
                                   class="w-full px-3 py-2 text-sm border border-amber-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300 bg-white"/>
                        </div>
                    </div>
                </div>

                {{-- Hora de comida solo PTC --}}
                @if($docente->tipo === 'ptc')
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-sm font-medium text-blue-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Hora de comida (PTC — 1 hora)
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-blue-600 mb-1">Inicio comida</label>
                            <input type="time" name="comida_inicio" value="{{ old('comida_inicio') }}"
                                   class="w-full px-3 py-2 text-sm border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"/>
                        </div>
                        <div>
                            <label class="block text-xs text-blue-600 mb-1">Fin comida</label>
                            <input type="time" name="comida_fin" value="{{ old('comida_fin') }}"
                                   class="w-full px-3 py-2 text-sm border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white"/>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit"
                        class="flex items-center gap-2 bg-vino-900 hover:bg-vino-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar horario
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
function toggleEditHorario(id) {
    const edit    = document.querySelector(`.horario-edit-${id}`);
    const card    = document.getElementById(`horario-${id}`);
    const isHidden = edit.classList.contains('hidden');

    edit.classList.toggle('hidden', !isHidden);
    card.classList.toggle('ring-2', isHidden);
    card.classList.toggle('ring-blue-200', isHidden);
}
</script>
@endpush

@endsection