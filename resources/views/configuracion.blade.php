@extends('layouts.app') {{-- Importante: extiende el layout principal --}}

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Configuración del Sistema</h2>
        <p class="text-gray-700">Aquí podrás gestionar la configuración del WiFi, ajustes del RTC, parámetros del servidor, etc.</p>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="font-semibold text-gray-800">Ejemplo de Función: Configuración WiFi</h4>
            <p class="text-sm text-gray-600">SSID: <input type="text" class="border rounded px-2 py-1 mt-1"></p>
            <p class="text-sm text-gray-600">Contraseña: <input type="password" class="border rounded px-2 py-1 mt-1"></p>
            <button class="mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150">Guardar WiFi</button>
        </div>
        {{-- Añade aquí más opciones de configuración --}}
    </div>
@endsection