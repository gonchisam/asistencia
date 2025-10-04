@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Generación de Reportes</h2>

        <p class="text-gray-700 mb-6">Utiliza los filtros a continuación para generar reportes de asistencia en formato PDF o Excel.</p>

        <form action="{{ route('reportes.index') }}" method="GET" class="mb-8">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Filtros de Reporte</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha Fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="estudiante_id" class="block text-sm font-medium text-gray-700">Estudiante:</label>
                    <select name="estudiante_id" id="estudiante_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todos los estudiantes</option>
                        @foreach ($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" {{ (string)request('estudiante_id') === (string)$estudiante->id ? 'selected' : '' }}>
                                {{ $estudiante->nombre }} (UID: {{ $estudiante->uid }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="carrera" class="block text-sm font-medium text-gray-700">Carrera:</label>
                    <select name="carrera" id="carrera" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
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
                    <label for="anio_estudio" class="block text-sm font-medium text-gray-700">Año de Estudio:</label>
                    <select name="anio_estudio" id="anio_estudio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todos los años</option>
                        <option value="Primer Año" {{ request('anio_estudio') === 'Primer Año' ? 'selected' : '' }}>Primer Año</option>
                        <option value="Segundo Año" {{ request('anio_estudio') === 'Segundo Año' ? 'selected' : '' }}>Segundo Año</option>
                        <option value="Tercer Año" {{ request('anio_estudio') === 'Tercer Año' ? 'selected' : '' }}>Tercer Año</option>
                    </select>
                </div>
                <div>
                    <label for="ci" class="block text-sm font-medium text-gray-700">CI:</label>
                    <input type="text" name="ci" id="ci" value="{{ request('ci') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="accion" class="block text-sm font-medium text-gray-700">Acción:</label>
                    <select name="accion" id="accion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todas</option>
                        <option value="ENTRADA" {{ request('accion') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                        <option value="SALIDA" {{ request('accion') === 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    </select>
                </div>
                <div>
                    <label for="modo" class="block text-sm font-medium text-gray-700">Modo:</label>
                    <select name="modo" id="modo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todos</option>
                        <option value="ONLINE" {{ request('modo') === 'ONLINE' ? 'selected' : '' }}>ONLINE</option>
                        <option value="OFFLINE_SYNC" {{ request('modo') === 'OFFLINE_SYNC' ? 'selected' : '' }}>OFFLINE</option>
                    </select>
                </div>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    Aplicar Filtros
                </button>
                <a href="{{ route('reportes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-150 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m15.356-2A8.001 8.001 0 0115.418 15m0 0v5h.582"></path></svg>
                    Refrescar Filtros
                </a>
            </div>
        </form>

        <hr class="my-8">

        <h3 class="text-xl font-semibold text-gray-700 mb-4">Vista Previa del Reporte</h3>

        @if($asistencias->isEmpty())
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">No se encontraron asistencias con los filtros seleccionados.</span>
            </div>
        @else
            <div class="overflow-x-auto bg-white rounded-lg shadow-lg mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CI</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modo</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($asistencias as $asistencia)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $asistencia->estudiante->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->estudiante->uid }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->estudiante->ci }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->estudiante->carrera }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->estudiante->año }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $asistencia->accion === 'ENTRADA' ? 'text-green-600' : 'text-red-600' }} font-bold">{{ $asistencia->accion }}</td>
                            
                            {{-- CORRECCIÓN: Mapear el valor de 'modo' para una visualización amigable --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asistencia->modo }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex space-x-4">
                <form action="{{ route('reportes.pdf') }}" method="GET" class="inline-block" target="_blank">
                    @foreach(request()->all() as $key => $value)
                        @if(!is_null($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-150 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        Generar PDF
                    </button>
                </form>
                <form action="{{ route('reportes.excel') }}" method="GET" class="inline-block">
                    @foreach(request()->all() as $key => $value)
                        @if(!is_null($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        Generar Excel
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection