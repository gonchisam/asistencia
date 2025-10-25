@extends('layouts.app') {{-- Usa tu layout principal --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Cursos (Grupos)
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('admin.cursos.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Crear Nuevo Curso
                        </a>
                        {{-- Mantenemos el botón de importar --}}
                        <a href="{{ route('admin.inscripciones.importar.vista') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 ml-4">
                            Importar Inscripciones (Excel)
                        </a>
                    </div>

                    @include('admin.partials._session-messages')

                    {{-- Estructura Agrupada --}}
                    @if($cursos->isEmpty())
                        <p class="text-center text-gray-500">No hay cursos registrados.</p>
                    @else
                        @php
                            $currentCarrera = '';
                            $currentAno = '';
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        {{-- Columnas principales del curso --}}
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paralelo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gestión</th>
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($cursos as $curso)
                                        {{-- Mostrar encabezado de Carrera si cambia --}}
                                        {{-- Usamos $curso->carrera porque lo seleccionamos en el JOIN --}}
                                        @if ($curso->carrera !== $currentCarrera)
                                            @php $currentCarrera = $curso->carrera; $currentAno = ''; @endphp
                                            <tr class="bg-blue-50">
                                                <td colspan="4" class="px-6 py-3 text-lg font-bold text-blue-800">
                                                    Carrera: {{ $currentCarrera }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Mostrar encabezado de Año si cambia dentro de la misma Carrera --}}
                                        {{-- Usamos $curso->ano_cursado --}}
                                        @if ($curso->ano_cursado !== $currentAno)
                                            @php $currentAno = $curso->ano_cursado; @endphp
                                            <tr class="bg-gray-100">
                                                 <td colspan="4" class="px-6 py-2 text-md font-semibold text-gray-700">
                                                     Año: {{ $currentAno }}
                                                 </td>
                                             </tr>
                                        @endif

                                        {{-- Fila del curso --}}
                                        <tr>
                                            {{-- Usamos $curso->materia_nombre (el alias del select) --}}
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $curso->materia_nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $curso->paralelo }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $curso->gestion }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                {{-- Enlace a la página de gestión individual del curso --}}
                                                <a href="{{ route('admin.cursos.show', $curso) }}" class="text-blue-600 hover:text-blue-900">Gestionar</a>
                                                {{-- Enlace para editar los datos básicos del curso --}}
                                                <a href="{{ route('admin.cursos.edit', $curso) }}" class="text-indigo-600 hover:text-indigo-900 ml-2">Editar</a>
                                                {{-- Formulario para eliminar el curso --}}
                                                <form action="{{ route('admin.cursos.destroy', $curso) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Eliminar este curso? Se borrarán sus horarios e inscripciones.');">
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
                        {{ $cursos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection