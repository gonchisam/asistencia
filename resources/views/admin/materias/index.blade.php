@extends('layouts.app') 

@section('header')
    {{-- El header se mantiene en el layout, pero el estilo visual lo movemos a @section('content') --}}
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Materias
    </h2>
@endsection

@section('content')
    <div class="py-12">
        {{-- Contenedor principal: Estilo moderno (shadow-2xl, rounded-xl) --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">
                <div class="text-gray-900">
                    
                    {{-- 1. Encabezado y Botón de Creación Unificado --}}
                    {{-- 1. Encabezado y Botón de Creación Unificado --}}
<div class="flex flex-wrap items-center justify-between mb-8 pb-6 border-b border-gray-200 gap-4">
    
    {{-- Título Destacado en Azul --}}
    <h1 class="text-3xl font-extrabold text-gray-900">
        <span class="text-blue-600">Gestión de</span> Materias
    </h1>

    {{-- Contenedor de Botones --}}
    <div class="flex items-center space-x-3">

                            {{-- --- [INICIO] BOTÓN BORRAR TODO (NUEVO) --- --}}
                            {{-- Solo mostramos el botón si hay materias para borrar --}}
                            @if(!$materias->isEmpty())
                                <form action="{{ route('admin.materias.destroyAll') }}" method="POST" onsubmit="return confirm('⚠️ ¡ALERTA MÁXIMA! ⚠️\n\n¿Estás ABSOLUTAMENTE SEGURO de que quieres eliminar TODAS las materias?\n\nEsta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    
                                    {{-- Clases de botón de peligro (rojo) --}}
                                    <button type="submit"
                                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Borrar Todo
                                    </button>
                                </form>
                            @endif
                            {{-- --- [FIN] BOTÓN BORRAR TODO --- --}}


                            {{-- Botón Importar --}}
                            <a href="{{ route('admin.materias.importar.vista') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Importar
                            </a>

                            {{-- Botón de Crear Nueva Materia: Estilo azul moderno --}}
                            <a href="{{ route('admin.materias.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Crear Nueva Materia
                            </a>
                        </div>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- 2. Estructura de Pestañas --}}
                    @if($materias->isEmpty())
                        <div class="p-10 text-center bg-gray-50 border border-gray-200 rounded-lg">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"></path></svg>
                            <h3 class="mt-2 text-xl font-medium text-gray-900">No hay Materias registradas</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Empieza creando una nueva materia para asignar horarios y profesores.
                            </p>
                        </div>
                    @else
                        @php
                            $materiasPorCarrera = [];
                            foreach ($materias as $materia) {
                                $carrera = $materia->carrera;
                                $ano = $materia->ano_cursado;
                                
                                if (!isset($materiasPorCarrera[$carrera])) {
                                    $materiasPorCarrera[$carrera] = [];
                                }
                                
                                if (!isset($materiasPorCarrera[$carrera][$ano])) {
                                    $materiasPorCarrera[$carrera][$ano] = [];
                                }
                                
                                $materiasPorCarrera[$carrera][$ano][] = $materia;
                            }
                            
                            // Ordenar alfabéticamente por carrera
                            ksort($materiasPorCarrera);
                            $firstCarrera = array_key_first($materiasPorCarrera);
                        @endphp

                        {{-- Pestañas de Carreras (Nivel 1: Azul) --}}
                        <div class="mb-6">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                                    @foreach($materiasPorCarrera as $carrera => $materiasPorAno)
                                        <a href="#carrera-{{ Str::slug($carrera) }}" 
                                           class="tab-carrera whitespace-nowrap py-3 px-1 border-b-3 font-semibold text-base transition duration-150 ease-in-out
                                                  {{ ($carrera === $firstCarrera) ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                           data-carrera="{{ Str::slug($carrera) }}">
                                            {{ $carrera }}
                                        </a>
                                    @endforeach
                                </nav>
                            </div>
                        </div>

                        {{-- Contenido de las Pestañas de Carrera --}}
                        @foreach($materiasPorCarrera as $carrera => $materiasPorAno)
                            <div id="carrera-{{ Str::slug($carrera) }}" class="tab-content {{ ($carrera === $firstCarrera) ? '' : 'hidden' }}">
                                
                                {{-- Subpestañas de Años (Nivel 2: Verde) --}}
                                <div class="mb-4">
                                    <div class="border-b border-gray-200">
                                        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="SubTabs">
                                            @php
                                                // Ordenar años
                                                ksort($materiasPorAno);
                                                $firstAno = array_key_first($materiasPorAno);
                                            @endphp
                                            @foreach($materiasPorAno as $ano => $materiasLista)
                                                <a href="#{{ Str::slug($carrera) }}-ano-{{ $ano }}" 
                                                   class="tab-ano whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition duration-150 ease-in-out
                                                          {{ ($ano === $firstAno && $carrera === $firstCarrera) ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                                   data-carrera="{{ Str::slug($carrera) }}" 
                                                   data-ano="{{ $ano }}">
                                                    {{ $ano }}
                                                </a>
                                            @endforeach
                                        </nav>
                                    </div>
                                </div>

                                {{-- Contenido de las Subpestañas de Año --}}
                                @foreach($materiasPorAno as $ano => $materiasLista)
                                    <div id="{{ Str::slug($carrera) }}-ano-{{ $ano }}" 
                                         class="ano-content {{ ($ano === $firstAno && $carrera === $firstCarrera) ? '' : 'hidden' }}"
                                         data-carrera="{{ Str::slug($carrera) }}">
                                        
                                        @if(count($materiasLista) > 0)
                                            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-blue-50/50">
                                                        <tr>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nombre de la Materia</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Código</th>
                                                            <th scope="col" class="relative px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach ($materiasLista as $materia)
                                                            <tr class="hover:bg-blue-50/70 transition duration-150 ease-in-out">
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $materia->nombre }}</td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $materia->codigo ?? 'N/A' }}</td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                                    <a href="{{ route('admin.materias.edit', $materia) }}" class="text-blue-600 hover:text-blue-800 transition duration-150">Editar</a>
                                                                    <form action="{{ route('admin.materias.destroy', $materia) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ ¿Estás seguro de que quieres eliminar la materia &quot;{{ $materia->nombre }}&quot;? Esto eliminará todos sus registros asociados.');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="text-red-600 hover:text-red-800 transition duration-150">Eliminar</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-center text-gray-500 py-4">No hay materias registradas para este año.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Script de Pestañas Mejorado (Se mantiene, solo se ajustaron las clases activas) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Función para alternar clases de pestañas
            const toggleTabClasses = (tab, isActive, isSubTab = false) => {
                const activeColor = isSubTab ? 'green' : 'blue';
                const inactiveColor = 'gray';

                if (isActive) {
                    tab.classList.add(`border-${activeColor}-600`, `text-${activeColor}-700`);
                    tab.classList.remove('border-transparent', `text-${inactiveColor}-500`, `hover:text-${inactiveColor}-700`, `hover:border-${inactiveColor}-300`);
                } else {
                    tab.classList.remove(`border-${activeColor}-600`, `text-${activeColor}-700`);
                    tab.classList.add('border-transparent', `text-${inactiveColor}-500`, `hover:text-${inactiveColor}-700`, `hover:border-${inactiveColor}-300`);
                }
            };

            // Manejo de pestañas de carrera (Nivel 1)
            const carreraTabs = document.querySelectorAll('.tab-carrera');
            carreraTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Ocultar todos los contenidos de carrera
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Quitar clase activa de todas las pestañas de carrera
                    carreraTabs.forEach(t => toggleTabClasses(t, false));
                    
                    // Mostrar contenido de la pestaña seleccionada
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.remove('hidden');
                    
                    // Marcar pestaña de carrera como activa
                    toggleTabClasses(this, true);
                    
                    // Mostrar el primer año de la carrera seleccionada y marcarlo como activo
                    const firstAnoTab = document.querySelector(`.tab-ano[data-carrera="${this.dataset.carrera}"]`);
                    if (firstAnoTab) {
                        firstAnoTab.click();
                    }
                });
            });
            
            // Manejo de subpestañas de año (Nivel 2)
            const anoTabs = document.querySelectorAll('.tab-ano');
            anoTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const carrera = this.dataset.carrera;
                    
                    // Ocultar todos los contenidos de año de esta carrera
                    document.querySelectorAll(`.ano-content[data-carrera="${carrera}"]`).forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Quitar clase activa de todas las subpestañas de esta carrera
                    document.querySelectorAll(`.tab-ano[data-carrera="${carrera}"]`).forEach(t => toggleTabClasses(t, false, true));
                    
                    // Mostrar contenido de la subpestaña seleccionada
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.remove('hidden');
                    
                    // Marcar subpestaña de año como activa
                    toggleTabClasses(this, true, true);
                });
            });
            
            // Asegurar que la primera pestaña esté activa al cargar
            const initialTab = document.querySelector('.tab-carrera:first-child');
            if (initialTab) {
                // Dispara el evento click para inicializar correctamente el contenido del año
                initialTab.click(); 
            }
        });
    </script>

    {{-- Estilos para consistencia --}}
    <style>
        .border-b-3 {
            border-bottom-width: 3px; /* Más grueso para el nivel principal */
        }
        
        .tab-carrera, .tab-ano {
            transition: all 0.2s ease-in-out;
        }
        
        .tab-carrera {
            border-bottom-width: 3px;
        }
        
        .tab-carrera:hover {
            border-color: #93c5fd; /* Azul más claro en hover */
        }

        .tab-ano:hover {
            border-color: #bbf7d0; /* Verde más claro en hover */
        }
        
        /* Estilos de scrollbar se mantienen */
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: #a0aec0 #f7fafc;
        }
        
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f7fafc;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: #a0aec0;
            border-radius: 3px;
        }
    </style>
@endsection