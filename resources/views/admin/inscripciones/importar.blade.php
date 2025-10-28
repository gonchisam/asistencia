@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

            {{-- Encabezado --}}
            <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">
                {{-- Flecha "Atrás" --}}
                <a href="{{ route('admin.cursos.index') }}" 
                   class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                   title="Volver a la Gestión">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>

                {{-- Título Principal --}}
                <h2 class="text-3xl font-extrabold text-gray-900">
                    <span class="text-blue-600">Importar</span> Inscripciones (Excel) 📊
                </h2>
            </div>

            <div class="text-gray-900">
                {{-- Mensajes de sesión --}}
                @include('admin.partials._session-messages')

                {{-- Sección de Instrucciones --}}
                <div class="mb-8 p-6 bg-gray-50 border-l-4 border-blue-500 rounded-lg text-sm text-gray-700 shadow-md">
                    <h4 class="font-extrabold text-xl text-blue-700 mb-3 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Instrucciones Clave para la Importación
                    </h4>
                    
                    <p class="mb-4">Sube un archivo Excel (.xls o .xlsx). La <strong>primera fila</strong> debe contener <strong>exactamente</strong> las siguientes cabeceras:</p>
                    
                    {{-- Cabeceras requeridas --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-gray-100 p-4 rounded-lg border border-gray-200 font-mono text-xs mb-4 shadow-inner">
                        <span class="py-1 px-2 bg-white rounded shadow-sm text-center">ci_estudiante</span>
                        <span class="py-1 px-2 bg-white rounded shadow-sm text-center">carrera</span>
                        <span class="py-1 px-2 bg-white rounded shadow-sm text-center">ano_cursado</span>
                        <span class="py-1 px-2 bg-white rounded shadow-sm text-center">paralelo</span>
                        <span class="py-1 px-2 bg-white rounded shadow-sm text-center">gestion</span>
                    </div>

                    {{-- Tabla de Ejemplo --}}
                    <div class="mt-6 mb-4">
                        <h5 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h2m0 0l2-2m-2 2l2 2m5.5-10a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Ejemplo de Estructura:
                        </h5>
                        <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
                            <table class="min-w-full text-sm">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th class="py-3 px-4 text-left font-bold text-blue-800 border-b border-blue-100">ci_estudiante</th>
                                        <th class="py-3 px-4 text-left font-bold text-blue-800 border-b border-blue-100">carrera</th>
                                        <th class="py-3 px-4 text-left font-bold text-blue-800 border-b border-blue-100">ano_cursado</th>
                                        <th class="py-3 px-4 text-left font-bold text-blue-800 border-b border-blue-100">paralelo</th>
                                        <th class="py-3 px-4 text-left font-bold text-blue-800 border-b border-blue-100">gestion</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 font-mono text-gray-700">1234567</td>
                                        <td class="py-2 px-4">Sistemas</td>
                                        <td class="py-2 px-4 text-center">Primer Año</td>
                                        <td class="py-2 px-4 text-center">Primero C</td>
                                        <td class="py-2 px-4 text-center">2025</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 bg-gray-50">
                                        <td class="py-2 px-4 font-mono text-gray-700">7654321</td>
                                        <td class="py-2 px-4">Contabilidad</td>
                                        <td class="py-2 px-4 text-center">Segundo Año</td>
                                        <td class="py-2 px-4 text-center">Segundo A</td>
                                        <td class="py-2 px-4 text-center">2025</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 font-mono text-gray-700">8912345</td>
                                        <td class="py-2 px-4">Secretariado</td>
                                        <td class="py-2 px-4 text-center">Tercer Año</td>
                                        <td class="py-2 px-4 text-center">Tercero A</td>
                                        <td class="py-2 px-4 text-center">2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-2 italic">Nota: Los datos mostrados son ejemplos ilustrativos.</p>
                    </div>
                    
                    <p class="font-extrabold text-red-600 mt-6 mb-2">⚠ Atención:</p>
                    <ul class="list-disc list-inside space-y-1 pl-4">
                        <li><strong>Pre-requisitos:</strong> Estudiantes, Materias, y Cursos deben <strong>existir</strong> previamente en el sistema.</li>
                        
                        <li><strong>Inscripción:</strong> Se inscribirá al estudiante en <strong>todos</strong> los cursos que coincidan con esas materias, el <code>paralelo</code> y la <code>gestion</code>.</li>
                        <li><strong>Omisión:</strong> Las filas que no encuentren un estudiante, o que no encuentren cursos (ej. paralelo/gestión incorrectos), serán <strong>ignoradas</strong>.</li>
                    </ul>
                </div>

                {{-- Formulario de Subida --}}
                <form action="{{ route('admin.inscripciones.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label for="archivo_excel" class="block font-semibold text-lg text-gray-800 mb-2">
                            📂 Archivo de Inscripciones
                        </label>
                        
                        <input id="archivo_excel"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                                   focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                   file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold 
                                   file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition duration-150"
                            type="file"
                            name="archivo_excel"
                            accept=".xls,.xlsx"
                            required>
                        
                        @error('archivo_excel')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-end mt-8 border-t pt-4">
                        {{-- Botón Cancelar --}}
                        <a href="{{ route('admin.cursos.index') }}" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs
                                   text-gray-700 uppercase tracking-wider hover:bg-gray-300 active:bg-gray-400 focus:outline-none
                                   focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm mr-4">
                            Cancelar
                        </a>
                        
                        {{-- Botón Procesar --}}
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs 
                                   text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none 
                                   focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition ease-in-out duration-150 shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Procesar Importación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection