<x-app-layout>
    <x-slot name="header">
        {{-- Título más destacado en el layout, usando el estilo del ejemplo anterior --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <span class="text-blue-600">Gestión de</span> Curso
        </h2>
        <h3 class="text-gray-600 text-sm mt-1 mb-2">
            Materia: {{ $curso->materia->nombre }} (Paralelo {{ $curso->paralelo }}) - Gestión {{ $curso->gestion }}
        </h3>
        
        {{-- Mostrar el docente asignado --}}
        @if($curso->docente)
            <p class="text-green-600 text-sm font-medium">
                👨‍🏫 Docente: {{ $curso->docente->name }} ({{ $curso->docente->email }})
            </p>
        @else
            <p class="text-orange-500 text-sm font-medium">
                ⚠️ Sin docente asignado
            </p>
        @endif

        {{-- Botón de Volver (flecha atrás) --}}
        <a href="{{ route('admin.cursos.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm 
                  text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500
                  transition ease-in-out duration-150 mt-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a Cursos
        </a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @include('admin.partials._session-messages')

            {{-- 1. Gestión de Horarios: Estilo de Tarjeta Moderna --}}
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">
                <div class="text-gray-900">
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-6 border-b pb-4">
                        <span class="text-green-600">📝 Horarios</span> Programados
                    </h3>
                    
                    {{-- Formulario de Adición de Horario --}}
                    <form action="{{ route('admin.cursos.horarios.store', $curso) }}" method="POST" class="border-b border-gray-100 pb-6 mb-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                            <div>
                                <x-input-label for="dia_semana" value="Día" />
                                <select name="dia_semana" id="dia_semana" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                    @foreach ($diasSemana as $num => $nombre)
                                        <option value="{{ $num }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="periodo_id" value="Periodo" />
                                <select name="periodo_id" id="periodo_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                    @foreach ($periodos as $periodo)
                                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }} ({{ \Carbon\Carbon::parse($periodo->hora_inicio)->format('H:i') }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="aula_id" value="Aula" />
                                <select name="aula_id" id="aula_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                    @foreach ($aulas as $aula)
                                        <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Botón de Añadir (Azul como acción principal) --}}
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md h-10">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Añadir Horario
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('dia_semana') ?? $errors->get('periodo_id') ?? $errors->get('aula_id')" class="mt-2" />
                    </form>
                    
                    {{-- Lista de Horarios --}}
                    <h4 class="text-xl font-medium text-gray-800 mb-4">Detalle de Sesiones</h4>
                    <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                        @forelse ($curso->horarios as $horario)
                            <li class="py-3 px-4 flex justify-between items-center hover:bg-green-50/50 transition duration-150">
                                <div class="flex items-center space-x-4">
                                    <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
                                    <div>
                                        <span class="font-bold text-gray-800">{{ $diasSemana[$horario->dia_semana] }}</span>
                                        <span class="text-gray-600 text-sm"> | {{ $horario->periodo->nombre }} ({{ \Carbon\Carbon::parse($horario->periodo->hora_inicio)->format('H:i') }})</span>
                                        <span class="text-purple-600 text-sm font-medium"> | Aula: {{ $horario->aula->nombre }}</span>
                                    </div>
                                </div>
                                <form action="{{ route('admin.cursos.horarios.destroy', $horario) }}" method="POST" onsubmit="return confirm('¿Eliminar este horario? Esta acción es inmediata.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold transition duration-150 p-1 rounded">
                                        Eliminar
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="py-4 px-4 text-center text-gray-500 bg-gray-50">
                                📚 Este curso aún no tiene horarios asignados.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
            
            {{-- 2. Gestión de Estudiantes Inscritos: Estilo de Tarjeta Moderna --}}
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">
                <div class="p-0 text-gray-900">
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-6 border-b pb-4">
                        <span class="text-blue-600">👨‍🎓 Estudiantes</span> Inscritos
                    </h3>
                    
                    {{-- Formulario de Inscripción de Estudiante --}}
                    <form action="{{ route('admin.cursos.estudiantes.store', $curso) }}" method="POST" class="border-b border-gray-100 pb-6 mb-6">
                        @csrf
                        <div class="flex items-end gap-6">
                            <div class="flex-grow">
                                <x-input-label for="ci" value="CI del Estudiante" class="text-gray-700"/>
                                <x-text-input id="ci" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" type="text" name="ci" :value="old('ci')" placeholder="Escriba el CI para inscribir..." required />
                                <x-input-error :messages="$errors->get('ci')" class="mt-2" />
                            </div>
                            {{-- Botón de Inscripción (Azul como acción principal) --}}
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md h-10">
                                Inscribir
                            </button>
                        </div>
                    </form>

                    {{-- Lista de Estudiantes --}}
                    <h4 class="text-xl font-medium text-gray-800 mb-4">Lista (Total: {{ $curso->estudiantes->count() }})</h4>
                    <div class="overflow-x-auto">
                        <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                            @forelse ($curso->estudiantes->sortBy('apellido') as $estudiante)
                                <li class="py-3 px-4 flex justify-between items-center hover:bg-blue-50/50 transition duration-150">
                                    <div class="flex items-center space-x-4">
                                        <span class="font-bold text-gray-800">
                                            {{ $estudiante->primer_apellido }} 
                                            {{ $estudiante->segundo_apellido ? ' ' . $estudiante->segundo_apellido . ' ' : ' ' }}
                                            {{ $estudiante->nombre }}
                                        </span>
                                        <span class="text-gray-600 text-sm"> | CI: {{ $estudiante->ci }}</span>
                                    </div>
                                    <form action="{{ route('admin.cursos.estudiantes.destroy', [$curso, $estudiante]) }}" method="POST" onsubmit="return confirm('⚠️ ¿Está seguro de quitar a {{ $estudiante->nombre }} del curso?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold transition duration-150 p-1 rounded">
                                            Quitar
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="py-4 px-4 text-center text-gray-500 bg-gray-50">
                                    😔 Aún no hay estudiantes inscritos en este curso.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>