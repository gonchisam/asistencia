<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestionar Curso: {{ $curso->materia->nombre }} ({{ $curso->paralelo }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @include('admin.partials._session-messages') <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Gestión de Horarios</h3>
                    
                    <form action="{{ route('admin.cursos.horarios.store', $curso) }}" method="POST" class="border-b pb-6 mb-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="dia_semana" value="Día" />
                                <select name="dia_semana" id="dia_semana" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach ($diasSemana as $num => $nombre)
                                        <option value="{{ $num }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="periodo_id" value="Periodo" />
                                <select name="periodo_id" id="periodo_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach ($periodos as $periodo)
                                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }} ({{ \Carbon\Carbon::parse($periodo->hora_inicio)->format('H:i') }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="aula_id" value="Aula" />
                                <select name="aula_id" id="aula_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach ($aulas as $aula)
                                        <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-primary-button type="submit">Añadir Horario</x-primary-button>
                        </div>
                        <x-input-error :messages="$errors->get('dia_semana') ?? $errors->get('periodo_id') ?? $errors->get('aula_id')" class="mt-2" />
                    </form>
                    
                    <h4 class="text-md font-medium text-gray-800 mb-2">Horarios Programados</h4>
                    <ul class="divide-y divide-gray-200">
                        @forelse ($curso->horarios as $horario)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <span class="font-semibold">{{ $diasSemana[$horario->dia_semana] }}</span>
                                    <span class="text-gray-600"> | {{ $horario->periodo->nombre }} ({{ \Carbon\Carbon::parse($horario->periodo->hora_inicio)->format('H:i') }})</span>
                                    <span class="text-gray-600"> | Aula: {{ $horario->aula->nombre }}</span>
                                </div>
                                <form action="{{ route('admin.cursos.horarios.destroy', $horario) }}" method="POST" onsubmit="return confirm('¿Eliminar este horario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Eliminar</button>
                                </form>
                            </li>
                        @empty
                            <li class="py-3 text-gray-500">No hay horarios programados para este curso.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Gestión de Estudiantes Inscritos</h3>
                    
                    <form action="{{ route('admin.cursos.estudiantes.store', $curso) }}" method="POST" class="border-b pb-6 mb-6">
                        @csrf
                        <div class="flex items-end gap-4">
                            <div class="flex-grow">
                                <x-input-label for="ci" value="CI del Estudiante" />
                                <x-text-input id="ci" class="block mt-1 w-full" type="text" name="ci" :value="old('ci')" required />
                                <x-input-error :messages="$errors->get('ci')" class="mt-2" />
                            </div>
                            <x-primary-button type="submit">Inscribir Estudiante</x-primary-button>
                        </div>
                    </form>

                    <h4 class="text-md font-medium text-gray-800 mb-2">Estudiantes Inscritos ({{ $curso->estudiantes->count() }})</h4>
                    <ul class="divide-y divide-gray-200">
                        @forelse ($curso->estudiantes as $estudiante)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <span class="font-semibold">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</span>
                                    <span class="text-gray-600"> | CI: {{ $estudiante->ci }}</span>
                                </div>
                                <form action="{{ route('admin.cursos.estudiantes.destroy', [$curso, $estudiante]) }}" method="POST" onsubmit="return confirm('¿Quitar a este estudiante del curso?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Quitar</button>
                                </form>
                            </li>
                        @empty
                            <li class="py-3 text-gray-500">No hay estudiantes inscritos en este curso.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>