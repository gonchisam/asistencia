@extends('layouts.app') {{-- Usa tu layout principal --}}

@section('header') {{-- Título de la página --}}
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Importar Inscripciones (Excel)
    </h2>
@endsection

@section('content') {{-- Contenido principal --}}
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8"> {{-- Contenedor más estrecho --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Muestra mensajes de error generales o de validación de Excel --}}
                    @include('admin.partials._session-messages') 

                    {{-- Sección de Instrucciones --}}
                    <div class="mb-6 p-4 bg-blue-100 border border-blue-300 rounded-lg text-sm text-blue-800">
                        <h4 class="font-bold text-lg mb-2">Instrucciones:</h4>
                        <p>Sube un archivo Excel (.xls o .xlsx) con las siguientes columnas exactas (incluyendo la cabecera en la primera fila):</p>
                        <ul class="list-disc list-inside mt-2 mb-3 pl-4 font-mono bg-gray-100 p-2 rounded">
                            <li>ci_estudiante</li>
                            <li>nombre_materia</li>
                            <li>carrera</li>
                            <li>ano_cursado</li>
                            <li>paralelo</li>
                            <li>gestion</li>
                        </ul>
                        <p class="font-semibold">Importante:</p>
                        <ul class="list-disc list-inside mt-1 pl-4">
                           <li>Los estudiantes deben existir previamente en el sistema (buscados por CI).</li>
                           <li>Las materias deben existir previamente (buscadas por nombre, carrera y año).</li>
                           <li>Los cursos deben existir previamente (buscados por materia, paralelo y gestión).</li>
                           <li>Si una fila del Excel no encuentra alguna de estas coincidencias, será ignorada.</li>
                        </ul>
                    </div>

                    {{-- Formulario de Subida --}}
                    <form action="{{ route('admin.inscripciones.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div>
                            <label for="archivo_excel" class="block font-medium text-sm text-gray-700 mb-1">Seleccionar Archivo Excel</label>
                            {{-- Input tipo 'file' --}}
                            <input id="archivo_excel" 
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                                   type="file" 
                                   name="archivo_excel" 
                                   accept=".xls,.xlsx" {{-- Acepta solo archivos Excel --}}
                                   required>
                            {{-- Muestra error específico para el archivo --}}
                            @error('archivo_excel')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Botones --}}
                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('admin.cursos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4 text-sm font-medium">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Procesar Importación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection