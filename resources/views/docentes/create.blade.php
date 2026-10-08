@extends('layouts.app')
@section('title', 'Nuevo docente')
@section('subtitle', 'Registrar un nuevo docente o PTC')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('docentes.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Regresar a docentes
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">

        <h2 class="text-base font-semibold text-gray-800 mb-6 pb-4 border-b border-gray-100">
            Información del docente
        </h2>

        <form method="POST" action="{{ route('docentes.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nombre(s) *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           placeholder="Ej. María Guadalupe"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('nombre') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('nombre')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Apellido paterno *</label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required
                           placeholder="Ej. Hernández"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('apellido_paterno') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('apellido_paterno')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Apellido materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}"
                           placeholder="Ej. López"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Tipo de docente *</label>
                    <select name="tipo" required
                            class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                   @error('tipo') border-red-300 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">Seleccionar...</option>
                        <option value="asignatura" {{ old('tipo') === 'asignatura' ? 'selected' : '' }}>Docente de asignatura</option>
                        <option value="ptc"        {{ old('tipo') === 'ptc'        ? 'selected' : '' }}>Profesor de Tiempo Completo (PTC)</option>
                    </select>
                    @error('tipo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="correo@tescha.edu.mx"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           placeholder="Ej. 7771234567"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('docentes.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200 hover:border-gray-300 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-vino-900 hover:bg-vino-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md">
                    Guardar docente
                </button>
            </div>

        </form>
    </div>
</div>

@endsection