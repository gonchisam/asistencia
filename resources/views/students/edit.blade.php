@extends('layouts.app')

@section('content')
    {{-- Contenedor principal con el mismo estilo moderno del login --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-6xl mx-auto">
        
        {{-- Encabezado con botón de regreso --}}
        <div class="flex items-center justify-center mb-8 relative">
            {{-- Botón de regreso --}}
            <a href="{{ route('students.index') }}" 
               class="absolute left-0 bg-gray-600 hover:bg-gray-700 text-white p-3 rounded-full transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transform hover:scale-105"
               title="Regresar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            
            {{-- Título principal --}}
            <h1 class="text-3xl font-extrabold text-gray-900 text-center">
                <span class="text-blue-600">Editar Estudiante:</span><br>
                <span class="text-lg font-semibold text-gray-700 mt-2 block">
                    {{ $student->nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}
                </span>
            </h1>
        </div>

        {{-- Mensajes de estado --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded-lg text-green-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif
        
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Formulario --}}
        <form id="student-form" action="{{ route('students.update', $student->id) }}" method="POST" onsubmit="return convertirMayusculas()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Columna 1 --}}
                <div class="space-y-5">
                    {{-- 1. UID --}}
                    <div>
                        <x-input-label for="uid" :value="__('UID (Código RFID)')" class="text-gray-700 font-semibold mb-2" />
                        <div class="flex items-center space-x-3">
                            <x-text-input 
                                type="text" 
                                name="uid" 
                                id="uid" 
                                value="{{ old('uid', $student->uid) }}"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('uid') border-red-500 @enderror"
                                required
                            />
                            {{-- Botón para activar el modo de escaneo --}}
                            <button type="button" 
                                    id="poll-button"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105"
                                    title="Activar escaneo de UID">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <p id="uid-status" class="mt-2 text-sm font-medium {{ old('uid', $student->uid) ? 'text-green-600' : 'text-blue-600' }}">
                            {{ old('uid', $student->uid) ? 'UID actual cargado.' : 'Esperando escaneo RFID o ingreso manual.' }}
                        </p>
                        @error('uid')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Nombres --}}
                    <div>
                        <x-input-label for="nombre" :value="__('Nombres')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="text" 
                            name="nombre" 
                            id="nombre" 
                            value="{{ old('nombre', $student->nombre) }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('nombre') border-red-500 @enderror"
                            required
                        />
                        @error('nombre')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Primer Apellido --}}
                    <div>
                        <x-input-label for="primer_apellido" :value="__('Primer Apellido')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="text" 
                            name="primer_apellido" 
                            id="primer_apellido" 
                            value="{{ old('primer_apellido', $student->primer_apellido) }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('primer_apellido') border-red-500 @enderror"
                            required
                        />
                        @error('primer_apellido')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Segundo Apellido --}}
                    <div>
                        <x-input-label for="segundo_apellido" :value="__('Segundo Apellido (opcional)')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="text" 
                            name="segundo_apellido" 
                            id="segundo_apellido" 
                            value="{{ old('segundo_apellido', $student->segundo_apellido) }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('segundo_apellido') border-red-500 @enderror"
                        />
                        @error('segundo_apellido')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 5. Número de CI --}}
                    <div>
                        <x-input-label for="ci" :value="__('Número de CI')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="text" 
                            name="ci" 
                            id="ci" 
                            value="{{ old('ci', $student->ci) }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('ci') border-red-500 @enderror"
                            required
                        />
                        @error('ci')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Columna 2 --}}
                <div class="space-y-5">
                    {{-- 6. Celular --}}
                    <div>
                        <x-input-label for="celular" :value="__('Celular (opcional)')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="text" 
                            name="celular" 
                            id="celular" 
                            value="{{ old('celular', $student->celular) }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('celular') border-red-500 @enderror"
                        />
                        @error('celular')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 7. Correo Electrónico --}}
                    <div>
                        <x-input-label for="correo" :value="__('Correo Electrónico')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="email" 
                            name="correo" 
                            id="correo" 
                            value="{{ old('correo', $student->correo) }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('correo') border-red-500 @enderror"
                            required
                        />
                        @error('correo')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 8. Carrera --}}
                    <div>
                        <x-input-label for="carrera" :value="__('Carrera')" class="text-gray-700 font-semibold mb-2" />
                        <select name="carrera" id="carrera"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('carrera') border-red-500 @enderror"
                                required>
                            <option value="">Seleccione...</option>
                            <option value="Contabilidad" {{ old('carrera', $student->carrera) == 'Contabilidad' ? 'selected' : '' }}>CONTABILIDAD</option>
                            <option value="Secretariado" {{ old('carrera', $student->carrera) == 'Secretariado' ? 'selected' : '' }}>SECRETARIADO</option>
                            <option value="Mercadotecnia" {{ old('carrera', $student->carrera) == 'Mercadotecnia' ? 'selected' : '' }}>MERCADOTECNIA</option>
                            <option value="Sistemas" {{ old('carrera', $student->carrera) == 'Sistemas' ? 'selected' : '' }}>SISTEMAS</option>
                        </select>
                        @error('carrera')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 9. Año de estudio --}}
                    <div>
                        <x-input-label for="año" :value="__('Año de estudio')" class="text-gray-700 font-semibold mb-2" />
                        <select name="año" id="año"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('año') border-red-500 @enderror"
                                required>
                            <option value="">Seleccione...</option>
                            <option value="Primer Año" {{ old('año', $student->año) == 'Primer Año' ? 'selected' : '' }}>PRIMER AÑO</option>
                            <option value="Segundo Año" {{ old('año', $student->año) == 'Segundo Año' ? 'selected' : '' }}>SEGUNDO AÑO</option>
                            <option value="Tercer Año" {{ old('año', $student->año) == 'Tercer Año' ? 'selected' : '' }}>TERCER AÑO</option>
                        </select>
                        @error('año')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 10. Fecha de Nacimiento --}}
                    <div>
                        <x-input-label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="date" 
                            name="fecha_nacimiento" 
                            id="fecha_nacimiento" 
                            value="{{ old('fecha_nacimiento', $student->fecha_nacimiento ? $student->fecha_nacimiento->format('Y-m-d') : '') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('fecha_nacimiento') border-red-500 @enderror"
                            required 
                            min="1940-01-01" 
                            max="{{ now()->subYears(15)->format('Y-m-d') }}"
                        />
                        @error('fecha_nacimiento')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 11. Sexo --}}
                    <div>
                        <x-input-label for="sexo" :value="__('Sexo')" class="text-gray-700 font-semibold mb-2" />
                        <select name="sexo" id="sexo"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('sexo') border-red-500 @enderror"
                                required>
                            <option value="">Seleccione...</option>
                            <option value="MASCULINO" {{ old('sexo', $student->sexo) == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                            <option value="FEMENINO" {{ old('sexo', $student->sexo) == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                        </select>
                        @error('sexo')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Campos adicionales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-gray-200">
                {{-- Estado del estudiante --}}
                <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg">
                    <input type="hidden" name="estado" value="0">
                    <input type="checkbox" 
                           name="estado" 
                           id="estado" 
                           value="1" 
                           {{ old('estado', $student->estado) ? 'checked' : '' }}
                           class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-150 ease-in-out">
                    <x-input-label for="estado" :value="__('Estudiante Activo')" class="text-gray-700 font-semibold mb-0 cursor-pointer" />
                </div>

                {{-- Última acción registrada --}}
                <div>
                    <x-input-label for="last_action" :value="__('Última Acción Registrada')" class="text-gray-700 font-semibold mb-2" />
                    <select name="last_action" id="last_action"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('last_action') border-red-500 @enderror">
                        <option value="">Ninguna</option>
                        <option value="ENTRADA" {{ old('last_action', $student->last_action) == 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                        <option value="SALIDA" {{ old('last_action', $student->last_action) == 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    </select>
                    @error('last_action')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                <x-primary-button 
                    type="submit" 
                    id="submit-button"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105">
                    {{ __('Actualizar Estudiante') }}
                </x-primary-button>
                
                <a href="{{ route('students.index') }}" 
                   class="text-gray-600 hover:text-gray-800 font-semibold transition duration-150 ease-in-out underline hover:no-underline">
                    {{ __('Cancelar') }}
                </a>
            </div>
        </form>
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
                uidStatus.className = 'text-sm text-blue-600 font-medium';
                
                // Efecto visual para el botón
                pollButton.classList.add('bg-blue-700', 'ring-2', 'ring-blue-300');
            }

            function stopPolling() {
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
                // Remover efecto visual del botón
                pollButton.classList.remove('bg-blue-700', 'ring-2', 'ring-blue-300');
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
                            uidStatus.className = 'text-sm text-green-600 font-bold';
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener UID temporal:', error);
                        uidStatus.textContent = 'Error al escanear UID. Intente de nuevo.';
                        uidStatus.className = 'text-sm text-red-600 font-bold';
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
                
                if (this.value.trim() === '') {
                    uidStatus.textContent = 'Esperando escaneo RFID o ingreso manual.';
                    uidStatus.className = 'text-sm text-blue-600 font-medium';
                } else {
                    uidStatus.textContent = 'UID ingresado manualmente.';
                    uidStatus.className = 'text-sm text-gray-600 font-medium';
                }
            });

            const celularInput = document.getElementById('celular');
            if (celularInput) {
                celularInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[a-zA-Z]/g, '');
                });
            }

            // Efecto visual para campos al enfocar
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('ring-2', 'ring-blue-200', 'rounded-lg');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('ring-2', 'ring-blue-200', 'rounded-lg');
                });
            });
        });
    </script>
@endsection