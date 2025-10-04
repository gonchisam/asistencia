@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex items-center justify-center mb-6"> {{-- Added a flex container here --}}
        <a href="{{ route('students.index') }}" class="mr-4 bg-gray-200 hover:bg-gray-300 text-gray-800 p-2 rounded-full transition duration-150 flex items-center justify-center" title="Regresar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">
            Editar Estudiante: {{ $student->nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
        {{-- Muestra los mensajes de éxito o error del servidor --}}
        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form id="student-form" action="{{ route('students.update', $student->id) }}" method="POST" onsubmit="return convertirMayusculas()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label for="uid" class="block text-sm font-medium text-gray-700">UID (Código RFID):</label>
                        <div class="flex items-center space-x-2">
                            <input type="text" name="uid" id="uid" value="{{ old('uid', $student->uid) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('uid') border-red-500 @enderror" required>
                            {{-- Botón para activar el modo de escaneo --}}
                            <a href="#" id="poll-button" class="inline-block align-middle bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-sm" title="Activar escaneo de UID">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </a>
                        </div>
                        <p id="uid-status" class="mt-1 text-sm font-medium text-gray-500">
                            {{ old('uid', $student->uid) ? 'UID actual cargado.' : 'Esperando escaneo RFID o ingreso manual.' }}
                        </p>
                        @error('uid')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombres:</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $student->nombre) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror" required>
                        @error('nombre')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="primer_apellido" class="block text-sm font-medium text-gray-700">Primer Apellido:</label>
                        <input type="text" name="primer_apellido" id="primer_apellido" value="{{ old('primer_apellido', $student->primer_apellido) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('primer_apellido') border-red-500 @enderror" required>
                        @error('primer_apellido')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="segundo_apellido" class="block text-sm font-medium text-gray-700">Segundo Apellido (opcional):</label>
                        <input type="text" name="segundo_apellido" id="segundo_apellido" value="{{ old('segundo_apellido', $student->segundo_apellido) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('segundo_apellido') border-red-500 @enderror">
                        @error('segundo_apellido')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ci" class="block text-sm font-medium text-gray-700">Número de CI:</label>
                        <input type="text" name="ci" id="ci" value="{{ old('ci', $student->ci) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ci') border-red-500 @enderror" required>
                        @error('ci')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="celular" class="block text-sm font-medium text-gray-700">Celular (opcional):</label>
                        <input type="text" name="celular" id="celular" value="{{ old('celular', $student->celular) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('celular') border-red-500 @enderror">
                        @error('celular')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700">Correo Electrónico:</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo', $student->correo) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('correo') border-red-500 @enderror" required>
                        @error('correo')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="carrera" class="block text-sm font-medium text-gray-700">Carrera:</label>
                        <select name="carrera" id="carrera"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('carrera') border-red-500 @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="Contabilidad" {{ old('carrera', $student->carrera) == 'Contabilidad' ? 'selected' : '' }}>CONTABILIDAD</option>
                            <option value="Secretariado" {{ old('carrera', $student->carrera) == 'Secretariado' ? 'selected' : '' }}>SECRETARIADO</option>
                            <option value="Mercadotecnia" {{ old('carrera', $student->carrera) == 'Mercadotecnia' ? 'selected' : '' }}>MERCADOTECNIA</option>
                            <option value="Sistemas" {{ old('carrera', $student->carrera) == 'Sistemas' ? 'selected' : '' }}>SISTEMAS</option>
                        </select>
                        @error('carrera')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="año" class="block text-sm font-medium text-gray-700">Año:</label>
                        <select name="año" id="año"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('año') border-red-500 @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="Primer Año" {{ old('año', $student->año) == 'Primer Año' ? 'selected' : '' }}>PRIMER AÑO</option>
                            <option value="Segundo Año" {{ old('año', $student->año) == 'Segundo Año' ? 'selected' : '' }}>SEGUNDO AÑO</option>
                            <option value="Tercer Año" {{ old('año', $student->año) == 'Tercer Año' ? 'selected' : '' }}>TERCER AÑO</option>
                        </select>
                        @error('año')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento:</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $student->fecha_nacimiento ? $student->fecha_nacimiento->format('Y-m-d') : '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_nacimiento') border-red-500 @enderror" 
                            required 
                            min="1940-01-01" 
                            max="{{ now()->subYears(15)->format('Y-m-d') }}">
                        @error('fecha_nacimiento')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo:</label>
                        <select name="sexo" id="sexo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sexo') border-red-500 @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="MASCULINO" {{ old('sexo', $student->sexo) == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                            <option value="FEMENINO" {{ old('sexo', $student->sexo) == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                        </select>
                        @error('sexo')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="flex items-center">
                    {{-- Campo oculto para asegurar que el estado se envía cuando el checkbox está desmarcado --}}
                    <input type="hidden" name="estado" value="0">
                    <input type="checkbox" name="estado" id="estado" value="1" {{ old('estado', $student->estado) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="estado" class="ml-2 block text-sm font-medium text-gray-700">Estudiante Activo</label>
                </div>
                <div>
                    <label for="last_action" class="block text-sm font-medium text-gray-700">Última Acción Registrada:</label>
                    <select name="last_action" id="last_action"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('last_action') border-red-500 @enderror">
                        <option value="">Ninguna</option>
                        <option value="ENTRADA" {{ old('last_action', $student->last_action) == 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                        <option value="SALIDA" {{ old('last_action', $student->last_action) == 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    </select>
                    @error('last_action')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" id="submit-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Actualizar Estudiante
                </button>
                <a href="{{ route('students.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function convertirMayusculas() {
        const campos = ['uid', 'nombre', 'primer_apellido', 'segundo_apellido', 'ci'];
        campos.forEach(id => {
            const campo = document.getElementById(id);
            if (campo && campo.value) {
                campo.value = campo.value.toUpperCase();
            }
        });
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const uidInput = document.getElementById('uid');
        const pollButton = document.getElementById('poll-button');
        const uidStatus = document.getElementById('uid-status');
        let pollInterval = null;

        function startPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
            }
            pollInterval = setInterval(pollForUid, 1000);
            uidStatus.textContent = 'Esperando escaneo RFID...';
            uidStatus.className = 'text-sm text-gray-500 font-medium';
        }

        function stopPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        }

        function pollForUid() {
            fetch('/api/get-uid')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Respuesta de red no ok al obtener UID temporal');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.uid && uidInput.value !== data.uid) {
                        uidInput.value = data.uid;
                        stopPolling();
                        uidStatus.textContent = '¡UID escaneado con éxito! Ahora puedes editar los datos.';
                        uidStatus.className = 'text-sm text-green-500 font-bold';
                    }
                })
                .catch(error => {
                    console.error('Error al obtener UID temporal:', error);
                    uidStatus.textContent = 'Error al escanear UID. Intente de nuevo.';
                    uidStatus.className = 'text-sm text-red-500 font-bold';
                });
        }

        pollButton.addEventListener('click', function(e) {
            e.preventDefault();
            uidInput.value = '';
            startPolling();
        });

        uidInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            stopPolling();
            uidStatus.textContent = '';
        });

        const celularInput = document.getElementById('celular');
        if (celularInput) {
            celularInput.addEventListener('input', function() {
                this.value = this.value.replace(/[a-zA-Z]/g, '');
            });
        }
    });
</script>
@endsection