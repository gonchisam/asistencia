@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Gestión de Estudiantes</h2>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <a href="{{ route('students.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                Registrar Nuevo Estudiante
            </a>

            <div class="w-full md:w-auto">
                <form action="{{ route('students.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                    <div>
                        <select name="carrera" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todas las carreras</option>
                            <option value="Contabilidad" {{ request('carrera') == 'Contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                            <option value="Secretariado" {{ request('carrera') == 'Secretariado' ? 'selected' : '' }}>Secretariado</option>
                            <option value="Mercadotecnia" {{ request('carrera') == 'Mercadotecnia' ? 'selected' : '' }}>Mercadotecnia</option>
                            <option value="Sistemas" {{ request('carrera') == 'Sistemas' ? 'selected' : '' }}>Sistemas</option>
                        </select>
                    </div>
                    
                    <div>
                        <select name="año" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los años</option>
                            <option value="Primer Año" {{ request('año') == 'Primer Año' ? 'selected' : '' }}>Primer Año</option>
                            <option value="Segundo Año" {{ request('año') == 'Segundo Año' ? 'selected' : '' }}>Segundo Año</option>
                            <option value="Tercer Año" {{ request('año') == 'Tercer Año' ? 'selected' : '' }}>Tercer Año</option>
                        </select>
                    </div>
                    
                    <div>
                        <select name="estado" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Filtrar
                        </button>
                        <a href="{{ route('students.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'nombre', 'direction' => request('sort') == 'nombre' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors">
                                <span>Nombre</span>
                                @if(request('sort') == 'nombre')
                                    <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'uid', 'direction' => request('sort') == 'uid' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors">
                                <span>UID RFID</span>
                                @if(request('sort') == 'uid')
                                    <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'carrera', 'direction' => request('sort') == 'carrera' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors">
                                <span>Carrera</span>
                                @if(request('sort') == 'carrera')
                                    <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'año', 'direction' => request('sort') == 'año' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors">
                                <span>Año</span>
                                @if(request('sort') == 'año')
                                    <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 px-6 text-left">
                            <a href="{{ route('students.index', ['sort' => 'estado', 'direction' => request('sort') == 'estado' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                               class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors">
                                <span>Estado</span>
                                @if(request('sort') == 'estado')
                                    <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse ($estudiantes as $student)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $student->nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}</td>
                            <td class="py-3 px-6 text-left">{{ $student->uid }}</td>
                            <td class="py-3 px-6 text-left">{{ $student->carrera }}</td>
                            <td class="py-3 px-6 text-left">{{ $student->año }}</td>
                            <td class="py-3 px-6 text-left">
                                <span class="px-2 py-1 font-semibold leading-tight rounded-full {{ $student->estado == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $student->estado == 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <a href="{{ route('students.edit', $student->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Editar</a>
                                @if ($student->estado == 1)
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres dar de baja a este estudiante? Esto cambiará su estado a inactivo.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Dar de Baja</button>
                                </form>
                                @else
                                <form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres reactivar a este estudiante?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-green-600 hover:text-green-900">Reactivar</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay estudiantes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $estudiantes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection