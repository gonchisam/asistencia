@extends('layouts.app') {{-- Importante: extiende el layout principal --}}

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Registro de Asistencia</h2>

        {{-- Sección de búsqueda y filtro --}}
        <div class="mb-6 flex items-center space-x-4">
            <input type="text" id="search-uid" placeholder="Buscar por UID..." class="flex-1 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150">Buscar</button>
        </div>

        {{-- Mensajes de estado (si existen) --}}
        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        {{-- Tabla de Asistencia Reciente --}}
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Asistencias Recientes</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">UID</th>
                        <th class="py-3 px-6 text-left">Nombre</th>
                        <th class="py-3 px-6 text-left">Acción</th>
                        <th class="py-3 px-6 text-left">Modo</th>
                        <th class="py-3 px-6 text-left">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse ($asistencias as $asistencia)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $asistencia->uid }}</td>
                            <td class="py-3 px-6 text-left">{{ $asistencia->nombre }}</td>
                            <td class="py-3 px-6 text-left">{{ $asistencia->accion }}</td>
                            <td class="py-3 px-6 text-left">{{ $asistencia->modo }}</td>
                            <td class="py-3 px-6 text-left">{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay registros de asistencia recientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $asistencias->links() }}
        </div>
    </div>
@endsection