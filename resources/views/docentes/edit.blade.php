@extends('layouts.app')
@section('title', 'Editar docente')
@section('subtitle', $docente->nombre_completo)

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('docentes.show', $docente) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-vino-700 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Regresar al docente
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">
        <h2 class="text-base font-semibold text-gray-800 mb-6 pb-4 border-b border-gray-100">
            Editar información
        </h2>

        <form method="POST" action="{{ route('docentes.update', $docente) }}" class="space-y-5">
            @csrf @method('PATCH')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nombre(s) *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $docente->nombre) }}" required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('nombre') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('nombre')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Apellido paterno *</label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $docente->apellido_paterno) }}" required
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('apellido_paterno') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('apellido_paterno')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Apellido materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $docente->apellido_materno) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Tipo *</label>
                    <select name="tipo" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400">
                        <option value="asignatura" {{ old('tipo', $docente->tipo) === 'asignatura' ? 'selected' : '' }}>Docente de asignatura</option>
                        <option value="ptc"        {{ old('tipo', $docente->tipo) === 'ptc'        ? 'selected' : '' }}>PTC</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Correo</label>
                    <input type="email" name="email" value="{{ old('email', $docente->email) }}"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                  @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror"/>
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $docente->telefono) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all"/>
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <input type="checkbox" name="activo" id="activo" value="1"
                       {{ old('activo', $docente->activo) ? 'checked' : '' }}
                       class="w-4 h-4 text-vino-700 rounded border-gray-300 focus:ring-vino-400"/>
                <label for="activo" class="text-sm font-medium text-gray-600">Docente activo</label>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <form method="POST" action="{{ route('docentes.destroy', $docente) }}"
                      onsubmit="return confirm('¿Seguro que deseas eliminar este docente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-400 hover:text-red-600 transition-colors">
                        Eliminar docente
                    </button>
                </form>
                <div class="flex items-center gap-3">
                    <a href="{{ route('docentes.show', $docente) }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-500 rounded-xl border border-gray-200 hover:border-gray-300 transition-all">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-vino-900 hover:bg-vino-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md">
                        Guardar cambios
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection
