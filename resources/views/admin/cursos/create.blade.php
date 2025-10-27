<x-app-layout>
    <x-slot name="header">
        {{-- Slot de header se remueve para usar el diseño unificado de la tarjeta principal --}}
    </x-slot>

    {{-- Contenedor principal con estilo moderno (igual que materias.create) --}}
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Aplica el mismo estilo de tarjeta: rounded-xl, shadow-2xl con hover, p-8 --}}
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">

                {{-- Encabezado unificado con flecha y título (igual que materias.create) --}}
                <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">

                    {{-- Flecha "Atrás" con hover en azul --}}
                    <a href="{{ route('admin.cursos.index') }}"
                        class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                        title="Volver a la Gestión de Cursos">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>

                    {{-- Título Principal con color azul destacado --}}
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        {{-- Cambiado de indigo-600 a blue-600 para unificar el color primario --}}
                        <span class="text-blue-600">Crear Nuevo</span> Curso (Grupo) 🧑‍🏫
                    </h2>
                </div>

                <div class="p-0 text-gray-900">
                    <form action="{{ route('admin.cursos.store') }}" method="POST">
                        @csrf

                        {{-- Manejo de Errores Global (Manteniendo el estilo original) --}}
                        @if ($errors->any())
                            <div class="mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-lg"
                                role="alert">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0 text-red-600" fill="currentColor"
                                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <strong class="font-bold text-lg">Revisa los Errores:</strong>
                                </div>
                                <ul class="list-disc list-inside text-sm ml-8">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Campo Materia --}}
                            <div class="md:col-span-2">
                                <x-input-label for="materia_id" value="Materia" class="text-gray-700" />
                                <select name="materia_id" id="materia_id"
                                    {{-- Cambiado a blue-500 para unificar el color de focus --}}
                                    class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                                    required>
                                    <option value="">-- Seleccione una materia --</option>
                                    @foreach ($materias as $materia)
                                        <option value="{{ $materia->id }}" @selected(old('materia_id') == $materia->id)>
                                            {{ $materia->nombre }} ({{ $materia->carrera }} -
                                            {{ $materia->ano_cursado }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('materia_id')" class="mt-2" />
                            </div>

                            {{-- Campo Paralelo --}}
                            <div>
                                <x-input-label for="paralelo" value="Paralelo (Ej: A, B, Único)"
                                    class="text-gray-700" />
                                {{-- Asegurando que x-text-input use el focus blue --}}
                                <x-text-input id="paralelo" class="block mt-1 w-full rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                    type="text" name="paralelo" :value="old('paralelo')" placeholder="A" required />
                                <x-input-error :messages="$errors->get('paralelo')" class="mt-2" />
                            </div>

                            {{-- Campo Gestión --}}
                            <div>
                                <x-input-label for="gestion" value="Gestión (Ej: 2025 o 1-2025)"
                                    class="text-gray-700" />
                                {{-- Asegurando que x-text-input use el focus blue --}}
                                <x-text-input id="gestion" class="block mt-1 w-full rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                    type="text" name="gestion" :value="old('gestion', date('Y'))" placeholder="{{ date('Y') }}" required />
                                <x-input-error :messages="$errors->get('gestion')" class="mt-2" />
                            </div>

                            {{-- ¡NUEVO CAMPO! Docente Asignado --}}
                            <div class="md:col-span-2">
                                <x-input-label for="docente_id" value="Docente Asignado (Opcional)" class="text-gray-700" />
                                <select name="docente_id" id="docente_id"
                                    class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                    <option value="">-- Sin asignar docente --</option>
                                    @foreach ($docentes as $docente)
                                        <option value="{{ $docente->id }}" @selected(old('docente_id') == $docente->id)>
                                            {{ $docente->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('docente_id')" class="mt-2" />
                            </div>

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

                            {{-- Botón Crear Curso (Primario) --}}
                            {{-- Cambiado de indigo a blue para unificar el color primario --}}
                            <x-primary-button class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Crear Curso
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>