@extends('layouts.app') {{-- Usa tu layout principal --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Aulas
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-end mb-6">
                        <a href="{{ route('admin.aulas.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Crear Nueva Aula
                        </a>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- Estructura Agrupada por Ubicación --}}
                    @if($aulas->isEmpty())
                        <p class="text-center text-gray-500">No hay aulas registradas.</p>
                    @else
                        @php
                            $currentUbicacion = null; // Usamos null para manejar el primer caso y aulas sin ubicación
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                        {{-- Quitamos Ubicación de aquí, irá en el encabezado --}}
                                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th> --}}
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($aulas as $aula)
                                        {{-- Mostrar encabezado de Ubicación si cambia --}}
                                        @if ($aula->ubicacion !== $currentUbicacion)
                                            @php $currentUbicacion = $aula->ubicacion; @endphp
                                            <tr class="bg-blue-50">
                                                {{-- Usamos colspan="3" porque ahora hay 3 columnas visibles --}}
                                                <td colspan="3" class="px-6 py-3 text-lg font-bold text-blue-800">
                                                    Ubicación: {{ $currentUbicacion ?: 'Sin especificar' }} {{-- Muestra 'Sin especificar' si es null o vacío --}}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Fila del aula --}}
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $aula->nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $aula->codigo }}</td>
                                            {{-- <td class="px-6 py-4 whitespace-nowrap">{{ $aula->ubicacion }}</td> --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.aulas.edit', $aula) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                <form action="{{ route('admin.aulas.destroy', $aula) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta aula?');">
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

                    {{-- Paginación --}}
                    <div class="mt-6">
                        {{ $aulas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection