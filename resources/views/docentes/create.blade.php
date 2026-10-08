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

        <form method="POST" action="{{ route('docentes.store') }}" class="space-y-5"
              enctype="multipart/form-data">
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
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Grado académico *</label>
                    <select name="grado" required
                            class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-vino-400 transition-all
                                   @error('grado') border-red-300 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">Seleccionar...</option>
                        @foreach(['Lic.', 'Ing.', 'Mtro.', 'Mtra.', 'Dr.', 'Dra.'] as $g)
                            <option value="{{ $g }}" {{ old('grado') === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                    @error('grado')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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

            {{-- PDF de horarios (opcional) --}}
            <div class="border-t border-gray-100 pt-5">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                    Asignación académica <span class="font-normal text-gray-400 normal-case">(opcional — puedes subirla después)</span>
                </p>

                <div id="drop-zone-create"
                     class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer
                            hover:border-vino-300 hover:bg-vino-50/30 transition-all duration-200"
                     onclick="document.getElementById('pdf-input-create').click()">

                    <div id="drop-icon-create">
                        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">Arrastra el PDF o <span class="text-vino-700 font-medium">haz clic para seleccionar</span></p>
                        <p class="text-xs text-gray-400 mt-1">Solo PDF · Máx. 10 MB</p>
                    </div>

                    <div id="drop-selected-create" class="hidden">
                        <div class="w-10 h-10 bg-vino-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5 text-vino-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-vino-700" id="file-name-create">archivo.pdf</p>
                        <p class="text-xs text-gray-400 mt-0.5">PDF listo · <button type="button" onclick="quitarPdf(event)" class="text-red-400 hover:text-red-600">Quitar</button></p>
                    </div>

                    <input type="file" name="pdf" id="pdf-input-create" accept=".pdf" class="hidden"
                           onchange="mostrarArchivoPdf(this)"/>
                </div>

                @error('pdf')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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

@push('scripts')
<script>
function mostrarArchivoPdf(input) {
    if (input.files && input.files[0]) {
        document.getElementById('drop-icon-create').classList.add('hidden');
        document.getElementById('drop-selected-create').classList.remove('hidden');
        document.getElementById('file-name-create').textContent = input.files[0].name;
    }
}

function quitarPdf(e) {
    e.stopPropagation();
    const input = document.getElementById('pdf-input-create');
    input.value = '';
    document.getElementById('drop-icon-create').classList.remove('hidden');
    document.getElementById('drop-selected-create').classList.add('hidden');
}

// Drag & drop
const zone = document.getElementById('drop-zone-create');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-vino-400','bg-vino-50'); });
zone.addEventListener('dragleave', () => zone.classList.remove('border-vino-400','bg-vino-50'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('border-vino-400','bg-vino-50');
    const input = document.getElementById('pdf-input-create');
    input.files = e.dataTransfer.files;
    mostrarArchivoPdf(input);
});
</script>
@endpush

@endsection