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
            <h1 class="text-3xl font-extrabold text-gray-900">
                <span class="text-blue-600">Registrar Nuevo Estudiante</span>
            </h1>
        </div>

        {{-- Formulario --}}
        <form id="student-form" action="{{ route('students.store') }}" method="POST" onsubmit="return convertirMayusculas()" autocomplete="off">
            @csrf

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
                                value="{{ old('uid') }}"
                                class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('uid') border-red-500 @enderror"
                                required
                            />
                            <button type="button" 
                                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transform hover:scale-105"
                                    id="refresh-button"
                                    title="Actualizar UID">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <p id="uid-status" class="mt-2 text-sm font-medium"></p>
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
                            value="{{ old('nombre') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('nombre') border-red-500 @enderror"
                            required 
                            disabled
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
                            value="{{ old('primer_apellido') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('primer_apellido') border-red-500 @enderror"
                            required 
                            disabled
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
                            value="{{ old('segundo_apellido') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('segundo_apellido') border-red-500 @enderror"
                            disabled
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
                            value="{{ old('ci') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('ci') border-red-500 @enderror"
                            required 
                            disabled
                        />
                        @error('ci')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 6. Celular --}}
                    <div>
                        <x-input-label for="celular" :value="__('Celular (opcional)')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="text" 
                            name="celular" 
                            id="celular" 
                            value="{{ old('celular') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('celular') border-red-500 @enderror"
                            disabled
                        />
                        @error('celular')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Columna 2 --}}
                <div class="space-y-5">
                    {{-- 7. Correo Electrónico --}}
                    <div>
                        <x-input-label for="correo" :value="__('Correo Electrónico')" class="text-gray-700 font-semibold mb-2" />
                        <x-text-input 
                            type="email" 
                            name="correo" 
                            id="correo" 
                            value="{{ old('correo') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('correo') border-red-500 @enderror"
                            required 
                            disabled
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
                                required 
                                disabled>
                            <option value="">Seleccione...</option>
                            <option value="Contabilidad" {{ old('carrera') == 'Contabilidad' ? 'selected' : '' }}>CONTABILIDAD</option>
                            <option value="Secretariado" {{ old('carrera') == 'Secretariado' ? 'selected' : '' }}>SECRETARIADO</option>
                            <option value="Mercadotecnia" {{ old('carrera') == 'Mercadotecnia' ? 'selected' : '' }}>MERCADOTECNIA</option>
                            <option value="Sistemas" {{ old('carrera') == 'Sistemas' ? 'selected' : '' }}>SISTEMAS</option>
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
                                required 
                                disabled>
                            <option value="">Seleccione...</option>
                            <option value="Primer Año" {{ old('año') == 'Primer Año' ? 'selected' : '' }}>PRIMER AÑO</option>
                            <option value="Segundo Año" {{ old('año') == 'Segundo Año' ? 'selected' : '' }}>SEGUNDO AÑO</option>
                            <option value="Tercer Año" {{ old('año') == 'Tercer Año' ? 'selected' : '' }}>TERCER AÑO</option>
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
                            value="{{ old('fecha_nacimiento') }}"
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('fecha_nacimiento') border-red-500 @enderror"
                            required 
                            disabled 
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
                                required 
                                disabled>
                            <option value="">Seleccione...</option>
                            <option value="MASCULINO" {{ old('sexo') == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                            <option value="FEMENINO" {{ old('sexo') == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                        </select>
                        @error('sexo')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                <x-primary-button 
                    type="submit" 
                    id="submit-button"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105"
                    disabled>
                    {{ __('Guardar Estudiante') }}
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
            const campos = ['nombre', 'primer_apellido', 'segundo_apellido'];
            campos.forEach(id => {
                const campo = document.getElementById(id);
                if (campo) campo.value = campo.value.toUpperCase();
            });
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const uidInput = document.getElementById('uid');
            const celularInput = document.getElementById('celular');
            const uidStatus = document.getElementById('uid-status');
            const submitButton = document.getElementById('submit-button');
            const refreshButton = document.getElementById('refresh-button');
            const formFields = document.querySelectorAll(
                '#student-form input:not([name="uid"]), ' +
                '#student-form select'
            );

            let pollInterval = null;
            let formSubmitted = false;
            let isManualMode = false;

            function enableForm(state) {
                formFields.forEach(field => {
                    field.disabled = !state;
                });
                submitButton.disabled = !state;
                
                // Aplicar estilos visuales para estado deshabilitado/habilitado
                if (state) {
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.add('cursor-pointer');
                } else {
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.remove('cursor-pointer');
                }
            }

            uidInput.disabled = false;
            enableForm(false);

            function pollForUid() {
                if (formSubmitted || isManualMode) return;

                fetch('/api/get-uid')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.uid && uidInput.value === '') {
                            uidInput.value = data.uid;
                            stopPolling();
                            checkUid();
                        }
                    })
                    .catch(error => {
                        console.error('Error getting temporary UID:', error);
                    });
            }

            async function checkUid() {
                const uidValue = uidInput.value.trim();
                if (!uidValue) {
                    enableForm(false);
                    uidStatus.textContent = '';
                    if (!isManualMode) {
                        startPolling();
                    }
                    return;
                }

                try {
                    const response = await fetch('{{ route('students.check_uid') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ uid: uidValue })
                    });

                    const data = await response.json();

                    if (data.exists) {
                        uidStatus.textContent = '¡Este UID ya está en uso!';
                        uidStatus.className = 'text-sm text-red-600 font-bold';
                        uidInput.classList.add('border-red-500');
                        enableForm(false);
                    } else {
                        uidStatus.textContent = 'UID disponible. Complete los datos del estudiante.';
                        uidStatus.className = 'text-sm text-green-600 font-bold';
                        uidInput.classList.remove('border-red-500');
                        enableForm(true);
                        document.getElementById('nombre').focus();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    uidStatus.textContent = 'Error al verificar el UID. Intente de nuevo.';
                    uidStatus.className = 'text-sm text-red-600 font-bold';
                    enableForm(false);
                }
            }
            
            function startPolling() {
                if (!pollInterval && !isManualMode) {
                    uidStatus.textContent = 'Esperando lectura de UID RFID...';
                    uidStatus.className = 'text-sm text-blue-600 font-medium';
                    pollInterval = setInterval(pollForUid, 2000);
                }
            }

            function stopPolling() {
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
            }

            document.getElementById('student-form').addEventListener('submit', function() {
                formSubmitted = true;
                stopPolling();
            });

            refreshButton.addEventListener('click', function() {
                uidInput.value = '';
                uidInput.focus();
                isManualMode = true;
                stopPolling();
                checkUid();
            });

            startPolling();
            
            let typingTimer;
            const doneTypingInterval = 1500;

            uidInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
                
                clearTimeout(typingTimer);
                typingTimer = setTimeout(checkUid, doneTypingInterval);
            });

            celularInput.addEventListener('input', function() {
                this.value = this.value.replace(/[a-zA-Z]/g, '');
            });

            uidInput.addEventListener('focus', function() {
                isManualMode = true;
                stopPolling();
                uidStatus.textContent = 'Modo manual activado. Ingrese el UID manualmente.';
                uidStatus.className = 'text-sm text-blue-600 font-medium';
            });

            uidInput.addEventListener('blur', function() {
                if (uidInput.value.trim() === '') {
                    isManualMode = false;
                    uidStatus.textContent = '';
                    startPolling();
                } else {
                    checkUid();
                }
            });

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