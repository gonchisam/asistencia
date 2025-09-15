@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Registrar Nuevo Estudiante</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
        <form id="student-form" action="{{ route('students.store') }}" method="POST" onsubmit="return convertirMayusculas()" autocomplete="off">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label for="uid" class="block text-sm font-medium text-gray-700">UID (Código RFID):</label>
                        <div class="flex items-center space-x-2">
                            <input type="text" name="uid" id="uid" value="{{ old('uid') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('uid') border-red-500 @enderror" required>
                            {{-- Botón con ícono de refrescar (flechas circulares) --}}
                            <a href="{{ route('students.create') }}" class="inline-block align-middle bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-sm" id="refresh-button">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                            </a>
                        </div>
                        <p id="uid-status" class="mt-1 text-sm font-medium"></p>
                        @error('uid')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombres:</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror" required disabled>
                        @error('nombre')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="primer_apellido" class="block text-sm font-medium text-gray-700">Primer Apellido:</label>
                        <input type="text" name="primer_apellido" id="primer_apellido" value="{{ old('primer_apellido') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('primer_apellido') border-red-500 @enderror" required disabled>
                        @error('primer_apellido')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="segundo_apellido" class="block text-sm font-medium text-gray-700">Segundo Apellido (opcional):</label>
                        <input type="text" name="segundo_apellido" id="segundo_apellido" value="{{ old('segundo_apellido') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('segundo_apellido') border-red-500 @enderror" disabled>
                        @error('segundo_apellido')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ci" class="block text-sm font-medium text-gray-700">Número de CI:</label>
                        <input type="text" name="ci" id="ci" value="{{ old('ci') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ci') border-red-500 @enderror" required disabled>
                        @error('ci')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="carrera" class="block text-sm font-medium text-gray-700">Carrera:</label>
                        <select name="carrera" id="carrera"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('carrera') border-red-500 @enderror" required disabled>
                            <option value="">Seleccione...</option>
                            <option value="Contabilidad" {{ old('carrera') == 'Contabilidad' ? 'selected' : '' }}>CONTABILIDAD</option>
                            <option value="Secretariado" {{ old('carrera') == 'Secretariado' ? 'selected' : '' }}>SECRETARIADO</option>
                            <option value="Mercadotecnia" {{ old('carrera') == 'Mercadotecnia' ? 'selected' : '' }}>MERCADOTECNIA</option>
                            <option value="Sistemas" {{ old('carrera') == 'Sistemas' ? 'selected' : '' }}>SISTEMAS</option>
                        </select>
                        @error('carrera')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="año" class="block text-sm font-medium text-gray-700">Año:</label>
                        <select name="año" id="año"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('año') border-red-500 @enderror" required disabled>
                            <option value="">Seleccione...</option>
                            <option value="Primer Año" {{ old('año') == 'Primer Año' ? 'selected' : '' }}>PRIMER AÑO</option>
                            <option value="Segundo Año" {{ old('año') == 'Segundo Año' ? 'selected' : '' }}>SEGUNDO AÑO</option>
                            <option value="Tercer Año" {{ old('año') == 'Tercer Año' ? 'selected' : '' }}>TERCER AÑO</option>
                        </select>
                        @error('año')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento:</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_nacimiento') border-red-500 @enderror" required disabled>
                        @error('fecha_nacimiento')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo:</label>
                        <select name="sexo" id="sexo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sexo') border-red-500 @enderror" required disabled>
                            <option value="">Seleccione...</option>
                            <option value="MASCULINO" {{ old('sexo') == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                            <option value="FEMENINO" {{ old('sexo') == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                        </select>
                        @error('sexo')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="celular" class="block text-sm font-medium text-gray-700">Celular (opcional):</label>
                    <input type="text" name="celular" id="celular" value="{{ old('celular') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('celular') border-red-500 @enderror" disabled>
                    @error('celular')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700">Correo Electrónico:</label>
                    <input type="email" name="correo" id="correo" value="{{ old('correo') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('correo') border-red-500 @enderror" required disabled>
                    @error('correo')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" id="submit-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" disabled>
                    Guardar Estudiante
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
        const formFields = document.querySelectorAll('#student-form input:not([name="uid"]), #student-form select');

        let pollInterval = null;
        let formSubmitted = false;
        let isManualMode = false;

        function enableForm(state) {
            formFields.forEach(field => {
                field.disabled = !state;
            });
            submitButton.disabled = !state;
        }

        uidInput.disabled = false;
        celularInput.disabled = false;
        
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
                    uidStatus.className = 'text-sm text-red-500 font-bold';
                    uidInput.classList.add('line-through');
                    enableForm(false);
                } else {
                    uidStatus.textContent = 'UID disponible.';
                    uidStatus.className = 'text-sm text-green-500 font-bold';
                    uidInput.classList.remove('line-through');
                    enableForm(true);
                    document.getElementById('nombre').focus();
                }
            } catch (error) {
                console.error('Error:', error);
                uidStatus.textContent = 'Error al verificar el UID. Intente de nuevo.';
                uidStatus.className = 'text-sm text-red-500 font-bold';
                enableForm(false);
            }
        }
        
        function startPolling() {
            if (!pollInterval && !isManualMode) {
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

        startPolling();
        
        let typingTimer;
        const doneTypingInterval = 1500;

        // Convierte a mayúsculas al escribir
        uidInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            
            clearTimeout(typingTimer);
            typingTimer = setTimeout(checkUid, doneTypingInterval);
        });

        // Elimina letras y convierte a mayúsculas para el campo celular
        celularInput.addEventListener('input', function() {
            this.value = this.value.replace(/[a-zA-Z]/g, '');
        });

        // Detiene el polling y activa el modo manual al ENFOCAR el campo de UID
        uidInput.addEventListener('focus', function() {
            isManualMode = true;
            stopPolling();
            uidStatus.textContent = 'Modo manual activado.';
            uidStatus.className = 'text-sm text-blue-500 font-medium';
        });

        // Control de salida del campo
        uidInput.addEventListener('blur', function() {
            if (uidInput.value.trim() === '') {
                isManualMode = false;
                uidStatus.textContent = '';
                startPolling();
            } else {
                checkUid();
            }
        });

        document.addEventListener('load', function() {
            isManualMode = false;
            startPolling();
        });
    });
</script>
@endsection