@extends('layouts.app')

@section('header')
    {{-- El header se mantiene en el layout, pero el estilo visual lo movemos a @section('content') --}}
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Cursos (Grupos)
    </h2>
@endsection

@section('content')
    <div class="py-12">
        {{-- Contenedor principal: Estilo moderno (shadow-2xl, rounded-xl) --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">
                <div class="text-gray-900">
                    
                    {{-- 1. Encabezado y Botones de Creación Unificado --}}
                    <div class="flex items-start justify-between mb-8 pb-6 border-b border-gray-200">
                        
                        {{-- Título Destacado en Azul --}}
                        <h1 class="text-3xl font-extrabold text-gray-900">
                            <span class="text-blue-600">Gestión de</span> Cursos
                        </h1>

                        {{-- Contenedor de Botones (alineados a la derecha) --}}
                        <div class="flex space-x-3">
                            {{-- Botón de Crear Nuevo Curso: Estilo azul moderno --}}
                            <a href="{{ route('admin.cursos.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Crear Curso
                            </a>
                            {{-- Botón de Importar: Estilo verde moderno --}}
                            <a href="{{ route('admin.inscripciones.importar.vista') }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 014 4v2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4-4m0 0l-4 4m4-4v9"></path></svg>
                                Importar (Excel)
                            </a>
                        </div>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- 2. Estructura de Pestañas --}}
                    @if($cursos->isEmpty())
                        <div class="p-10 text-center bg-gray-50 border border-gray-200 rounded-lg">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M6 21h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zm12 0L18 3M6 21L6 3"></path></svg>
                            <h3 class="mt-2 text-xl font-medium text-gray-900">No hay Cursos (Grupos) registrados</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Empieza creando un nuevo curso para asignar estudiantes y profesores.
                            </p>
                        </div>
                    @else
                        {{-- Organizar cursos por carrera, año y paralelo --}}
                        @php
                            $cursosPorCarrera = [];
                            foreach ($cursos as $curso) {
                                $carrera = $curso->carrera;
                                $ano = $curso->ano_cursado;
                                $paralelo = $curso->paralelo;
                                
                                if (!isset($cursosPorCarrera[$carrera])) {
                                    $cursosPorCarrera[$carrera] = [];
                                }
                                
                                if (!isset($cursosPorCarrera[$carrera][$ano])) {
                                    $cursosPorCarrera[$carrera][$ano] = [];
                                }
                                
                                if (!isset($cursosPorCarrera[$carrera][$ano][$paralelo])) {
                                    $cursosPorCarrera[$carrera][$ano][$paralelo] = [];
                                }
                                
                                $cursosPorCarrera[$carrera][$ano][$paralelo][] = $curso;
                            }
                            
                            // Ordenar alfabéticamente por carrera y obtener la primera
                            ksort($cursosPorCarrera);
                            $firstCarrera = array_key_first($cursosPorCarrera);
                        @endphp

                        {{-- Pestañas de Carreras (Nivel 1: Azul) --}}
                        <div class="mb-6">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                                    @foreach($cursosPorCarrera as $carrera => $cursosPorAno)
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
                        @foreach($cursosPorCarrera as $carrera => $cursosPorAno)
                            @php
                                // Determinar si esta es la primera carrera (para el estado inicial)
                                $isFirstCarrera = ($carrera === $firstCarrera);
                            @endphp
                            <div id="carrera-{{ Str::slug($carrera) }}" class="tab-content {{ $isFirstCarrera ? '' : 'hidden' }}">
                                
                                {{-- Subpestañas de Años (Nivel 2: Verde) --}}
                                <div class="mb-4">
                                    <div class="border-b border-gray-200">
                                        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="SubTabs">
                                            @php
                                                // Ordenar años y obtener el primero
                                                ksort($cursosPorAno);
                                                $firstAno = array_key_first($cursosPorAno);
                                            @endphp
                                            @foreach($cursosPorAno as $ano => $cursosPorParalelo)
                                                @php
                                                    $isFirstAno = ($ano === $firstAno && $isFirstCarrera);
                                                @endphp
                                                <a href="#{{ Str::slug($carrera) }}-ano-{{ $ano }}"
                                                   class="tab-ano whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition duration-150 ease-in-out
                                                        {{ $isFirstAno ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                                   data-carrera="{{ Str::slug($carrera) }}"
                                                   data-ano="{{ $ano }}">
                                                     {{ $ano }}° Año
                                                </a>
                                            @endforeach
                                        </nav>
                                    </div>
                                </div>

                                {{-- Contenido de las Subpestañas de Año --}}
                                @foreach($cursosPorAno as $ano => $cursosPorParalelo)
                                    @php
                                        $isFirstAnoContent = ($ano === $firstAno && $isFirstCarrera);
                                    @endphp
                                    <div id="{{ Str::slug($carrera) }}-ano-{{ $ano }}"
                                         class="ano-content {{ $isFirstAnoContent ? '' : 'hidden' }}"
                                         data-carrera="{{ Str::slug($carrera) }}">
                                        
                                        {{-- Subpestañas de Paralelos (Nivel 3: Púrpura) --}}
                                        <div class="mb-4">
                                            <div class="border-b border-gray-200">
                                                <nav class="-mb-px flex space-x-4 overflow-x-auto" aria-label="ParaleloTabs">
                                                    @php
                                                        // Ordenar paralelos alfabéticamente y obtener el primero
                                                        ksort($cursosPorParalelo);
                                                        $firstParalelo = array_key_first($cursosPorParalelo);
                                                    @endphp
                                                    @foreach($cursosPorParalelo as $paralelo => $cursosLista)
                                                        @php
                                                            $isFirstParalelo = ($paralelo === $firstParalelo && $isFirstAnoContent);
                                                        @endphp
                                                        <a href="#{{ Str::slug($carrera) }}-ano-{{ $ano }}-paralelo-{{ $paralelo }}"
                                                           class="tab-paralelo whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition duration-150 ease-in-out
                                                                {{ $isFirstParalelo ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                                           data-carrera="{{ Str::slug($carrera) }}"
                                                           data-ano="{{ $ano }}"
                                                           data-paralelo="{{ $paralelo }}">
                                                             Paralelo {{ $paralelo }}
                                                        </a>
                                                    @endforeach
                                                </nav>
                                            </div>
                                        </div>

                                        {{-- Contenido de los Paralelos (Tabla) --}}
                                        @foreach($cursosPorParalelo as $paralelo => $cursosLista)
                                            @php
                                                $isFirstParaleloContent = ($paralelo === $firstParalelo && $isFirstAnoContent);
                                            @endphp
                                            <div id="{{ Str::slug($carrera) }}-ano-{{ $ano }}-paralelo-{{ $paralelo }}"
                                                 class="paralelo-content-data {{ $isFirstParaleloContent ? '' : 'hidden' }}"
                                                 data-carrera="{{ Str::slug($carrera) }}"
                                                 data-ano="{{ $ano }}">
                                                
                                                @if(count($cursosLista) > 0)
                                                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-blue-50/50">
                                                                <tr>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Materia</th>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Docente</th>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Gestión</th>
                                                                    <th scope="col" class="relative px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @foreach ($cursosLista as $curso)
                                                                    <tr class="hover:bg-blue-50/70 transition duration-150 ease-in-out">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $curso->materia_nombre }}</td>
                                                                        {{-- Asumiendo que 'docente_nombre' existe en el modelo $curso --}}
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $curso->docente_nombre ?? 'Sin Asignar' }}</td> 
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $curso->gestion }}</td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                                            <a href="{{ route('admin.cursos.show', $curso) }}" class="text-blue-600 hover:text-blue-800 transition duration-150">Gestionar</a>
                                                                            <a href="{{ route('admin.cursos.edit', $curso) }}" class="text-green-600 hover:text-green-800 transition duration-150">Editar</a>
                                                                            <form action="{{ route('admin.cursos.destroy', $curso) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ ¿Estás seguro de que quieres eliminar el curso &quot;{{ $curso->materia_nombre }} (P. {{ $curso->paralelo }})&quot;? Esto eliminará todos sus registros asociados (horarios, inscripciones, etc.).');">
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
                                                    <p class="text-center text-gray-500 py-4">No hay cursos registrados para este paralelo.</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                    
                    {{-- Paginación (Se mantiene, pero se debe asegurar que funcione correctamente con la paginación de Laravel) --}}
                    <div class="mt-6">
                        {{-- Asegúrate que $cursos es un objeto paginado de Laravel (ej: $cursos = Curso::paginate(10);) --}}
                        {{ $cursos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Script de Pestañas Multinivel Adaptado --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Función para alternar clases de pestañas
            const toggleTabClasses = (tab, isActive, level = 1) => {
                let activeColor, inactiveColor, activeBorder, activeText;

                // Nivel 1: Carrera (Azul)
                if (level === 1) {
                    activeColor = 'blue';
                    activeBorder = 'border-blue-600';
                    activeText = 'text-blue-700';
                // Nivel 2: Año (Verde)
                } else if (level === 2) {
                    activeColor = 'green';
                    activeBorder = 'border-green-600';
                    activeText = 'text-green-700';
                // Nivel 3: Paralelo (Púrpura)
                } else if (level === 3) {
                    activeColor = 'purple';
                    activeBorder = 'border-purple-600';
                    activeText = 'text-purple-700';
                }

                inactiveColor = 'gray';

                if (isActive) {
                    tab.classList.add(activeBorder, activeText);
                    tab.classList.remove('border-transparent', `text-${inactiveColor}-500`, `hover:text-${inactiveColor}-700`, `hover:border-${inactiveColor}-300`);
                } else {
                    tab.classList.remove(activeBorder, activeText);
                    tab.classList.add('border-transparent', `text-${inactiveColor}-500`, `hover:text-${inactiveColor}-700`, `hover:border-${inactiveColor}-300`);
                }
            };

            // Manejo de pestañas de carrera (Nivel 1)
            const carreraTabs = document.querySelectorAll('.tab-carrera');
            carreraTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const currentCarreraSlug = this.dataset.carrera;
                    
                    // 1. Ocultar todos los contenidos de carrera
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // 2. Quitar clase activa de todas las pestañas de carrera
                    carreraTabs.forEach(t => toggleTabClasses(t, false, 1));
                    
                    // 3. Mostrar contenido de la pestaña seleccionada
                    document.getElementById(`carrera-${currentCarreraSlug}`).classList.remove('hidden');
                    
                    // 4. Marcar pestaña de carrera como activa
                    toggleTabClasses(this, true, 1);
                    
                    // 5. Click en el primer año de la carrera seleccionada
                    const firstAnoTab = document.querySelector(`.tab-ano[data-carrera="${currentCarreraSlug}"]`);
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
                    const carreraSlug = this.dataset.carrera;
                    const ano = this.dataset.ano;

                    // 1. Ocultar todos los contenidos de año de esta carrera
                    document.querySelectorAll(`.ano-content[data-carrera="${carreraSlug}"]`).forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // 2. Quitar clase activa de todas las subpestañas de esta carrera
                    document.querySelectorAll(`.tab-ano[data-carrera="${carreraSlug}"]`).forEach(t => toggleTabClasses(t, false, 2));
                    
                    // 3. Mostrar contenido de la subpestaña seleccionada (Contenedor de Paralelos)
                    document.getElementById(`${carreraSlug}-ano-${ano}`).classList.remove('hidden');
                    
                    // 4. Marcar subpestaña de año como activa
                    toggleTabClasses(this, true, 2);

                    // 5. Click en el primer paralelo de este año
                    const firstParaleloTab = document.querySelector(`.tab-paralelo[data-carrera="${carreraSlug}"][data-ano="${ano}"]`);
                    if (firstParaleloTab) {
                        firstParaleloTab.click();
                    }
                });
            });

            // Manejo de subpestañas de paralelo (Nivel 3)
            const paraleloTabs = document.querySelectorAll('.tab-paralelo');
            paraleloTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const carreraSlug = this.dataset.carrera;
                    const ano = this.dataset.ano;

                    // 1. Ocultar todos los contenidos de paralelo de este año y carrera
                    document.querySelectorAll(`.paralelo-content-data[data-carrera="${carreraSlug}"][data-ano="${ano}"]`).forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // 2. Quitar clase activa de todas las subpestañas de paralelo de este año y carrera
                    document.querySelectorAll(`.tab-paralelo[data-carrera="${carreraSlug}"][data-ano="${ano}"]`).forEach(t => toggleTabClasses(t, false, 3));
                    
                    // 3. Mostrar contenido de la subpestaña seleccionada (Tabla de Cursos)
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.remove('hidden');
                    
                    // 4. Marcar subpestaña de paralelo como activa
                    toggleTabClasses(this, true, 3);
                });
            });
            
            // Asegurar que la primera pestaña de carrera esté activa al cargar, lo cual disparará la activación de Año y Paralelo.
            const initialTab = document.querySelector('.tab-carrera:first-child');
            if (initialTab) {
                // Previene un click redundante si ya está activo por las clases Blade, pero asegura la inicialización del contenido del año/paralelo.
                if (!initialTab.classList.contains('border-blue-600')) {
                    initialTab.click();
                } else {
                    // Si ya está activo por Blade, solo llama a click en su primer año para inicializar el segundo y tercer nivel
                    const currentCarreraSlug = initialTab.dataset.carrera;
                    const firstAnoTab = document.querySelector(`.tab-ano[data-carrera="${currentCarreraSlug}"]:not(.border-green-600)`);
                    if (firstAnoTab) {
                        firstAnoTab.click();
                    } else {
                        // Si el primer año también está activo, inicializa el paralelo
                        const firstActiveAnoTab = document.querySelector(`.tab-ano[data-carrera="${currentCarreraSlug}"].border-green-600`);
                        if (firstActiveAnoTab) {
                            const ano = firstActiveAnoTab.dataset.ano;
                            const firstParaleloTab = document.querySelector(`.tab-paralelo[data-carrera="${currentCarreraSlug}"][data-ano="${ano}"]:not(.border-purple-600)`);
                            if (firstParaleloTab) {
                                firstParaleloTab.click();
                            }
                        }
                    }
                }
            }
        });
    </script>

    {{-- Estilos para consistencia --}}
    <style>
        .border-b-3 {
            border-bottom-width: 3px; /* Más grueso para el nivel principal */
        }
        
        .tab-carrera, .tab-ano, .tab-paralelo {
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

        .tab-paralelo:hover {
            border-color: #d8b4fe; /* Púrpura más claro en hover */
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