<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia SACA</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados para las pestañas */
        .tab-button.active {
            @apply text-blue-600 border-b-2 border-blue-600;
        }   
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        /* Estilos para el dropdown del usuario */
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
<body class="bg-gray-100 font-sans">

    <div class="min-h-screen flex flex-col">
        <header class="bg-gray-800 text-white shadow-md p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold">Control de Asistencia SACA</h1>
                
                {{-- Contenedor para la Navegación de Pestañas y Autenticación --}}
                <div class="flex items-center space-x-6">
                    {{-- Navegación de Pestañas --}}
                    <nav>
                        <ul class="flex space-x-6">
                            <li>
                                <button class="tab-button py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out" data-tab="asistencia">Asistencia</button>
                            </li>
                            {{-- Modificado: Enlace directo a la gestión de estudiantes --}}
                            <li>
                                <a href="{{ route('students.index') }}" class="py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out">Registrar Estudiante</a>
                            </li>
                            <li>
                                <button class="tab-button py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out" data-tab="configuracion">Configuración</button>
                            </li>
                            <li>
                                <button class="tab-button py-2 px-4 hover:text-blue-400 focus:outline-none transition duration-150 ease-in-out" data-tab="reportes">Reportes</button>
                            </li>
                        </ul>
                    </nav>

                    {{-- Bloque de Autenticación y Fecha/Hora --}}
                    <div class="flex items-center space-x-4">
                        <div class="text-gray-400 text-md">
                            <span id="current-date"></span> | <span id="current-time"></span>
                        </div>

                        @auth {{-- Si el usuario está autenticado --}}
                            <div class="relative group">
                                <button class="flex items-center text-gray-100 hover:text-blue-400 focus:outline-none focus:text-blue-400">
                                    <span class="font-medium mr-2">{{ Auth::user()->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity duration-200 ease-out invisible group-hover:visible group-focus-within:visible">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                    
                                    {{-- FORMULARIO DE LOGOUT --}}
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else {{-- Si el usuario NO está autenticado --}}
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('login') }}" class="text-gray-100 hover:text-blue-400 font-medium">Iniciar Sesión</a>
                                <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">Registrarse</a>
                            </div>
                        @endauth
                    </div>
                </div> {{-- Fin del contenedor de Navegación y Autenticación --}}

            </div>
        </header>

        <main class="flex-grow container mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div id="asistencia" class="tab-content active">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Registro y Detalle de Asistencias</h2>
                    
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Estudiantes Registrados</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {{-- Aquí se usaría Blade/PHP para iterar sobre los estudiantes --}}
                            @foreach($estudiantes as $estudiante)
                                {{-- Solo mostrar estudiantes con estado activo (1) --}}
                                @if($estudiante->estado == 1)
                                <div class="bg-blue-50 p-4 rounded-lg flex items-center space-x-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div>
                                        <h4 class="font-medium text-blue-800 text-lg">{{ $estudiante->nombre }}</h4>
                                        <p class="text-sm text-gray-600">UID: <span class="font-mono text-gray-700">{{ $estudiante->uid }}</span></p>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                            {{-- Fin de la iteración --}}
                        </div>
                    </div>
                    
                    {{-- Nuevas Tablas para llegadas por categoría --}}
                    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Tabla: Llegaron a Tiempo --}}
                        <div class="bg-white rounded-lg shadow p-4 border border-green-200">
                            <h3 class="text-xl font-semibold text-green-700 mb-4 border-b pb-2">Llegadas a Tiempo <span class="text-green-500">(✔)</span></h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($asistencias as $asistencia)
                                            {{-- Suponiendo que $asistencia->estado_llegada es 'a_tiempo', 'temprano' o 'tarde' --}}
                                            @if($asistencia->accion == 'ENTRADA' && $asistencia->estado_llegada == 'a_tiempo')
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-2 px-3 whitespace-nowrap text-sm text-gray-900">{{ $asistencia->nombre }}</td>
                                                <td class="py-2 px-3 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->fecha_hora->format('H:i:s') }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tabla: Llegaron Temprano --}}
                        <div class="bg-white rounded-lg shadow p-4 border border-blue-200">
                            <h3 class="text-xl font-semibold text-blue-700 mb-4 border-b pb-2">Llegadas Temprano <span class="text-blue-500">(⚡)</span></h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-blue-50">
                                        <tr>
                                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($asistencias as $asistencia)
                                            @if($asistencia->accion == 'ENTRADA' && $asistencia->estado_llegada == 'temprano')
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-2 px-3 whitespace-nowrap text-sm text-gray-900">{{ $asistencia->nombre }}</td>
                                                <td class="py-2 px-3 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->fecha_hora->format('H:i:s') }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tabla: Llegaron Tarde --}}
                        <div class="bg-white rounded-lg shadow p-4 border border-red-200">
                            <h3 class="text-xl font-semibold text-red-700 mb-4 border-b pb-2">Llegadas Tarde <span class="text-red-500">(❌)</span></h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-red-50">
                                        <tr>
                                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($asistencias as $asistencia)
                                            @if($asistencia->accion == 'ENTRADA' && $asistencia->estado_llegada == 'tarde')
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-2 px-3 whitespace-nowrap text-sm text-gray-900">{{ $asistencia->nombre }}</td>
                                                <td class="py-2 px-3 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->fecha_hora->format('H:i:s') }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 overflow-x-auto bg-white rounded-lg shadow">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Tabla Global de Asistencias por Orden de Llegada</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UID</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- Aquí se usaría Blade/PHP para iterar sobre las asistencias (asegúrate de que estén ordenadas por fecha_hora en el controlador) --}}
                                @foreach($asistencias as $asistencia)
                                <tr class="{{ $asistencia->accion == 'ENTRADA' ? 'bg-green-50' : 'bg-red-50' }} hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-900">{{ $asistencia->nombre }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $asistencia->uid }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $asistencia->accion == 'ENTRADA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $asistencia->accion }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->modo }}</td>
                                </tr>
                                @endforeach
                                {{-- Fin de la iteración --}}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6">
                        {{-- Esto es específico de Laravel/Blade para la paginación --}}
                        {{ $asistencias->links() }}
                    </div>
                </div>

                {{-- Contenido de "Configuración" --}}
                <div id="configuracion" class="tab-content">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Configuración del Sistema</h2>
                    <p class="text-gray-700">Aquí podrás gestionar la configuración del WiFi, ajustes del RTC, parámetros del servidor, etc.</p>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-semibold text-gray-800">Ejemplo de Función: Configuración WiFi</h4>
                        <p class="text-sm text-gray-600">SSID: <input type="text" class="border rounded px-2 py-1 mt-1"></p>
                        <p class="text-sm text-gray-600">Contraseña: <input type="password" class="border rounded px-2 py-1 mt-1"></p>
                        <button class="mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150">Guardar WiFi</button>
                    </div>
                </div>

                {{-- Contenido de "Reportes" --}}
                <div id="reportes" class="tab-content">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Generación de Reportes</h2>
                    <p class="text-gray-700">Desde aquí podrás generar reportes de asistencia por fechas, estudiantes o módulos.</p>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-semibold text-gray-800">Ejemplo de Función: Reporte por Fecha</h4>
                        <p class="text-sm text-gray-600">Fecha Inicio: <input type="date" class="border rounded px-2 py-1 mt-1"></p>
                        <p class="text-sm text-gray-600">Fecha Fin: <input type="date" class="border rounded px-2 py-1 mt-1"></p>
                        <button class="mt-3 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-150">Generar Reporte</button>
                    </div>
                </div>

            </div>
        </main>

        <footer class="bg-gray-800 text-white text-center p-4 mt-8">
            <p>&copy; 2025 Sistema de Asistencia RFID. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            // Función para activar una pestaña
            function activateTab(tabId) {
                // Remover 'active' de todos los botones y contenidos
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Añadir 'active' al botón clickeado (solo si es un botón de pestaña)
                const targetButton = document.querySelector(`.tab-button[data-tab=\"${tabId}\"]`);
                if (targetButton) {
                    targetButton.classList.add('active');
                }

                // Mostrar el contenido de la pestaña correspondiente
                const targetTabContent = document.getElementById(tabId);
                if (targetTabContent) {
                    targetTabContent.classList.add('active');
                }
            }

            // Activar la pestaña "Asistencia" por defecto al cargar la página
            activateTab('asistencia');

            // Asignar listeners a los botones de las pestañas
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTabId = button.dataset.tab;
                    activateTab(targetTabId);
                });
            });

            // Script para la fecha y hora actuales
            function updateDateTime() {
                const now = new Date();
                const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
                const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };

                document.getElementById('current-date').textContent = now.toLocaleDateString('es-ES', dateOptions);
                document.getElementById('current-time').textContent = now.toLocaleTimeString('es-ES', timeOptions);
            }

            // Actualizar la fecha y hora cada segundo
            updateDateTime(); // Llamar inmediatamente al cargar
            setInterval(updateDateTime, 1000); // Luego cada segundo
        });
    </script>
</body>
</html>