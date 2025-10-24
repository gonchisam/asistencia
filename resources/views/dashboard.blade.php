@extends('layouts.app')

@section('content')
    {{-- Contenedor principal con el mismo estilo moderno del login --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-7xl mx-auto">
        
        {{-- Título principal con el mismo estilo --}}
        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">
            <span class="text-blue-600">Registro de Asistencia</span>
        </h2>

        {{-- Formulario de búsqueda y filtros --}}
        <form method="GET" action="{{ route('dashboard') }}">
            {{-- Sección de búsqueda y filtro con el estilo del login --}}
            <div class="mb-8">
                {{-- Fila 1: Búsqueda por UID --}}
                <div class="flex items-end space-x-4 mb-6">
                    <div class="flex-1">
                        <x-input-label for="search-uid" :value="__('Buscar por UID')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            id="search-uid" 
                            name="uid"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                            type="text" 
                            placeholder="Ingresa el UID..." 
                            value="{{ request('uid') }}"
                        />
                    </div>
                </div>

                {{-- Fila 2: Filtros por Carrera y Año --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Filtro por Carrera --}}
                    <div>
                        <x-input-label for="carrera" :value="__('Filtrar por Carrera')" class="text-gray-700 font-semibold mb-2" />
                        <select 
                            id="carrera" 
                            name="carrera"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                        >
                            <option value="">Todas las carreras</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera }}" {{ request('carrera') == $carrera ? 'selected' : '' }}>
                                    {{ $carrera }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por Año --}}
                    <div>
                        <x-input-label for="año" :value="__('Filtrar por Año')" class="text-gray-700 font-semibold mb-2" />
                        <select 
                            id="año" 
                            name="año"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                        >
                            <option value="">Todos los años</option>
                            @foreach($años as $año)
                                <option value="{{ $año }}" {{ request('año') == $año ? 'selected' : '' }}>
                                    {{ $año }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Fila 3: Solo 2 botones alineados a la izquierda --}}
                <div class="flex items-center space-x-4">
                    {{-- Botón Buscar --}}
                    <x-primary-button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('Buscar') }}
                    </x-primary-button>

                    {{-- Botón Limpiar Filtros --}}
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 no-underline"
                    >
                        {{ __('Limpiar Filtros') }}
                    </a>
                </div>
            </div>
        </form>

        {{-- Mensajes de estado con el estilo del login --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded-lg text-green-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        {{-- Mostrar filtros activos --}}
        @if(request()->anyFilled(['uid', 'carrera', 'año']))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">Filtros activos:</h4>
                <div class="flex flex-wrap gap-2">
                    @if(request('uid'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                            UID: {{ request('uid') }}
                        </span>
                    @endif
                    @if(request('carrera'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">
                            Carrera: {{ request('carrera') }}
                        </span>
                    @endif
                    @if(request('año'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                            Año: {{ request('año') }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Tabla de Asistencia Reciente --}}
        <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Asistencias Recientes</h3>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-4 px-6 text-left font-semibold">UID</th>
                            <th class="py-4 px-6 text-left font-semibold">Nombre</th>
                            <th class="py-4 px-6 text-left font-semibold">Carrera</th>
                            <th class="py-4 px-6 text-left font-semibold">Año</th>
                            <th class="py-4 px-6 text-left font-semibold">Acción</th>
                            <th class="py-4 px-6 text-left font-semibold">Modo</th>
                            <th class="py-4 px-6 text-left font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        @forelse ($asistencias as $asistencia)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                                <td class="py-4 px-6 text-left whitespace-nowrap">
                                    <span class="font-medium">{{ $asistencia->uid }}</span>
                                </td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->nombre }}</td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->carrera ?? 'N/A' }}</td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->año ?? 'N/A' }}</td>
                                <td class="py-4 px-6 text-left">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        {{ $asistencia->accion === 'ENTRADA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $asistencia->accion }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->modo }}</td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p>No hay registros de asistencia recientes.</p>
                                        @if(request()->anyFilled(['uid', 'carrera', 'año']))
                                            <p class="text-sm text-gray-400 mt-1">Intenta con otros filtros</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación con el estilo del login --}}
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow-sm p-4">
                {{ $asistencias->links() }}
            </div>
        </div>
    </div>

    {{-- Script para funcionalidad de búsqueda --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-uid');
            
            // Permitir búsqueda con Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        });
    </script>
@endsection