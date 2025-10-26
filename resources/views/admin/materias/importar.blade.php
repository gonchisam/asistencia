@extends('layouts.app')

@section('content')

    {{-- Contenedor principal --}}
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8"> 
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

                {{-- Encabezado con flecha y título unificado --}}
                <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">

                    {{-- Flecha "Atrás" --}}
                    <a href="{{ route('admin.materias.index') }}"
                        class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                        title="Volver a la Gestión de Materias">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>

                    {{-- Título Principal --}}
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        <span class="text-blue-600">Importar</span> Materias (Excel) 📚
                    </h2>
                </div>

                {{-- Mensajes de Sesión (General) --}}
                @include('admin.partials._session-messages')

                {{-- Mensajes de Error de Importación (Específicos) --}}
                @if (session('import_errors'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-lg shadow-sm text-sm"
                        role="alert">
                        <strong class="font-extrabold text-lg flex items-center mb-1">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            ¡Se encontraron errores!
                        </strong>
                        <p class="block sm:inline font-medium">{{ session('warning') }}</p>

                        <ul class="list-disc list-inside mt-3 space-y-1 ml-4">
                            @foreach (session('import_errors') as $error)
                                {{-- El error puede ser un string (de nuestro controlador) o un objeto Failure (de validación) --}}
                                <li>{!! is_string($error) ? $error : $error->errors()[0] !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                {{-- Sección de Instrucciones --}}
                <div class="mb-8 p-6 bg-gray-50 border-l-4 border-blue-500 rounded-lg text-sm text-gray-700 shadow-md">
                    <h4 class="font-extrabold text-xl text-blue-700 mb-3 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Guía de Formato de Archivo
                    </h4>
                    
                    <p class="mb-3">El archivo debe ser Excel (.xlsx, .xls, .csv) y la primera fila debe contener las siguientes cabeceras:</p>

                    <div class="space-y-3">
                        <div class="font-semibold text-gray-800">
                            Columnas Requeridas:
                            <div class="grid grid-cols-3 gap-2 bg-gray-100 p-3 rounded-lg border border-gray-200 font-mono text-xs mt-2 shadow-inner">
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">nombre</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">carrera</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">ano_cursado</span>
                            </div>
                        </div>
                    </div>

                    <p class="font-extrabold text-red-600 mt-5 mb-2">⚠ Consideraciones Importantes:</p>
                    <ul class="list-disc list-inside space-y-1 pl-4 text-xs">
                        <li>Las cabeceras distinguen minúsculas/mayúsculas.</li>
                        <li>Las **carreras** válidas son: 'Sistemas', 'Contabilidad', 'Secretariado', 'Mercadotecnia'.</li>
                        <li>Los **años** válidos son: 'Primer Año', 'Segundo Año', 'Tercer Año'.</li>
                        <li>Materias **duplicadas** (mismo nombre, carrera y año) serán **omitidas**.</li>
                    </ul>
                </div>

                {{-- Formulario de Subida --}}
                <form action="{{ route('admin.materias.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="archivo_excel" class="block font-semibold text-lg text-gray-800 mb-2">
                                📂 Seleccionar Archivo
                            </label>
                            
                            <input id="archivo_excel"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                                       focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold 
                                       file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition duration-150"
                                type="file"
                                name="archivo_excel"
                                accept=".xlsx, .xls, .csv"
                                required>

                            @error('archivo_excel')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.materias.index') }}" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs
                                text-gray-700 uppercase tracking-wider hover:bg-gray-300 active:bg-gray-400 focus:outline-none
                                focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm mr-4">
                            Cancelar
                        </a>
                        
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition ease-in-out duration-150 shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Importar Materias
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection