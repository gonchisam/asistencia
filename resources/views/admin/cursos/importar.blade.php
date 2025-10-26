<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            <span class="text-purple-600">📂 Importar</span> Inscripciones (Excel)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Contenedor Principal: Tarjeta Moderna --}}
            <div class="bg-white overflow-hidden shadow-xl rounded-xl p-8">
                <div class="text-gray-900">

                    {{-- Alerta de Errores (Refactorizada) --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-lg" role="alert">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                <strong class="font-bold text-lg">¡Proceso Fallido!</strong>
                            </div>
                            <ul class="mt-3 list-disc list-inside text-sm ml-8">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Bloque de Instrucciones y Formato --}}
                    <div class="mb-6 p-6 bg-purple-50 border-l-4 border-purple-600 rounded-lg shadow-md">
                        <h4 class="font-bold text-lg text-purple-800 flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Instrucciones para la Importación:
                        </h4>
                        <p class="text-sm text-gray-700">Por favor, sube un archivo **Excel (.xls o .xlsx)** que contenga las siguientes columnas exactamente como se listan (con cabecera):</p>
                        
                        {{-- Lista de Columnas (Formato de código) --}}
                        <div class="mt-3 bg-gray-100 p-3 rounded-md border border-gray-200">
                            <code class="text-purple-700 font-mono text-xs md:text-sm block whitespace-pre-wrap">
                                ci_estudiante | nombre_materia | carrera | ano_cursado | paralelo | gestion
                            </code>
                        </div>

                        <p class="text-sm mt-3 text-red-600">
                            <b>⚠️ Importante:</b> Los estudiantes, materias y cursos (paralelo/gestión) deben **existir previamente** en el sistema para que la inscripción se registre correctamente.
                        </p>
                    </div>

                    {{-- Formulario de Carga de Archivo --}}
                    <form action="{{ route('admin.inscripciones.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div>
                            <x-input-label for="archivo_excel" value="Seleccionar Archivo de Inscripciones" class="text-gray-700"/>
                            <input id="archivo_excel" 
                                class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                                       focus:outline-none focus:border-purple-500 focus:ring-purple-500 p-2" 
                                type="file" name="archivo_excel" accept=".xlsx, .xls" required>
                            <x-input-error :messages="$errors->get('archivo_excel')" class="mt-2" />
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-6 space-x-4">
                            
                            {{-- Botón Cancelar (Estilo Secundario) --}}
                            <a href="{{ route('admin.cursos.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-sm 
                                      text-gray-700 uppercase tracking-wider hover:bg-gray-300 active:bg-gray-400 focus:outline-none 
                                      focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                Cancelar
                            </a>
                            
                            {{-- Botón Principal (Estilo Primario) --}}
                            <x-primary-button class="bg-purple-600 hover:bg-purple-700 active:bg-purple-800 focus:ring-purple-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Procesar Importación
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>