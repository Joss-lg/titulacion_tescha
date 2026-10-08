<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Titulación ISC TESCHA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-vino-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Tarjeta principal --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

            {{-- Header vino --}}
            <div class="bg-vino-900 px-8 py-8 text-center">
                {{-- Logo / Escudo --}}
                <div class="w-16 h-16 bg-vino-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-white font-bold text-xl">ISC</span>
                </div>
                <h1 class="text-white text-xl font-semibold">Sistema de Titulación</h1>
                <p class="text-vino-300 text-sm mt-1">Carrera de Ingeniería en Sistemas Computacionales</p>
                <p class="text-vino-400 text-xs mt-0.5">TESCHA</p>
            </div>

            {{-- Formulario --}}
            <div class="px-8 py-8">

                <h2 class="text-gray-800 text-lg font-semibold mb-1">Bienvenida, Mtra.</h2>
                <p class="text-gray-400 text-sm mb-6">Ingresa tus credenciales para continuar</p>

                {{-- Error de sesión --}}
                @if (session('status'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="correo@tescha.edu.mx"
                            class="w-full px-4 py-3 border rounded-xl text-sm text-gray-800 placeholder-gray-300
                                   focus:outline-none focus:ring-2 focus:ring-vino-500 focus:border-transparent
                                   transition-all duration-150
                                   @error('email') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                        />
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 border rounded-xl text-sm text-gray-800 placeholder-gray-300
                                   focus:outline-none focus:ring-2 focus:ring-vino-500 focus:border-transparent
                                   transition-all duration-150
                                   @error('password') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                        />
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recordarme --}}
                    <div class="flex items-center">
                        <input
                            id="remember_me"
                            name="remember"
                            type="checkbox"
                            class="w-4 h-4 text-vino-700 border-gray-300 rounded focus:ring-vino-500"
                        />
                        <label for="remember_me" class="ml-2 text-sm text-gray-500">
                            Mantener sesión iniciada
                        </label>
                    </div>

                    {{-- Botón --}}
                    <button
                        type="submit"
                        class="w-full bg-vino-900 hover:bg-vino-800 active:bg-vino-950
                               text-white font-semibold py-3 px-6 rounded-xl
                               transition-all duration-150 shadow-sm hover:shadow-md
                               text-sm tracking-wide mt-2">
                        Iniciar sesión
                    </button>

                </form>

            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-vino-600 text-xs mt-6">
            Sistema interno · Carrera ISC · TESCHA {{ date('Y') }}
        </p>

    </div>

</body>
</html>