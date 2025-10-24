<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Importar Inscripciones (Excel)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Error!</strong>
                            <ul class="mt-3 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4 p-4 bg-blue-100 border border-blue-300 rounded">
                        <h4 class="font-bold">Instrucciones:</h4>
                        <p class="text-sm">Sube un archivo Excel (.xls o .xlsx) con las siguientes columnas (con cabecera):</p>
                        <ul class="list-disc list-inside text-sm mt-2 font-mono">
                            <li>ci_estudiante</li>
                            <li>nombre_materia</li>
                            <li>carrera</li>
                            <li>ano_cursado</li>
                            <li>paralelo</li>
                            <li>gestion</li>
                        </ul>
                        <p class="text-sm mt-2"><b>Importante:</b> Los estudiantes, materias y cursos (paralelo/gestión) deben existir previamente en el sistema para que la inscripción sea exitosa.</p>
                    </div>

                    <form action="{{ route('admin.inscripciones.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div>
                            <x-input-label for="archivo_excel" value="Archivo Excel" />
                            <input id="archivo_excel" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" type="file" name="archivo_excel" required>
                            <x-input-error :messages="$errors->get('archivo_excel')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.cursos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Procesar Importación
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>