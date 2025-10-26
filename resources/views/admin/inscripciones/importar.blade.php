@extends('layouts.app') {{-- Usa tu layout principal --}}

@section('content') {{-- Contenido principal --}}

    {{-- Contenedor principal con estilo moderno --}}
    <div class="py-12">
        {{-- Mantenemos el contenedor un poco más estrecho (max-w-3xl) --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Aplica el mismo estilo de tarjeta: rounded-xl, shadow-2xl con hover, p-8 --}}
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

                {{-- Encabezado con flecha y título unificado --}}
                <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">

                    {{-- Flecha "Atrás" (Back Arrow). Asumo que se vuelve a la lista de cursos o inscripciones. --}}
                    <a href="{{ route('admin.cursos.index') }}" {{-- Ajusta esta ruta si necesitas volver a otro lugar --}}
                        class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                        title="Volver a la Gestión">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>

                    {{-- Título Principal con color azul destacado --}}
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        <span class="text-blue-600">Importar</span> Inscripciones (Excel) 📊
                    </h2>
                </div>

                <div class="p-0 text-gray-900">
                    
                    {{-- Muestra mensajes de error generales o de validación de Excel --}}
                    @include('admin.partials._session-messages')

                    {{-- Sección de Instrucciones - Estilo más limpio y moderno --}}
                    <div class="mb-8 p-6 bg-gray-50 border-l-4 border-blue-500 rounded-lg text-sm text-gray-700 shadow-md">
                        <h4 class="font-extrabold text-xl text-blue-700 mb-3 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Instrucciones Clave para la Importación
                        </h4>
                        
                        <p class="mb-4">Sube un archivo Excel (.xls o .xlsx). La **primera fila** debe contener **exactamente** las siguientes cabeceras:</p>
                        
                        {{-- Lista de Columnas con estilo de código mejorado --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-gray-100 p-4 rounded-lg border border-gray-200 font-mono text-xs mb-4 shadow-inner">
                            <span class="py-1 px-2 bg-white rounded shadow-sm text-center">ci_estudiante</span>
                            <span class="py-1 px-2 bg-white rounded shadow-sm text-center">nombre_materia</span>
                            <span class="py-1 px-2 bg-white rounded shadow-sm text-center">carrera</span>
                            <span class="py-1 px-2 bg-white rounded shadow-sm text-center">ano_cursado</span>
                            <span class="py-1 px-2 bg-white rounded shadow-sm text-center">paralelo</span>
                            <span class="py-1 px-2 bg-white rounded shadow-sm text-center">gestion</span>
                        </div>
                        
                        <p class="font-extrabold text-red-600 mt-5 mb-2">⚠ Atención:</p>
                        <ul class="list-disc list-inside space-y-1 pl-4">
                           <li>**Pre-requisitos:** Estudiantes, Materias, y Cursos deben **existir** previamente en el sistema.</li>
                           <li>**Validación:** Las filas que no encuentren una coincidencia exacta de Estudiante + Curso (a través de los datos de Materia/Paralelo/Gestión) serán **automáticamente ignoradas**.</li>
                        </ul>
                    </div>

                    {{-- Formulario de Subida --}}
                    <form action="{{ route('admin.inscripciones.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label for="archivo_excel" class="block font-semibold text-lg text-gray-800 mb-2">
                                📂 Archivo de Inscripciones
                            </label>
                            
                            {{-- Input tipo 'file' con el mismo estilo blue-based --}}
                            <input id="archivo_excel"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                                       focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold 
                                       file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition duration-150"
                                type="file"
                                name="archivo_excel"
                                accept=".xls,.xlsx"
                                required>
                            
                            {{-- Muestra error específico para el archivo --}}
                            @error('archivo_excel')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-8 border-t pt-4">
                            
                            {{-- Botón Cancelar (Secundario) --}}
                            <a href="{{ route('admin.cursos.index') }}" 
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs
                                    text-gray-700 uppercase tracking-wider hover:bg-gray-300 active:bg-gray-400 focus:outline-none
                                    focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm mr-4">
                                Cancelar
                            </a>
                            
                            {{-- Botón Principal (blue-600) --}}
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition ease-in-out duration-150 shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Procesar Importación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection