<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema de Asistencia SACA') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        @stack('styles') {{-- Para CSS específico de alguna vista --}}

        @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Si usas Vite --}}

        <style>
            /* Estilos adicionales si los necesitas, por ejemplo para el dropdown del usuario */
            .group:hover .group-hover\:opacity-100 {
                opacity: 1;
                visibility: visible;
            }
            .group-focus-within\:opacity-100:focus-within {
                opacity: 1;
                visibility: visible;
            }
            .group-hover\:visible, .group-focus-within\:visible:focus-within {
                visibility: visible;
            }
        </style>
    </head>
    <body class="font-sans antialiased flex flex-col min-h-screen">
        <div class="min-h-screen bg-gray-100 flex flex-col w-full">
            <header class="bg-gray-800 text-white shadow-md p-4">
                <div class="container mx-auto flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Control de Asistencia SACA</h1>
                    <nav>
                        <ul class="flex space-x-6">
                            <li>
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                    Asistencia
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('students.index') }}" class="inline-flex items-center py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('students.index') || request()->routeIs('students.create') || request()->routeIs('students.edit') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                    Registrar Estudiante
                                </a>
                            </li>
                            
                            <li>
                                <a href="{{ route('reportes.index') }}" class="inline-flex items-center py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('reportes.index') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                    Reportes
                                </a>
                            </li>
                            {{-- AÑADE ESTA LÍNEA PARA EL NUEVO ENLACE --}}
                            <li>
                                <a href="{{ route('estadisticas.index') }}" class="inline-flex items-center py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('estadisticas.index') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                                    Estadísticas
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="flex items-center space-x-4">
                        <div class="text-gray-400 text-md">
                            <span id="current-date"></span> | <span id="current-time"></span>
                        </div>
                        @auth
                            <div class="relative group">
                                <button class="flex items-center text-gray-100 hover:text-blue-400 focus:outline-none focus:text-blue-400">
                                    <span class="font-medium mr-2">{{ Auth::user()->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity duration-200 ease-out invisible group-hover:visible group-focus-within:visible">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('login') }}" class="text-gray-100 hover:text-blue-400 font-medium">Iniciar Sesión</a>
                                <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">Registrarse</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="flex-grow container mx-auto px-4 py-8">
                @yield('content')
            </main>

            <footer class="bg-gray-800 text-white text-center p-4 mt-8">
                <p>&copy; {{ date('Y') }} Sistema de Asistencia SACA</p>
            </footer>
        </div>

        <script>
            // Script para la fecha y hora actual, ahora vive en el layout principal
            function updateDateTime() {
                const now = new Date();
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };

                const currentDateElement = document.getElementById('current-date');
                const currentTimeElement = document.getElementById('current-time');

                if (currentDateElement) {
                    currentDateElement.textContent = now.toLocaleDateString('es-ES', dateOptions);
                }
                if (currentTimeElement) {
                    currentTimeElement.textContent = now.toLocaleTimeString('es-ES', timeOptions);
                }
            }

            // Actualiza cada segundo
            setInterval(updateDateTime, 1000);
            // Llama una vez al cargar para mostrarla inmediatamente
            updateDateTime();
        </script>
        {{-- MUEVE ESTE SCRIPT AL FINAL DEL ARCHIVO PARA OPTIMIZAR LA CARGA DE LA PÁGINA --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @stack('scripts')
    </body>
</html>