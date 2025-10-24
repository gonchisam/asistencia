<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Curso (Grupo)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.cursos.store') }}" method="POST">
                        @csrf
                        
                        @if ($errors->any())
                            @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="materia_id" value="Materia" />
                                <select name="materia_id" id="materia_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Seleccione una materia...</option>
                                    @foreach ($materias as $materia)
                                        <option value="{{ $materia->id }}" @selected(old('materia_id') == $materia->id)>
                                            {{ $materia->nombre }} ({{ $materia->carrera }} - {{ $materia->ano_cursado }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('materia_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="paralelo" value="Paralelo (Ej: A, B, Unico)" />
                                <x-text-input id="paralelo" class="block mt-1 w-full" type="text" name="paralelo" :value="old('paralelo')" required />
                                <x-input-error :messages="$errors->get('paralelo')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="gestion" value="Gestión (Ej: 2025, 1-2025)" />
                                <x-text-input id="gestion" class="block mt-1 w-full" type="text" name="gestion" :value="old('gestion')" required />
                                <x-input-error :messages="$errors->get('gestion')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.cursos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Crear Curso
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>