@extends('layouts.app') {{-- Usa tu layout principal --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Periodos (Horarios)
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('admin.periodos.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Crear Nuevo Periodo
                        </a>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- Estructura Agrupada por Nombre de Periodo --}}
                    @if($periodos->isEmpty())
                         <p class="text-center text-gray-500">No hay periodos registrados.</p>
                    @else
                        @php
                            // Usaremos el nombre del periodo como clave de agrupación
                            $currentPeriodoNombre = ''; 
                        @endphp

                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                {{-- Encabezado Principal de la Tabla --}}
                                <thead class="bg-gray-100">
                                    <tr>
                                        {{-- Quitamos Nombre, irá en el encabezado de grupo --}}
                                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th> --}}
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hora Inicio</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hora Fin</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tolerancia</th>
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($periodos as $periodo)
                                        {{-- Mostrar encabezado de Nombre de Periodo si cambia --}}
                                        @if ($periodo->nombre !== $currentPeriodoNombre)
                                            @php $currentPeriodoNombre = $periodo->nombre; @endphp
                                            <tr class="bg-blue-100"> {{-- Fondo azul claro --}}
                                                {{-- Usamos colspan="4" porque ahora hay 4 columnas visibles --}}
                                                <td colspan="4" class="px-6 py-3 text-lg font-bold text-blue-800"> {{-- Letra azul oscuro --}}
                                                    Periodo: {{ $currentPeriodoNombre }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Fila del periodo (Blanco normal) --}}
                                        <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                            {{-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $periodo->nombre }}</td> --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($periodo->hora_inicio)->format('H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($periodo->hora_fin)->format('H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $periodo->tolerancia_ingreso_minutos }} min.</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.periodos.edit', $periodo) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                <form action="{{ route('admin.periodos.destroy', $periodo) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Eliminar este periodo? Podría afectar horarios existentes.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                     @endif

                     {{-- Paginación (si se implementa) --}}
                     {{-- 
                     @if ($periodos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-6">
                            {{ $periodos->links() }}
                        </div> 
                     @endif
                     --}}

                </div>
            </div>
        </div>
    </div>
@endsection