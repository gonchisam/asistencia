@extends('layouts.app') {{-- Importante: extiende el layout principal --}}

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Generación de Reportes</h2>
        <p class="text-gray-700">Desde aquí podrás generar reportes de asistencia por fechas, estudiantes o módulos.</p>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="font-semibold text-gray-800">Ejemplo de Función: Reporte por Fecha</h4>
            <p class="text-sm text-gray-600">Fecha Inicio: <input type="date" class="border rounded px-2 py-1 mt-1"></p>
            <p class="text-sm text-gray-600">Fecha Fin: <input type="date" class="border rounded px-2 py-1 mt-1"></p>
            <button class="mt-3 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-150">Generar Reporte</button>
        </div>
        {{-- Añade aquí más opciones de reportes --}}
    </div>
@endsection