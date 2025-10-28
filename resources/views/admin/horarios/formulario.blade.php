{{-- resources/views/admin/horarios/formulario.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

            {{-- Encabezado --}}
            <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">
                <a href="{{ route('admin.cursos.index') }}"
                   class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                   title="Volver a Cursos">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>

                <h2 class="text-3xl font-extrabold text-gray-900">
                    <span class="text-blue-600">Generar</span> Horario PDF 📅
                </h2>
            </div>

            {{-- Mensajes --}}
            @include('admin.partials._session-messages')

            {{-- Instrucciones --}}
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                <p class="text-sm text-gray-700">
                    <strong>📋 Instrucciones:</strong> Selecciona la carrera, año, paralelo y gestión para generar el horario en formato PDF.
                </p>
            </div>

            {{-- Formulario --}}
            <form action="{{ route('admin.horarios.generar-pdf') }}" method="POST" target="_blank">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Carrera --}}
                    <div>
                        <label for="carrera" class="block text-sm font-medium text-gray-700 mb-2">
                            Carrera *
                        </label>
                        <select name="carrera" id="carrera" 
                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                                required>
                            <option value="">-- Seleccione --</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera }}">{{ $carrera }}</option>
                            @endforeach
                        </select>
                        @error('carrera')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Año --}}
                    <div>
                        <label for="ano_cursado" class="block text-sm font-medium text-gray-700 mb-2">
                            Año de Estudio *
                        </label>
                        <select name="ano_cursado" id="ano_cursado"
                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                                required>
                            <option value="">-- Seleccione --</option>
                            @foreach($anos as $ano)
                                <option value="{{ $ano }}">{{ $ano }}</option>
                            @endforeach
                        </select>
                        @error('ano_cursado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Paralelo --}}
                    <div>
                        <label for="paralelo" class="block text-sm font-medium text-gray-700 mb-2">
                            Paralelo *
                        </label>
                        <select name="paralelo" id="paralelo"
                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                                required>
                            <option value="">-- Seleccione --</option>
                            @foreach($paralelos as $paralelo)
                                <option value="{{ $paralelo }}">{{ $paralelo }}</option>
                            @endforeach
                        </select>
                        @error('paralelo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gestión --}}
                    <div>
                        <label for="gestion" class="block text-sm font-medium text-gray-700 mb-2">
                            Gestión *
                        </label>
                        <input type="text" name="gestion" id="gestion"
                               value="{{ old('gestion', date('Y')) }}"
                               class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                               placeholder="2025"
                               required>
                        @error('gestion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-end mt-8 pt-6 border-t space-x-4">
                    <a href="{{ route('admin.cursos.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-wider hover:bg-gray-300 transition">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700 transition shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Generar PDF
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection