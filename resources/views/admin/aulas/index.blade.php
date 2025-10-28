@extends('layouts.app') {{-- Usa tu layout principal --}}

@section('content')
    {{-- Contenedor principal con el mismo estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-7xl mx-auto">
        
        {{-- Encabezado unificado: Flecha | Título | Botón de Creación --}}
        <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-200">
            
            {{-- Contenedor Izquierdo: Flecha de atrás y el Título --}}
            <div class="flex items-center space-x-4">
                {{-- Flecha "Atrás" (Back Arrow) --}}
                <a href="{{ url()->previous() }}"
                   class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                   title="Volver a la página anterior">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>

                {{-- Título Destacado --}}
                        <h1 class="text-3xl font-extrabold text-gray-900">
                            <span class="text-blue-600">Gestión de</span> Aulas
                        </h1>
            </div>
            
            {{-- Contenedor Derecho: Botón Crear Nueva Aula (Movido aquí) --}}
            <a href="{{ route('admin.aulas.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 inline-flex items-center text-sm md:text-base">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Crear Nueva Aula') }}
            </a>
        </div>
        
        {{-- Mensajes de estado (Se mantienen igual) --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded-lg text-green-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        
        {{-- ELIMINADA la sección de botones duplicada que estaba aquí antes --}}

        {{-- Estructura de Pestañas por Ubicación --}}
        @if($aulas->isEmpty())
             <div class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <p class="text-lg text-gray-600">No hay aulas registradas.</p>
                    <p class="text-sm text-gray-500 mt-2">Comienza creando la primera aula.</p>
                </div>
            </div>
        @else
            @php
                $aulasPorUbicacion = [];
                // La paginación en Laravel devuelve un objeto Paginator. Necesitas acceder a los items para iterar
                $items = method_exists($aulas, 'items') ? $aulas->items() : $aulas; 
                
                foreach ($items as $aula) {
                    $ubicacion = $aula->ubicacion ?: 'Sin especificar';
                    if (!isset($aulasPorUbicacion[$ubicacion])) {
                        $aulasPorUbicacion[$ubicacion] = [];
                    }
                    $aulasPorUbicacion[$ubicacion][] = $aula;
                }
                ksort($aulasPorUbicacion);
            @endphp

            {{-- Pestañas de Ubicación con estilo moderno --}}
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                        @foreach($aulasPorUbicacion as $ubicacion => $aulasLista)
                            <a href="#ubicacion-{{ Str::slug($ubicacion) }}" 
                               class="tab-ubicacion whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition duration-200 ease-in-out 
                                        {{ $loop->first ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                               data-ubicacion="{{ Str::slug($ubicacion) }}">
                                {{ $ubicacion }} ({{ count($aulasLista) }})
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- Contenido de las Pestañas (Tabla con estilo de estudiantes) --}}
            @foreach($aulasPorUbicacion as $ubicacion => $aulasLista)
                <div id="ubicacion-{{ Str::slug($ubicacion) }}" class="tab-content {{ $loop->first ? '' : 'hidden' }}">
                    @if(count($aulasLista) > 0)
                        {{-- Tabla de aulas con estilo unificado --}}
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                                    <tr>
                                        <th scope="col" class="py-4 px-6 text-left">Nombre</th>
                                        <th scope="col" class="py-4 px-6 text-left">Código</th>
                                        <th scope="col" class="py-4 px-6 text-left">Ubicación</th>
                                        <th scope="col" class="py-4 px-6 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 text-sm font-light">
                                    @foreach ($aulasLista as $aula)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                                            <td class="py-4 px-6 text-left whitespace-nowrap">
                                                <span class="font-medium text-gray-900">{{ $aula->nombre }}</span>
                                            </td>
                                            <td class="py-4 px-6 text-left">
                                                <span class="font-mono text-gray-700">{{ $aula->codigo }}</span>
                                            </td>
                                            <td class="py-4 px-6 text-left">{{ $aula->ubicacion ?: 'Sin especificar' }}</td>
                                            <td class="py-4 px-6 text-center">
                                                <div class="flex items-center justify-center space-x-3">
                                                    <a href="{{ route('admin.aulas.edit', $aula) }}" 
                                                        class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out font-medium">
                                                        Editar
                                                    </a>
                                                    <form action="{{ route('admin.aulas.destroy', $aula) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta aula? Esta acción es irreversible.');">
                                                         @csrf
                                                         @method('DELETE')
                                                         <button type="submit" class="text-red-600 hover:text-red-800 transition duration-150 ease-in-out font-medium">
                                                             Eliminar
                                                         </button>
                                                     </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-4">No hay aulas registradas en esta ubicación.</p>
                    @endif
                </div>
            @endforeach
        @endif

        

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de pestañas de ubicación
            const ubicacionTabs = document.querySelectorAll('.tab-ubicacion');
            const initialTab = document.querySelector('.tab-ubicacion.border-blue-600');
            
            // Mostrar el primer contenido al cargar
            if (initialTab) {
                const initialTargetId = initialTab.getAttribute('href').substring(1);
                const initialContent = document.getElementById(initialTargetId);
                if (initialContent) {
                    // Asegurarse que todos estén ocultos por defecto
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden'); 
                    });
                    initialContent.classList.remove('hidden'); // Mostrar solo el primero
                }
            }


            ubicacionTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Ocultar todos los contenidos de ubicación
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Quitar clase activa de todas las pestañas
                    ubicacionTabs.forEach(t => {
                        t.classList.remove('border-blue-600', 'text-blue-600');
                        t.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    // Mostrar contenido de la pestaña seleccionada
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.remove('hidden');
                    
                    // Marcar pestaña como activa
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-blue-600', 'text-blue-600');
                });
            });
        });
    </script>

    <style>
        /* Estilos de scrollbar y transición para mantener la uniformidad con el código de estudiantes */
        .tab-ubicacion {
            transition: all 0.2s ease-in-out;
        }
        
        .tab-ubicacion:hover {
            border-color: #d1d5db;
        }
        
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }
        
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f7fafc;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 3px;
        }
    </style>
@endsection