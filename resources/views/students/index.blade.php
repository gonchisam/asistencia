@extends('layouts.app')

@section('content')
    {{-- Contenedor principal con el mismo estilo moderno del login --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-7xl mx-auto">
        
        {{-- Encabezado con título y botón flotante --}}
        <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-200">
            @can('manage-students')
            <h2 class="text-3xl font-extrabold text-gray-900">
                <span class="text-blue-600">Gestión de Estudiantes</span>
            </h2>
            
            {{-- Botón flotante de agregar --}}
            <a href="{{ route('students.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105"
               title="Registrar Nuevo Estudiante">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </a>
            @else
            <h2 class="text-3xl font-extrabold text-gray-900">
                <span class="text-blue-600">Lista de Estudiantes</span>
            </h2>
            @endcan
        </div>

        {{-- Mensajes de estado --}}
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

        @can('manage-students')
        <div class="mb-8 flex flex-wrap gap-4">
            {{-- Botón principal de registro --}}
            <a href="{{ route('students.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Registrar Nuevo Estudiante') }}
            </a>
            
            {{-- Botón Importar Estudiantes --}}
            <a href="{{ route('admin.estudiantes.importar.vista') }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                {{ __('Importar Estudiantes (Excel)') }}
            </a>
            
            {{-- Botón Asignar UID --}}
            <a href="{{ route('admin.estudiantes.asignar-uid.vista') }}" 
               class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1M4 4h16v4H4V4z"></path>
                </svg>
                {{ __('Asignar Tarjetas (UID)') }}
            </a>
            </div>
        @endcan
        {{-- Filtros --}}
        <div class="mb-8 bg-gray-50 rounded-xl p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Filtrar Estudiantes</h3>
            <form action="{{ route('students.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Carrera --}}
                <div class="flex flex-col">
                    <x-input-label for="carrera" :value="__('Carrera')" class="text-gray-700 font-semibold mb-2" />
                    <select name="carrera" id="carrera" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out text-sm">
                        <option value="">Todas las carreras</option>
                        <option value="Contabilidad" {{ request('carrera') == 'Contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                        <option value="Secretariado" {{ request('carrera') == 'Secretariado' ? 'selected' : '' }}>Secretariado</option>
                        <option value="Mercadotecnia" {{ request('carrera') == 'Mercadotecnia' ? 'selected' : '' }}>Mercadotecnia</option>
                        <option value="Sistemas" {{ request('carrera') == 'Sistemas' ? 'selected' : '' }}>Sistemas</option>
                    </select>
                </div>
                
                {{-- Año --}}
                <div class="flex flex-col">
                    <x-input-label for="año" :value="__('Año')" class="text-gray-700 font-semibold mb-2" />
                    <select name="año" id="año" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out text-sm">
                        <option value="">Todos los años</option>
                        <option value="Primer Año" {{ request('año') == 'Primer Año' ? 'selected' : '' }}>Primer Año</option>
                        <option value="Segundo Año" {{ request('año') == 'Segundo Año' ? 'selected' : '' }}>Segundo Año</option>
                        <option value="Tercer Año" {{ request('año') == 'Tercer Año' ? 'selected' : '' }}>Tercer Año</option>
                    </select>
                </div>
                
                {{-- Estado --}}
                <div class="flex flex-col">
                    <x-input-label for="estado" :value="__('Estado')" class="text-gray-700 font-semibold mb-2" />
                    <select name="estado" id="estado" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out text-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

                {{-- Botones de acción --}}
                <div class="flex flex-col justify-end space-y-2">
                    <div class="flex space-x-2">
                        <x-primary-button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full">
                            {{ __('Filtrar') }}
                        </x-primary-button>
                        
                        <a href="{{ route('students.index') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 inline-flex items-center justify-center w-full">
                            {{ __('Limpiar') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabla de estudiantes --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                    <tr class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                        {{-- Columna Nombre --}}
                        <th class="py-4 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'nombre', 'direction' => request('sort') == 'nombre' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-blue-600 transition-colors duration-150 ease-in-out group">
                                <span>Nombre</span>
                                @if(request('sort') == 'nombre')
                                    <span class="text-blue-600">{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @else
                                    <span class="text-gray-300 group-hover:text-blue-400">↕</span>
                                @endif
                            </a>
                        </th>
                        
                        {{-- Columna UID RFID --}}
                        <th class="py-4 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'uid', 'direction' => request('sort') == 'uid' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-blue-600 transition-colors duration-150 ease-in-out group">
                                <span>UID RFID</span>
                                @if(request('sort') == 'uid')
                                    <span class="text-blue-600">{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @else
                                    <span class="text-gray-300 group-hover:text-blue-400">↕</span>
                                @endif
                            </a>
                        </th>
                        
                        {{-- Columna Carrera --}}
                        <th class="py-4 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'carrera', 'direction' => request('sort') == 'carrera' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-blue-600 transition-colors duration-150 ease-in-out group">
                                <span>Carrera</span>
                                @if(request('sort') == 'carrera')
                                    <span class="text-blue-600">{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @else
                                    <span class="text-gray-300 group-hover:text-blue-400">↕</span>
                                @endif
                            </a>
                        </th>
                        
                        {{-- Columna Año --}}
                        <th class="py-4 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'año', 'direction' => request('sort') == 'año' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-blue-600 transition-colors duration-150 ease-in-out group">
                                <span>Año</span>
                                @if(request('sort') == 'año')
                                    <span class="text-blue-600">{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @else
                                    <span class="text-gray-300 group-hover:text-blue-400">↕</span>
                                @endif
                            </a>
                        </th>
                        
                        {{-- Columna Estado --}}
                        <th class="py-4 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'estado', 'direction' => request('sort') == 'estado' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-blue-600 transition-colors duration-150 ease-in-out group">
                                <span>Estado</span>
                                @if(request('sort') == 'estado')
                                    <span class="text-blue-600">{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @else
                                    <span class="text-gray-300 group-hover:text-blue-400">↕</span>
                                @endif
                            </a>
                        </th>
                        
                        {{-- Columna Acciones (siempre visible) --}}
                        <th class="py-4 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse ($estudiantes as $student)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="py-4 px-6 text-left whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $student->nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}</span>
                            </td>
                            
                            <td class="py-4 px-6 text-left">
                                @if($student->uid)
                                    <span class="font-mono text-blue-600">{{ $student->uid }}</span>
                                @else
                                    <span class="font-mono text-xs text-red-500 italic">UID NO ASIGNADO</span>
                                @endif
                            </td>
                            
                            <td class="py-4 px-6 text-left">{{ $student->carrera }}</td>
                            <td class="py-4 px-6 text-left">{{ $student->año }}</td>
                            <td class="py-4 px-6 text-left">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    {{ $student->estado == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $student->estado == 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            
                            {{-- Celda de Acciones --}}
                            <td class="py-4 px-6 text-center">
                                @can('manage-students')
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="{{ route('students.edit', $student->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out font-medium">
                                        Editar
                                    </a>
                                    
                                    @if ($student->estado == 1)
                                        {{-- Botón Dar de Baja --}}
                                        <form action="{{ route('students.destroy', $student->id) }}" method="POST" 
                                              onsubmit="return confirm('¿Estás seguro de que quieres dar de baja a este estudiante? Esto cambiará su estado a inactivo.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800 transition duration-150 ease-in-out font-medium">
                                                Dar de Baja
                                            </button>
                                        </form>
                                        
                                        @if ($student->device_id)
                                            <form action="{{ route('students.unlinkDevice', $student->id) }}" method="POST" 
                                                  onsubmit="return confirm('¿Estás seguro de que deseas desvincular el dispositivo de este estudiante? Perderá el acceso en ese celular.');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" 
                                                        class="text-yellow-600 hover:text-yellow-800 transition duration-150 ease-in-out font-medium">
                                                    Eliminar ID Celular
                                                </button>
                                            </form>
                                        @endif
                                        
                                    @else
                                        {{-- Botón Reactivar --}}
                                        <form action="{{ route('students.restore', $student->id) }}" method="POST" 
                                              onsubmit="return confirm('¿Estás seguro de que quieres reactivar a este estudiante?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-800 transition duration-150 ease-in-out font-medium">
                                                Reactivar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                @else
                                <span class="text-gray-400 text-xs italic">N/A</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-lg text-gray-600">No hay estudiantes registrados.</p>
                                    <p class="text-sm text-gray-500 mt-2">Comienza registrando el primer estudiante.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow-sm p-4">
                {{ $estudiantes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection