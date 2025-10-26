@extends('layouts.app')

@section('header')
    {{-- Título ya está correcto, pero se puede mejorar el contraste si se mantiene el x-app-layout --}}
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Periodos (Horarios) ⏱️
    </h2>
@endsection

@section('content')
    <div class="py-12">
        {{-- Contenedor principal: Cambiado para usar el estilo moderno (shadow-2xl, rounded-xl) --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">
                <div class="text-gray-900">
                    
                    {{-- 1. Encabezado y Botón de Creación --}}
                    <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-200">
                        
                        {{-- Título Destacado --}}
                        <h1 class="text-3xl font-extrabold text-gray-900">
                            <span class="text-blue-600">Gestión de</span> Periodos
                        </h1>

                        {{-- Botón de Crear Nuevo Periodo: Estilo azul moderno --}}
                        <a href="{{ route('admin.periodos.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Crear Nuevo Periodo
                        </a>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- 2. Estructura de Pestañas por Periodo --}}
                    @if($periodos->isEmpty())
                        <div class="p-10 text-center bg-gray-50 border border-gray-200 rounded-lg">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="mt-2 text-xl font-medium text-gray-900">No hay Periodos registrados</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Empieza creando un nuevo periodo de horario para organizar las clases.
                            </p>
                        </div>
                    @else
                        @php
                            $periodosPorNombre = [];
                            foreach ($periodos as $periodo) {
                                $nombre = $periodo->nombre ?: 'Sin nombre';
                                if (!isset($periodosPorNombre[$nombre])) {
                                    $periodosPorNombre[$nombre] = [];
                                }
                                $periodosPorNombre[$nombre][] = $periodo;
                            }
                            // Ordenar alfabéticamente por nombre
                            ksort($periodosPorNombre);
                            $firstPeriodo = array_key_first($periodosPorNombre);
                        @endphp

                        {{-- Pestañas de Periodo --}}
                        <div class="mb-6">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                                    @foreach($periodosPorNombre as $nombre => $periodosLista)
                                        <a href="#periodo-{{ Str::slug($nombre) }}" 
                                           class="tab-periodo whitespace-nowrap py-3 px-1 border-b-3 font-semibold text-base transition duration-150 ease-in-out
                                                  {{ ($nombre === $firstPeriodo) ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                           data-periodo="{{ Str::slug($nombre) }}">
                                            {{ $nombre }} ({{ count($periodosLista) }})
                                        </a>
                                    @endforeach
                                </nav>
                            </div>
                        </div>

                        {{-- Contenido de las Pestañas --}}
                        @foreach($periodosPorNombre as $nombre => $periodosLista)
                            <div id="periodo-{{ Str::slug($nombre) }}" class="tab-content {{ ($nombre === $firstPeriodo) ? '' : 'hidden' }}">
                                
                                @if(count($periodosLista) > 0)
                                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-blue-50/50"> {{-- Pequeño toque de azul al encabezado --}}
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Hora Inicio</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Hora Fin</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tolerancia</th>
                                                    <th scope="col" class="relative px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                {{-- Ordenar la lista por hora de inicio --}}
                                                @php
                                                    $sortedPeriodos = collect($periodosLista)->sortBy('hora_inicio');
                                                @endphp

                                                @foreach ($sortedPeriodos as $periodo)
                                                    <tr class="hover:bg-blue-50/70 transition duration-150 ease-in-out">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($periodo->hora_inicio)->format('H:i') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($periodo->hora_fin)->format('H:i') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $periodo->tolerancia_ingreso_minutos }} min.</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                            <a href="{{ route('admin.periodos.edit', $periodo) }}" class="text-blue-600 hover:text-blue-800 transition duration-150">Editar</a>
                                                            <form action="{{ route('admin.periodos.destroy', $periodo) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ ¿Estás seguro de ELIMINAR este periodo? Podría afectar la programación de horarios existentes.');">
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
                                    <p class="text-center text-gray-500 py-4">No hay periodos registrados para este grupo.</p>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Scripts y Estilos (Se mantienen para la funcionalidad de pestañas) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de pestañas de periodo
            const periodoTabs = document.querySelectorAll('.tab-periodo');
            periodoTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Ocultar todos los contenidos de periodo
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Quitar clase activa de todas las pestañas
                    periodoTabs.forEach(t => {
                        t.classList.remove('border-blue-600', 'text-blue-700');
                        t.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    // Mostrar contenido de la pestaña seleccionada
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.remove('hidden');
                    
                    // Marcar pestaña como activa
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-blue-600', 'text-blue-700');
                });
            });
        });
    </script>

    <style>
        .tab-periodo {
            transition: all 0.2s ease-in-out;
            border-bottom-width: 3px; /* Aumentar el grosor del borde de la pestaña */
        }
        
        .tab-periodo:hover {
            border-color: #93c5fd; /* Color más claro en hover */
        }
        
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
            background-color: #a0aec0; /* Un gris un poco más oscuro */
            border-radius: 3px;
        }
    </style>
@endsection