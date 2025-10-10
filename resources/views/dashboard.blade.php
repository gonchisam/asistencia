@extends('layouts.app')

@section('content')
    {{-- Contenedor principal con el mismo estilo moderno del login --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-7xl mx-auto">
        
        {{-- Título principal con el mismo estilo --}}
        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">
            <span class="text-blue-600">Registro de Asistencia</span>
        </h2>

        {{-- Sección de búsqueda y filtro con el estilo del login --}}
        <div class="mb-8 flex items-center space-x-4">
            <div class="flex-1">
                <x-input-label for="search-uid" :value="__('Buscar por UID')" class="text-gray-700 font-semibold mb-2" />
                <x-text-input 
                    id="search-uid" 
                    class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                    type="text" 
                    placeholder="Ingresa el UID..." 
                />
            </div>
            <div class="flex items-end">
                <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 h-fit">
                    {{ __('Buscar') }}
                </x-primary-button>
            </div>
        </div>

        {{-- Mensajes de estado con el estilo del login --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded-lg text-green-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
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
                                <td class="py-4 px-6 text-left">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        {{ $asistencia->accion === 'Entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $asistencia->accion }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->modo }}</td>
                                <td class="py-4 px-6 text-left">{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p>No hay registros de asistencia recientes.</p>
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

    {{-- Script para funcionalidad de búsqueda (opcional) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-uid');
            const searchButton = document.querySelector('x-primary-button');
            
            // Función para realizar búsqueda (puedes implementar la lógica según tus necesidades)
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value.trim();
                if (searchTerm) {
                    // Aquí puedes implementar la lógica de búsqueda
                    console.log('Buscando:', searchTerm);
                    // Por ejemplo: window.location.href = `?search=${searchTerm}`;
                }
            });
            
            // Permitir búsqueda con Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchButton.click();
                }
            });
        });
    </script>
@endsection