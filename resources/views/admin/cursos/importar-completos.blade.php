<x-app-layout>
    <x-slot name="header">
        {{-- Slot de header se remueve para usar el diseño unificado de la tarjeta principal --}}
    </x-slot>

    {{-- Contenedor principal con estilo moderno --}}
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

                {{-- Encabezado con flecha y título --}}
                <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">
                    <a href="{{ route('admin.cursos.index') }}"
                        class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                        title="Volver a Gestión de Cursos">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>

                    <h2 class="text-3xl font-extrabold text-gray-900">
                        <span class="text-purple-600">📂 Importar</span> Cursos Completos
                    </h2>
                </div>

                <div class="text-gray-900">

                    {{-- Alerta de Errores --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-lg" role="alert">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 mr-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <strong class="font-bold text-lg">¡Errores Detectados!</strong>
                            </div>
                            <ul class="list-disc list-inside text-sm ml-8">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Alerta de Errores de Importación --}}
                    @if (session('import_errors'))
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg" role="alert">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 mr-3 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <strong class="font-bold text-lg">Algunas Filas Tuvieron Problemas:</strong>
                            </div>
                            <ul class="list-disc list-inside text-sm ml-8 max-h-60 overflow-y-auto">
                                @foreach (session('import_errors') as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Bloque de Instrucciones --}}
                    <div class="mb-6 p-6 bg-purple-50 border-l-4 border-purple-600 rounded-lg shadow-md">
                        <h4 class="font-bold text-lg text-purple-800 flex items-center mb-3">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Instrucciones para la Importación:
                        </h4>
                        
                        <p class="text-sm text-gray-700 mb-3">
                            Esta herramienta te permite crear <strong>cursos completos</strong> (con horarios y docente asignado) desde un solo archivo Excel o TXT.
                        </p>

                        <p class="text-sm text-gray-700 mb-3">
                            <strong>Formato del archivo:</strong> Excel (.xlsx, .xls) o TXT separado por comas/tabs
                        </p>

                        {{-- Columnas Requeridas --}}
                        <div class="mt-3 bg-gray-100 p-4 rounded-md border border-gray-200">
                            <p class="text-xs font-semibold text-gray-700 mb-2">📋 Columnas (cabecera exacta):</p>
                            <code class="text-purple-700 font-mono text-xs md:text-sm block whitespace-pre-wrap">
dia | periodo | aula | materia | carrera | ano_cursado | paralelo | gestion | docente_correo
                            </code>
                        </div>

                        {{-- Tabla de Ejemplos --}}
                        <div class="mt-4 overflow-x-auto">
                            <p class="text-xs font-semibold text-gray-700 mb-2">✅ Ejemplo de datos:</p>
                            <table class="min-w-full text-xs border border-gray-300">
                                <thead class="bg-purple-100">
                                    <tr>
                                        <th class="border px-2 py-1">dia</th>
                                        <th class="border px-2 py-1">periodo</th>
                                        <th class="border px-2 py-1">aula</th>
                                        <th class="border px-2 py-1">materia</th>
                                        <th class="border px-2 py-1">carrera</th>
                                        <th class="border px-2 py-1">ano_cursado</th>
                                        <th class="border px-2 py-1">paralelo</th>
                                        <th class="border px-2 py-1">gestion</th>
                                        <th class="border px-2 py-1">docente_correo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr>
                                        <td class="border px-2 py-1">lunes</td>
                                        <td class="border px-2 py-1">Primer periodo</td>
                                        <td class="border px-2 py-1">AULA 2-4</td>
                                        <td class="border px-2 py-1">EMPRENDIMIENTO PRODUCTIVO (SIS)</td>
                                        <td class="border px-2 py-1">Sistemas</td>
                                        <td class="border px-2 py-1">Primer Año</td>
                                        <td class="border px-2 py-1">Primero A</td>
                                        <td class="border px-2 py-1">2025</td>
                                        <td class="border px-2 py-1">alinvar@saca.edu</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="border px-2 py-1">martes</td>
                                        <td class="border px-2 py-1">Tercer periodo</td>
                                        <td class="border px-2 py-1">AULA 1-7</td>
                                        <td class="border px-2 py-1">PROGRAMACIÓN II</td>
                                        <td class="border px-2 py-1">Sistemas</td>
                                        <td class="border px-2 py-1">Segundo Año</td>
                                        <td class="border px-2 py-1">Segundo B</td>
                                        <td class="border px-2 py-1">2025</td>
                                        <td class="border px-2 py-1">joeala@saca.edu</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Notas Importantes --}}
                        <div class="mt-4 space-y-2 text-sm">
                            <p class="text-gray-700">
                                <strong>📝 Notas importantes:</strong>
                            </p>
                            <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                                <li><strong>Día:</strong> Escribir en español: lunes, martes, miércoles, jueves, viernes, sábado, domingo</li>
                                <li><strong>Periodo:</strong> Debe existir previamente en el sistema (ej: "Primer periodo , Segundo periodo, etc.)</li>
                                <li><strong>Aula:</strong> Debe existir en el sistema.</li>
                                <li><strong>Materia:</strong> Debe estar registrada con su carrera y año.</li>
                                <li><strong>Docente:</strong> Buscar por <strong>correo electrónico.</strong></li>
                                <li><strong>Duplicados:</strong> Si el curso ya existe, solo se añade el horario (no se duplica el curso)</li>
                            </ul>
                        </div>

                        <p class="text-sm mt-3 text-red-600">
                            <b>⚠️ Importante:</b> Los <strong>periodos, aulas, materias y docentes</strong> deben existir previamente en el sistema.
                        </p>
                    </div>

                    {{-- Formulario de Carga de Archivo --}}
                    <form action="{{ route('admin.cursos.importar-completos.procesar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div>
                            <x-input-label for="archivo_excel" value="Seleccionar Archivo de Cursos Completos" class="text-gray-700"/>
                            <input id="archivo_excel" 
                                class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                                       focus:outline-none focus:border-purple-500 focus:ring-purple-500 p-2" 
                                type="file" name="archivo_excel" accept=".xlsx, .xls, .txt" required>
                            <x-input-error :messages="$errors->get('archivo_excel')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Formatos aceptados: Excel (.xlsx, .xls) o TXT</p>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end mt-6 space-x-4">
                            
                            {{-- Botón Cancelar --}}
                            <a href="{{ route('admin.cursos.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-sm 
                                      text-gray-700 uppercase tracking-wider hover:bg-gray-300 active:bg-gray-400 focus:outline-none 
                                      focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                Cancelar
                            </a>
                            
                            {{-- Botón Procesar --}}
                            <x-primary-button class="bg-purple-600 hover:bg-purple-700 active:bg-purple-800 focus:ring-purple-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Procesar Importación
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>