@extends('layouts.app')

@section('content')
    {{-- Contenedor principal --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 w-full max-w-7xl mx-auto">
        
        {{-- Título principal --}}
        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">
            <span class="text-blue-600">Generación de Reportes</span>
        </h2>

        {{-- Formulario de filtros (SIN CAMBIOS RESPECTO A LA VERSIÓN ANTERIOR) --}}
        <form action="{{ route('reportes.index') }}" method="GET" class="mb-8">
            <div class="mb-8 p-6 bg-gray-50 rounded-xl border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Filtros de Reporte</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    {{-- Fecha Inicio --}}
                    <div>
                        <x-input-label for="fecha_inicio" :value="__('Fecha Inicio')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="block w-full p-3 border border-gray-300 rounded-lg"/>
                    </div>
                    
                    {{-- Fecha Fin --}}
                    <div>
                        <x-input-label for="fecha_fin" :value="__('Fecha Fin')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="block w-full p-3 border border-gray-300 rounded-lg"/>
                    </div>

                    {{-- CI --}}
                    <div>
                        <x-input-label for="ci" :value="__('CI Estudiante')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input type="text" name="ci" id="ci" value="{{ request('ci') }}" placeholder="Buscar por CI..." class="block w-full p-3 border border-gray-300 rounded-lg"/>
                    </div>
                    
                    {{-- Carrera (Dinámico) --}}
                    <div>
                        <x-input-label for="carrera" :value="__('Carrera')" class="text-gray-700 font-semibold mb-2" />
                        <select name="carrera" id="carrera" class="block w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Todas las carreras</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera }}" {{ request('carrera') === $carrera ? 'selected' : '' }}>
                                    {{ $carrera }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Año Estudio --}}
                    <div>
                        <x-input-label for="año" :value="__('Año de Estudio')" class="text-gray-700 font-semibold mb-2" />
                        <select name="año" id="año" class="block w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Todos los años</option>
                             @foreach ($años as $año) 
                                <option value="{{ $año }}" {{ request('año') === $año ? 'selected' : '' }}>
                                    {{ $año }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                     {{-- Paralelo (Nuevo y Dinámico) --}}
                     <div>
                        <x-input-label for="paralelo" :value="__('Paralelo')" class="text-gray-700 font-semibold mb-2" />
                        <select name="paralelo" id="paralelo" class="block w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Todos los paralelos</option>
                            @foreach ($paralelos as $paralelo)
                                <option value="{{ $paralelo }}" {{ request('paralelo') === $paralelo ? 'selected' : '' }}>
                                    {{ $paralelo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Materia --}}
                    <div>
                        <x-input-label for="materia_id" :value="__('Materia')" class="text-gray-700 font-semibold mb-2" />
                        <select name="materia_id" id="materia_id" class="block w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Todas las materias</option>
                            @foreach ($materias as $materia)
                                <option value="{{ $materia->id }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }}>
                                    {{ $materia->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Modo --}}
                    <div>
                        <x-input-label for="modo" :value="__('Modo Asistencia')" class="text-gray-700 font-semibold mb-2" />
                        <select name="modo" id="modo" class="block w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <option value="ONLINE" {{ request('modo') === 'ONLINE' ? 'selected' : '' }}>ONLINE</option>
                            <option value="OFFLINE" {{ request('modo') === 'OFFLINE' ? 'selected' : '' }}>OFFLINE</option>
                            <option value="MOVIL" {{ request('modo') === 'MOVIL' ? 'selected' : '' }}>MOVIL</option>
                        </select>
                    </div>

                     {{-- Estado Llegada --}}
                    <div>
                        <x-input-label for="estado_llegada" :value="__('Estado Llegada')" class="text-gray-700 font-semibold mb-2" />
                        <select name="estado_llegada" id="estado_llegada" class="block w-full p-3 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <option value="a_tiempo" {{ request('estado_llegada') === 'a_tiempo' ? 'selected' : '' }}>A tiempo</option>
                            <option value="tarde" {{ request('estado_llegada') === 'tarde' ? 'selected' : '' }}>Tarde</option>
                            <option value="falta" {{ request('estado_llegada') === 'falta' ? 'selected' : '' }}>Falta</option>
                        </select>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="flex space-x-4">
                    <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        {{ __('Aplicar Filtros') }}
                    </x-primary-button>
                    
                    <a href="{{ route('reportes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m15.356-2A8.001 8.001 0 0115.418 15m0 0v5h.582"></path></svg>
                        {{ __('Limpiar Filtros') }}
                    </a>
                </div>
            </div>
        </form>

        <hr class="my-8 border-gray-200">

        {{-- Vista previa del reporte --}}
        <h3 class="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Vista Previa del Reporte</h3>

        @if($asistencias->isEmpty())
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-300 rounded-lg text-yellow-700">
                No se encontraron asistencias con los filtros seleccionados.
            </div>
        @else
            {{-- Tabla de vista previa (ACTUALIZADA con Paralelo) --}}
            <div class="mb-8">
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-4 px-6 text-left font-semibold">Fecha y Hora</th>
                                <th class="py-4 px-6 text-left font-semibold">CI</th>
                                <th class="py-4 px-6 text-left font-semibold">Nombre Completo</th>
                                <th class="py-4 px-6 text-left font-semibold">Carrera</th>
                                <th class="py-4 px-6 text-left font-semibold">Año</th>
                                {{-- --- INICIO: NUEVA COLUMNA --- --}}
                                <th class="py-4 px-6 text-left font-semibold">Paralelo</th> 
                                {{-- --- FIN: NUEVA COLUMNA --- --}}
                                <th class="py-4 px-6 text-left font-semibold">Materia</th>
                                <th class="py-4 px-6 text-left font-semibold">Modo</th>
                                <th class="py-4 px-6 text-left font-semibold">Estado Llegada</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @foreach($asistencias as $asistencia)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-4 px-6 text-left whitespace-nowrap">{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->ci ?? 'N/A' }}</td>
                                    <td class="py-4 px-6 text-left font-medium">{{ $asistencia->nombre }}</td> 
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->carrera ?? 'N/A' }}</td>
                                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->año ?? 'N/A' }}</td>
                                    {{-- --- INICIO: NUEVA CELDA --- --}}
                                    <td class="py-4 px-6 text-center">{{ $asistencia->curso->paralelo ?? 'N/A' }}</td> {{-- Asume relación asistencia->curso --}}
                                    {{-- --- FIN: NUEVA CELDA --- --}}
                                    <td class="py-4 px-6 text-left">{{ $asistencia->curso->materia->nombre ?? 'N/A' }}</td>
                                    <td class="py-4 px-6 text-left">
                                         <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($asistencia->modo == 'MOVIL') bg-blue-100 text-blue-800
                                            @elseif($asistencia->modo == 'ONLINE') bg-green-100 text-green-800
                                            @elseif($asistencia->modo == 'OFFLINE') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $asistencia->modo }}
                                        </span>
                                    </td>
                                     <td class="py-4 px-6 text-left">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium capitalize
                                            @if($asistencia->estado_llegada == 'a_tiempo') bg-green-100 text-green-800
                                            @elseif($asistencia->estado_llegada == 'tarde') bg-yellow-100 text-yellow-800
                                            @elseif($asistencia->estado_llegada == 'falta') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $asistencia->estado_llegada ? str_replace('_', ' ', $asistencia->estado_llegada) : 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(isset($isFiltered) && !$isFiltered && $asistencias instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-8 mb-8">
                    {{ $asistencias->links() }}
                </div>
            @endif

            {{-- Botones de generación de reportes (SIN CAMBIOS) --}}
            <div class="flex space-x-4">
                {{-- Botón PDF --}}
                <form action="{{ route('reportes.pdf') }}" method="GET" class="inline-block" target="_blank">
                    @foreach(request()->except('page') as $key => $value)
                        @if(!is_null($value) && $value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        {{ __('Generar PDF') }}
                    </button>
                </form>
                {{-- Botón Excel --}}
                <form action="{{ route('reportes.excel') }}" method="GET" class="inline-block">
                     @foreach(request()->except('page') as $key => $value)
                        @if(!is_null($value) && $value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        {{ __('Generar Excel') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection