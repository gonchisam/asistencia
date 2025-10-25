@extends('layouts.app') {{-- Asegúrate que coincida con tu layout --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Materias
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('admin.materias.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Crear Nueva Materia
                        </a>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- Estructura Agrupada --}}
                    @if($materias->isEmpty())
                        <p class="text-center text-gray-500">No hay materias registradas.</p>
                    @else
                        @php
                            $currentCarrera = '';
                            $currentAno = '';
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        {{-- Ya no necesitamos mostrar carrera/año en cada fila --}}
                                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th> --}}
                                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th> --}}
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($materias as $materia)
                                        {{-- Mostrar encabezado de Carrera si cambia --}}
                                        @if ($materia->carrera !== $currentCarrera)
                                            @php $currentCarrera = $materia->carrera; $currentAno = ''; @endphp
                                            <tr class="bg-blue-50">
                                                <td colspan="2" class="px-6 py-3 text-lg font-bold text-blue-800">
                                                    Carrera: {{ $currentCarrera }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Mostrar encabezado de Año si cambia dentro de la misma Carrera --}}
                                        @if ($materia->ano_cursado !== $currentAno)
                                            @php $currentAno = $materia->ano_cursado; @endphp
                                            <tr class="bg-gray-100">
                                                 <td colspan="2" class="px-6 py-2 text-md font-semibold text-gray-700">
                                                     Año: {{ $currentAno }}
                                                 </td>
                                             </tr>
                                        @endif

                                        {{-- Fila de la materia --}}
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $materia->nombre }}</td>
                                            {{-- <td class="px-6 py-4 whitespace-nowrap">{{ $materia->carrera }}</td> --}}
                                            {{-- <td class="px-6 py-4 whitespace-nowrap">{{ $materia->ano_cursado }}</td> --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('admin.materias.edit', $materia) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                <form action="{{ route('admin.materias.destroy', $materia) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta materia?');">
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
                        {{ $materias->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection