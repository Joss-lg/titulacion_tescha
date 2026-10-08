@extends('layouts.app')
@section('title', 'Importar asignación académica')
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

    {{-- Info --}}
    <div class="bg-vino-50 border border-vino-100 rounded-2xl p-5 mb-5 flex items-start gap-4">
        <div class="w-10 h-10 bg-vino-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-vino-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-vino-800">Importación inteligente con IA</p>
            <p class="text-sm text-vino-600 mt-1">
                Sube el PDF de la asignación académica del TESCHA y el sistema extraerá automáticamente
                todos los horarios, bloques y hora de comida.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">

        <form method="POST" action="{{ route('docentes.importar.procesar', $docente) }}"
              enctype="multipart/form-data" id="form-importar">
            @csrf

            {{-- Drop zone --}}
            <div id="drop-zone"
                 class="border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center cursor-pointer
                        hover:border-vino-300 hover:bg-vino-50/30 transition-all duration-200"
                 onclick="document.getElementById('pdf-input').click()">

                <div id="drop-icon">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-600">Arrastra el PDF aquí</p>
                    <p class="text-xs text-gray-400 mt-1">o haz clic para seleccionar</p>
                    <p class="text-xs text-gray-300 mt-3">Solo archivos PDF · Máx. 10 MB</p>
                </div>

                <div id="drop-selected" class="hidden">
                    <div class="w-14 h-14 bg-vino-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-vino-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-vino-700" id="file-name">archivo.pdf</p>
                    <p class="text-xs text-gray-400 mt-1">PDF listo para procesar</p>
                </div>

                <input type="file" name="pdf" id="pdf-input" accept=".pdf" class="hidden"
                       onchange="mostrarArchivo(this)"/>
            </div>

            @error('pdf')
                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror

            {{-- Botón --}}
            <div class="mt-6">
                <button type="submit" id="btn-procesar"
                    class="w-full flex items-center justify-center gap-2 bg-vino-900 hover:bg-vino-800
                           text-white font-semibold py-3 rounded-xl transition-all shadow-sm hover:shadow-md text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Analizar con IA y extraer horarios
                </button>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
function mostrarArchivo(input) {
    if (input.files && input.files[0]) {
        document.getElementById('drop-icon').classList.add('hidden');
        document.getElementById('drop-selected').classList.remove('hidden');
        document.getElementById('file-name').textContent = input.files[0].name;
    }
}

// Drag & drop
const zone = document.getElementById('drop-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-vino-400','bg-vino-50'); });
zone.addEventListener('dragleave', () => zone.classList.remove('border-vino-400','bg-vino-50'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('border-vino-400','bg-vino-50');
    const input = document.getElementById('pdf-input');
    input.files = e.dataTransfer.files;
    mostrarArchivo(input);
});

// Loading al enviar
document.getElementById('form-importar').addEventListener('submit', function() {
    const btn = document.getElementById('btn-procesar');
    btn.innerHTML = `
        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        Analizando PDF con IA...
    `;
    btn.disabled = true;
});
</script>
@endpush

@endsection