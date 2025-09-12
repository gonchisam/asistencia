@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Generación de Reportes</h2>

        <p class="text-gray-700 mb-6">Utiliza los filtros a continuación para generar reportes de asistencia en formato PDF o Excel.</p>

        <form action="{{ route('reportes.pdf') }}" method="GET" class="mb-8">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Filtros de Reporte</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha Fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="estudiante_id" class="block text-sm font-medium text-gray-700">Estudiante:</label>
                    <select name="estudiante_id" id="estudiante_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todos los estudiantes</option>
                        @foreach ($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" {{ (string)request('estudiante_id') === (string)$estudiante->id ? 'selected' : '' }}>
                                {{ $estudiante->nombre }} (UID: {{ $estudiante->uid }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="accion" class="block text-sm font-medium text-gray-700">Acción:</label>
                    <select name="accion" id="accion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todas</option>
                        <option value="ENTRADA" {{ request('accion') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                        <option value="SALIDA" {{ request('accion') === 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    </select>
                </div>
                <div>
                    <label for="modo" class="block text-sm font-medium text-gray-700">Modo:</label>
                    <select name="modo" id="modo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todos</option>
                        <option value="WIFI" {{ request('modo') === 'WIFI' ? 'selected' : '' }}>WIFI</option>
                        <option value="SD" {{ request('modo') === 'SD' ? 'selected' : '' }}>SD</option>
                    </select>
                </div>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-150 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    Generar PDF
                </button>
                <button type="submit" form="excel-form" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    Generar Excel
                </button>
            </div>
        </form>

        {{-- Formulario oculto para el Excel que usa la misma lógica de filtros --}}
        <form id="excel-form" action="{{ route('reportes.excel') }}" method="GET" style="display: none;">
            <input type="hidden" name="fecha_inicio" id="excel_fecha_inicio">
            <input type="hidden" name="fecha_fin" id="excel_fecha_fin">
            <input type="hidden" name="estudiante_id" id="excel_estudiante_id">
            <input type="hidden" name="accion" id="excel_accion">
            <input type="hidden" name="modo" id="excel_modo">
        </form>

        {{-- Script para sincronizar los valores del formulario al enviar para Excel --}}
        @push('scripts')
        <script>
            document.querySelector('form').addEventListener('submit', function(event) {
                // Si el botón presionado es el de PDF, no necesitamos sincronizar nada para Excel
                if (event.submitter && event.submitter.form === document.getElementById('excel-form')) {
                    document.getElementById('excel_fecha_inicio').value = document.getElementById('fecha_inicio').value;
                    document.getElementById('excel_fecha_fin').value = document.getElementById('fecha_fin').value;
                    document.getElementById('excel_estudiante_id').value = document.getElementById('estudiante_id').value;
                    document.getElementById('excel_accion').value = document.getElementById('accion').value;
                    document.getElementById('excel_modo').value = document.getElementById('modo').value;
                }
            });
        </script>
        @endpush
    </div>
@endsection