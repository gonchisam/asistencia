@extends('layouts.app')

@section('content')

    {{-- Contenedor principal con estilo moderno --}}
    <div class="py-12">
        {{-- Usamos max-w-3xl para una consistencia general, aunque el original era 2xl. Lo mantendré en 2xl para este contenido más corto, pero lo centraré con el mismo estilo de tarjeta. --}}
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8"> 

            {{-- Aplica el mismo estilo de tarjeta: rounded-xl, shadow-2xl con hover, p-8 --}}
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

                {{-- Encabezado con flecha y título unificado --}}
                <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">

                    {{-- Flecha "Atrás" con hover en azul. La ruta debe ser 'admin.estudiantes.index' o similar. --}}
                    <a href="{{ route('students.index') }}"
                        class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                        title="Volver a la Gestión de Estudiantes">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>

                    {{-- Título Principal con color azul destacado --}}
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        <span class="text-blue-600">Importar</span> Estudiantes (Excel) 👨‍🎓
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
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                {{-- Sección de Instrucciones - Usando el estilo de borde azul --}}
                <div class="mb-8 p-6 bg-gray-50 border-l-4 border-blue-500 rounded-lg text-sm text-gray-700 shadow-md">
                    <h4 class="font-extrabold text-xl text-blue-700 mb-3 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Guía de Formato de Archivo
                    </h4>
                    
                    <p class="mb-3">El archivo debe ser Excel (.xlsx, .xls) y la primera fila debe contener las siguientes cabeceras:</p>

                    <div class="space-y-3">
                        
                        <div class="font-semibold text-gray-800">
                            Columnas Requeridas:
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 bg-gray-100 p-3 rounded-lg border border-gray-200 font-mono text-xs mt-2 shadow-inner">
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">ci</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">nombre</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">primer_apellido</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">fecha_nacimiento</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">carrera</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">año/ano</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">sexo</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">correo</span>
                            </div>
                        </div>

                        <div class="font-semibold text-gray-800 pt-1">
                            Columnas Opcionales:
                            <div class="flex flex-wrap gap-2 bg-gray-100 p-3 rounded-lg border border-gray-200 font-mono text-xs mt-2 shadow-inner">
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">segundo_apellido</span>
                                <span class="py-1 px-2 bg-white rounded shadow-sm text-center">celular</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de Ejemplos --}}
                    <div class="mt-6">
                        <h5 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Ejemplos de Formato Correcto:
                        </h5>
                        
                        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 border-b border-gray-300">
                                    <tr>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">ci</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">nombre</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">primer_apellido</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">segundo_apellido</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">fecha_nacimiento</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">carrera</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">año</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">sexo</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700 border-r">correo</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700">celular</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    {{-- Ejemplo 1 --}}
                                    <tr class="hover:bg-blue-50 transition-colors">
                                        <td class="py-2 px-3 border-r font-mono text-xs">12345678</td>
                                        <td class="py-2 px-3 border-r">María</td>
                                        <td class="py-2 px-3 border-r">González</td>
                                        <td class="py-2 px-3 border-r">Pérez</td>
                                        <td class="py-2 px-3 border-r font-mono text-xs">2000-05-15</td>
                                        <td class="py-2 px-3 border-r">Sistemas</td>
                                        <td class="py-2 px-3 border-r">Primer año</td>
                                        <td class="py-2 px-3 border-r">Femenino</td>
                                        <td class="py-2 px-3 border-r font-mono text-xs">maria.gonzalez@gmail.com</td>
                                        <td class="py-2 px-3">71409147</td>
                                    </tr>
                                    {{-- Ejemplo 2 --}}
                                    <tr class="hover:bg-blue-50 transition-colors">
                                        <td class="py-2 px-3 border-r font-mono text-xs">6484552-2A</td>
                                        <td class="py-2 px-3 border-r">Carlos</td>
                                        <td class="py-2 px-3 border-r">López</td>
                                        <td class="py-2 px-3 border-r"></td>
                                        <td class="py-2 px-3 border-r font-mono text-xs">1999-12-20</td>
                                        <td class="py-2 px-3 border-r">Contabilidad</td>
                                        <td class="py-2 px-3 border-r">Tercer año</td>
                                        <td class="py-2 px-3 border-r">Masculino</td>
                                        <td class="py-2 px-3 border-r font-mono text-xs">carlos.lopez@gmail.com</td>
                                        <td class="py-2 px-3"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        
                    </div>

                    <p class="font-extrabold text-red-600 mt-5 mb-2">⚠ Consideraciones Importantes:</p>
                    <ul class="list-disc list-inside space-y-1 pl-4 text-xs">
                        <li>El formato de **`fecha_nacimiento`** debe ser **YYYY-MM-DD** (ej: 2001-05-15).</li>
                        <li>Los estudiantes con **CI o Correo duplicado** (en el archivo o en la base de datos) serán **omitidos**.</li>
                        <li>Las cabeceras deben escribirse exactamente como se muestran (minúsculas, sin espacios extra).</li>
                    </ul>
                </div>

                {{-- Formulario de Subida --}}
                <form action="{{ route('admin.estudiantes.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="archivo_excel" class="block font-semibold text-lg text-gray-800 mb-2">
                                📂 Seleccionar Archivo Excel
                            </label>
                            
                            {{-- Input tipo 'file' con el estilo blue-based consistente --}}
                            <input id="archivo_excel"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                                       focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold 
                                       file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition duration-150"
                                type="file"
                                name="archivo_excel"
                                accept=".xlsx, .xls"
                                required>

                            @error('archivo_excel')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                        
                        {{-- Botón Cancelar (Secundario) --}}
                        <a href="{{ route('students.index') }}" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs
                                text-gray-700 uppercase tracking-wider hover:bg-gray-300 active:bg-gray-400 focus:outline-none
                                focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm mr-4">
                            Cancelar
                        </a>
                        
                        {{-- Botón Principal (blue-600) --}}
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition ease-in-out duration-150 shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Importar Estudiantes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection