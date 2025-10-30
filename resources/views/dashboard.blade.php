@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 w-full max-w-7xl mx-auto">
    
    {{-- 🔹 Título principal --}}
    <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">
        <span class="text-blue-600">Registro de Asistencia</span>
    </h2>

    {{-- 🔹 Contenedor de filtros con colapsable --}}
    <div x-data="{ open: false }" class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-semibold text-gray-800 flex items-center">
                Filtros de Asistencia
            </h3>

            {{-- Botón lateral más pequeño --}}
            <button 
                @click="open = !open"
                type="button"
                class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-md text-sm transition"
                title="Mostrar / Ocultar filtros"
            >
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
                <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
            </button>
        </div>

        {{-- 🔹 Formulario colapsable --}}
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="overflow-hidden"
        >
            <form method="GET" action="{{ route('dashboard') }}" class="w-full">
                <div class="p-6 bg-gray-50 rounded-xl border border-gray-200">
                    
                    {{-- 🔸 Fila 1: UID + Fecha Desde + Fecha Hasta --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <div>
                            <x-input-label for="search-uid" :value="__('Buscar por UID')" />
                            <x-text-input 
                                id="search-uid" 
                                name="uid"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                type="text" 
                                placeholder="Ej. 123ABC" 
                                value="{{ request('uid') }}"
                            />
                        </div>
                        <div>
                            <x-input-label for="fecha_desde" :value="__('Fecha Desde')" />
                            <x-text-input 
                                id="fecha_desde" 
                                name="fecha_desde"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                type="date" 
                                value="{{ request('fecha_desde') }}"
                            />
                        </div>
                        <div>
                            <x-input-label for="fecha_hasta" :value="__('Fecha Hasta')" />
                            <x-text-input 
                                id="fecha_hasta" 
                                name="fecha_hasta"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                type="date" 
                                value="{{ request('fecha_hasta') }}"
                            />
                        </div>
                    </div>

                    {{-- 🔸 Fila 2: Carrera + Año --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="carrera" :value="__('Carrera')" />
                            <select id="carrera" name="carrera" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Todas</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera }}" {{ request('carrera') == $carrera ? 'selected' : '' }}>
                                        {{ $carrera }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="año" :value="__('Año Cursado')" />
                            <select id="año" name="año" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach($años as $año)
                                    <option value="{{ $año }}" {{ request('año') == $año ? 'selected' : '' }}>
                                        {{ $año }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 🔸 Fila 3: Curso + Materia --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="curso_id" :value="__('Curso (Grupo)')" />
                            <select id="curso_id" name="curso_id" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach($cursos as $curso)
                                    <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                                        {{ $curso->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="materia_id" :value="__('Materia')" />
                            <select id="materia_id" name="materia_id" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Todas</option>
                                @foreach($materias as $materia)
                                    <option value="{{ $materia->id }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }}>
                                        {{ $materia->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 🔸 Fila 4: Modo + Estado --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="modo" :value="__('Modo de Asistencia')" />
                            <select id="modo" name="modo" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="MOVIL" {{ request('modo') == 'MOVIL' ? 'selected' : '' }}>App Móvil</option>
                                <option value="ONLINE" {{ request('modo') == 'ONLINE' ? 'selected' : '' }}>RFID (Online)</option>
                                <option value="OFFLINE" {{ request('modo') == 'OFFLINE' ? 'selected' : '' }}>RFID (Offline)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="estado_llegada" :value="__('Estado de Llegada')" />
                            <select id="estado_llegada" name="estado_llegada" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="a_tiempo" {{ request('estado_llegada') == 'a_tiempo' ? 'selected' : '' }}>A tiempo</option>
                                <option value="tarde" {{ request('estado_llegada') == 'tarde' ? 'selected' : '' }}>Tarde</option>
                                <option value="falta" {{ request('estado_llegada') == 'falta' ? 'selected' : '' }}>Falta</option>
                            </select>
                        </div>
                    </div>

                    {{-- 🔸 Botones --}}
                    <div class="flex flex-wrap justify-center md:justify-start gap-4">
                        <x-primary-button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                            {{ __('Buscar') }}
                        </x-primary-button>
                        <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg no-underline">
                            {{ __('Limpiar') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 🔹 Tabla de Asistencias --}}
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">
            {{ isset($isFiltered) && $isFiltered ? 'Resultados del Filtro' : 'Asistencias Recientes (15 por página)' }}
        </h3>

        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-4 px-6 text-left font-semibold">Fecha y Hora</th>
                        <th class="py-4 px-6 text-left font-semibold">UID</th>
                        <th class="py-4 px-6 text-left font-semibold">Nombre Completo</th>
                        <th class="py-4 px-6 text-left font-semibold">Carrera</th>
                        <th class="py-4 px-6 text-left font-semibold">Año</th>
                        <th class="py-4 px-6 text-left font-semibold">Materia</th>
                        <th class="py-4 px-6 text-left font-semibold">Modo</th>
                        <th class="py-4 px-6 text-left font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse ($asistencias as $asistencia)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-4 px-6">{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                            <td class="py-4 px-6 text-blue-600 font-medium">{{ $asistencia->uid }}</td>
                            <td class="py-4 px-6 font-medium">{{ $asistencia->nombre }}</td>
                            <td class="py-4 px-6">{{ $asistencia->estudiante->carrera ?? 'N/A' }}</td>
                            <td class="py-4 px-6">{{ $asistencia->estudiante->año ?? 'N/A' }}</td>
                            <td class="py-4 px-6">{{ $asistencia->curso->materia->nombre ?? 'N/A' }}</td>
                            <td class="py-4 px-6">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium 
                                    @if($asistencia->modo == 'MOVIL') bg-blue-100 text-blue-800
                                    @elseif($asistencia->modo == 'ONLINE') bg-green-100 text-green-800
                                    @elseif($asistencia->modo == 'OFFLINE') bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $asistencia->modo }}
                                </span>
                            </td>
                            <td class="py-4 px-6 capitalize">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                    @if($asistencia->estado_llegada == 'a_tiempo') bg-green-100 text-green-800
                                    @elseif($asistencia->estado_llegada == 'tarde') bg-yellow-100 text-yellow-800
                                    @elseif($asistencia->estado_llegada == 'falta') bg-red-100 text-red-800
                                    @endif">
                                    {{ str_replace('_', ' ', $asistencia->estado_llegada ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                No hay registros de asistencia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🔹 Paginación --}}
    @if(isset($isFiltered) && !$isFiltered && $asistencias instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow-sm p-4">
                {{ $asistencias->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Alpine.js (si no está en tu layout) --}}
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
