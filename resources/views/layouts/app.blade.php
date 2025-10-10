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
        @stack('styles')

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .dropdown-menu {
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.3s ease-in-out;
            }
            
            .dropdown-menu.show {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            
            .nav-link {
                position: relative;
                transition: all 0.3s ease-in-out;
            }
            
            .nav-link::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0;
                height: 2px;
                background: #3b82f6;
                transition: width 0.3s ease-in-out;
            }
            
            .nav-link.active::after,
            .nav-link:hover::after {
                width: 100%;
            }
        </style>
    </head>
    <body class="font-sans antialiased flex flex-col min-h-screen bg-gray-50">
        {{-- Contenedor principal --}}
        <div class="min-h-screen flex flex-col w-full">
            {{-- Header con estilo moderno --}}
            <header class="bg-white shadow-2xl border-b border-gray-200 p-4">
                <div class="container mx-auto flex justify-between items-center">
                    {{-- Logo y título --}}
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-600 rounded-xl p-3 shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-extrabold text-gray-900">
                            <span class="text-blue-600">SACA</span> <span class="text-gray-700">Asistencia</span>
                        </h1>
                    </div>

                    {{-- Navegación principal --}}
                    <nav class="flex-1 mx-8">
                        <ul class="flex space-x-8 justify-center">
                            <li>
                                <a href="{{ route('dashboard') }}" 
                                   class="nav-link inline-flex items-center py-2 px-6 text-lg font-semibold text-gray-700 hover:text-blue-600 focus:outline-none transition duration-300 ease-in-out {{ request()->routeIs('dashboard') ? 'active text-blue-600' : '' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Asistencia
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('students.index') }}" 
                                   class="nav-link inline-flex items-center py-2 px-6 text-lg font-semibold text-gray-700 hover:text-blue-600 focus:outline-none transition duration-300 ease-in-out {{ request()->routeIs('students.index') || request()->routeIs('students.create') || request()->routeIs('students.edit') ? 'active text-blue-600' : '' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Estudiantes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reportes.index') }}" 
                                   class="nav-link inline-flex items-center py-2 px-6 text-lg font-semibold text-gray-700 hover:text-blue-600 focus:outline-none transition duration-300 ease-in-out {{ request()->routeIs('reportes.index') ? 'active text-blue-600' : '' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Reportes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('estadisticas.index') }}" 
                                   class="nav-link inline-flex items-center py-2 px-6 text-lg font-semibold text-gray-700 hover:text-blue-600 focus:outline-none transition duration-300 ease-in-out {{ request()->routeIs('estadisticas.index') ? 'active text-blue-600' : '' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Estadísticas
                                </a>
                            </li>
                        </ul>
                    </nav>

                    {{-- Información de usuario y fecha/hora --}}
                    <div class="flex items-center space-x-6">
                        {{-- Fecha y hora --}}
                        <div class="bg-blue-50 rounded-xl p-3 shadow-sm border border-blue-100">
                            <div class="text-center">
                                <div id="current-date" class="text-sm font-semibold text-blue-800"></div>
                                <div id="current-time" class="text-lg font-bold text-blue-600"></div>
                            </div>
                        </div>

                        {{-- Menú de usuario --}}
                        @auth
                            <div class="relative">
                                <button id="user-menu-button" 
                                        class="flex items-center space-x-3 bg-white rounded-xl shadow-lg p-3 border border-gray-200 hover:shadow-xl transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center shadow-md">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-semibold text-gray-900 text-sm">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-gray-500 capitalize">
                                            {{ Auth::user()->role }}
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                         id="dropdown-arrow" 
                                         fill="none" 
                                         stroke="currentColor" 
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- Menú desplegable --}}
                                <div id="user-dropdown" 
                                     class="dropdown-menu absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-200 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <div class="font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                                        <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                                        <div class="text-xs text-blue-600 font-medium capitalize mt-1">
                                            {{ Auth::user()->role }}
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('profile.edit') }}" 
                                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition duration-150 ease-in-out">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Mi Perfil
                                    </a>
                                    
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="flex items-center w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition duration-150 ease-in-out">
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('login') }}" 
                                   class="text-gray-700 hover:text-blue-600 font-semibold transition duration-200 ease-in-out">
                                    Iniciar Sesión
                                </a>
                                <a href="{{ route('register') }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition duration-200 ease-in-out transform hover:-translate-y-0.5">
                                    Registrarse
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Contenido principal --}}
            <main class="flex-grow container mx-auto px-4 py-8">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="bg-white border-t border-gray-200 py-6 mt-8">
                <div class="container mx-auto text-center">
                    <p class="text-gray-600 font-medium">
                        &copy; {{ date('Y') }} Sistema de Asistencia 
                        <span class="text-blue-600 font-bold">SACA</span>
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Control y gestión de asistencia académica
                    </p>
                </div>
            </footer>
        </div>

        {{-- Scripts --}}
        <script>
            // Script para la fecha y hora actual
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
            updateDateTime();

            // Script para el menú desplegable del usuario
            document.addEventListener('DOMContentLoaded', function() {
                const userMenuButton = document.getElementById('user-menu-button');
                const userDropdown = document.getElementById('user-dropdown');
                const dropdownArrow = document.getElementById('dropdown-arrow');

                if (userMenuButton && userDropdown) {
                    // Alternar menú al hacer clic
                    userMenuButton.addEventListener('click', function(e) {
                        e.stopPropagation();
                        userDropdown.classList.toggle('show');
                        dropdownArrow.classList.toggle('rotate-180');
                    });

                    // Cerrar menú al hacer clic fuera
                    document.addEventListener('click', function(e) {
                        if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                            userDropdown.classList.remove('show');
                            dropdownArrow.classList.remove('rotate-180');
                        }
                    });

                    // Prevenir que el menú se cierre al hacer clic dentro de él
                    userDropdown.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @stack('scripts')
    </body>
</html>