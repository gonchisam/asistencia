@extends('layouts.app')

@section('content')
    {{-- Contenedor principal con el mismo estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-7xl mx-auto">
        
        {{-- Título principal con el mismo estilo --}}
        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">
            <span class="text-blue-600">Generación de Reportes</span>
        </h2>

        <p class="text-gray-700 mb-8 text-center text-lg">Utiliza los filtros a continuación para generar reportes de asistencia en formato PDF o Excel.</p>

        {{-- Formulario de filtros con el estilo del login --}}
        <form action="{{ route('reportes.index') }}" method="GET" class="mb-8">
            <h3 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Filtros de Reporte</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div>
                    <x-input-label for="fecha_inicio" :value="__('Fecha Inicio')" class="text-gray-700 font-semibold mb-2" />
                    <x-text-input 
                        type="date" 
                        name="fecha_inicio" 
                        id="fecha_inicio" 
                        value="{{ request('fecha_inicio') }}" 
                        class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                    />
                </div>
                
                <div>
                    <x-input-label for="fecha_fin" :value="__('Fecha Fin')" class="text-gray-700 font-semibold mb-2" />
                    <x-text-input 
                        type="date" 
                        name="fecha_fin" 
                        id="fecha_fin" 
                        value="{{ request('fecha_fin') }}" 
                        class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                    />
                </div>
                
                <div>
                    <x-input-label for="estudiante_id" :value="__('Estudiante')" class="text-gray-700 font-semibold mb-2" />
                    <select name="estudiante_id" id="estudiante_id" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                        <option value="">Todos los estudiantes</option>
                        @foreach ($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" {{ (string)request('estudiante_id') === (string)$estudiante->id ? 'selected' : '' }}>
                                {{ $estudiante->nombre }} (UID: {{ $estudiante->uid }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <x-input-label for="carrera" :value="__('Carrera')" class="text-gray-700 font-semibold mb-2" />
                    <select name="carrera" id="carrera" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                        <option value="">Todas las carreras</option>
                        @php
                            $carreras = ['Contabilidad', 'Secretariado', 'Sistemas', 'Mercadotecnia'];
                        @endphp
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera }}" {{ request('carrera') === $carrera ? 'selected' : '' }}>
                                {{ $carrera }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <x-input-label for="anio_estudio" :value="__('Año de Estudio')" class="text-gray-700 font-semibold mb-2" />
                    <select name="anio_estudio" id="anio_estudio" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                        <option value="">Todos los años</option>
                        <option value="Primer Año" {{ request('anio_estudio') === 'Primer Año' ? 'selected' : '' }}>Primer Año</option>
                        <option value="Segundo Año" {{ request('anio_estudio') === 'Segundo Año' ? 'selected' : '' }}>Segundo Año</option>
                        <option value="Tercer Año" {{ request('anio_estudio') === 'Tercer Año' ? 'selected' : '' }}>Tercer Año</option>
                    </select>
                </div>
                
                <div>
                    <x-input-label for="ci" :value="__('CI')" class="text-gray-700 font-semibold mb-2" />
                    <x-text-input 
                        type="text" 
                        name="ci" 
                        id="ci" 
                        value="{{ request('ci') }}" 
                        class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                    />
                </div>
                
                <div>
                    <x-input-label for="accion" :value="__('Acción')" class="text-gray-700 font-semibold mb-2" />
                    <select name="accion" id="accion" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                        <option value="">Todas</option>
                        <option value="ENTRADA" {{ request('accion') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                        <option value="SALIDA" {{ request('accion') === 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    </select>
                </div>
                
                <div>
                    <x-input-label for="modo" :value="__('Modo')" class="text-gray-700 font-semibold mb-2" />
                    <select name="modo" id="modo" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                        <option value="">Todos</option>
                        <option value="ONLINE" {{ request('modo') === 'ONLINE' ? 'selected' : '' }}>ONLINE</option>
                        <option value="OFFLINE_SYNC" {{ request('modo') === 'OFFLINE_SYNC' ? 'selected' : '' }}>OFFLINE</option>
                    </select>
                </div>
            </div>

            {{-- Botones de acción con el estilo del login --}}
            <div class="flex space-x-4">
                <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                    {{ __('Aplicar Filtros') }}
                </x-primary-button>
                
                <a href="{{ route('reportes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m15.356-2A8.001 8.001 0 0115.418 15m0 0v5h.582"></path>
                    </svg>
                    {{ __('Refrescar Filtros') }}
                </a>
            </div>
        </form>

        <hr class="my-8 border-gray-200">

        {{-- Vista previa del reporte --}}
        <h3 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Vista Previa del Reporte</h3>

        @if($asistencias->isEmpty())
            {{-- Mensaje de estado con el estilo del login --}}
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-300 rounded-lg text-yellow-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Atención!</strong>
                <span class="block sm:inline">No se encontraron asistencias con los filtros seleccionados.</span>
            </div>
        @else
            {{-- Tabla de vista previa con el estilo del login --}}
            <div class="mb-8">
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-4 px-6 text-left font-semibold">Estudiante</th>
                                <th class="py-4 px-6 text-left font-semibold">UID</th>
                                <th class="py-4 px-6 text-left font-semibold">CI</th>
                                <th class="py-4 px-6 text-left font-semibold">Carrera</th>
                                <th class="py-4 px-6 text-left font-semibold">Año</th>
                                <th class="py-4 px-6 text-left font-semibold">Fecha</th>
                                <th class="py-4 px-6 text-left font-semibold">Acción</th>
                                <th class="py-4 px-6 text-left font-semibold">Modo</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @foreach($asistencias as $asistencia)
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <td class="py-4 px-6 text-left whitespace-nowrap">
                                        <span class="font-medium">{{ $asistencia->estudiante->nombre }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->uid }}</td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->ci }}</td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->carrera }}</td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->año }}</td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="py-4 px-6 text-left">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                            {{ $asistencia->accion === 'ENTRADA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $asistencia->accion }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->modo }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Botones de generación de reportes --}}
            <div class="flex space-x-4">
                <form action="{{ route('reportes.pdf') }}" method="GET" class="inline-block" target="_blank">
                    @foreach(request()->all() as $key => $value)
                        @if(!is_null($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                        {{ __('Generar PDF') }}
                    </button>
                </form>
                
                <form action="{{ route('reportes.excel') }}" method="GET" class="inline-block">
                    @foreach(request()->all() as $key => $value)
                        @if(!is_null($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                        {{ __('Generar Excel') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection