@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Registrar Nuevo Estudiante</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
        <form id="student-form" action="{{ route('students.store') }}" method="POST" onsubmit="return convertirMayusculas()">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label for="uid" class="block text-sm font-medium text-gray-700">UID (Código RFID):</label>
                        <input type="text" name="uid" id="uid" value="{{ old('uid') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('uid') border-red-500 @enderror" required>
                        <p id="uid-status" class="mt-1 text-sm font-medium"></p>
                        @error('uid')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombres:</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror" required>
                        @error('nombre')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="primer_apellido" class="block text-sm font-medium text-gray-700">Primer Apellido:</label>
                        <input type="text" name="primer_apellido" id="primer_apellido" value="{{ old('primer_apellido') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('primer_apellido') border-red-500 @enderror" required>
                        @error('primer_apellido')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="segundo_apellido" class="block text-sm font-medium text-gray-700">Segundo Apellido (opcional):</label>
                        <input type="text" name="segundo_apellido" id="segundo_apellido" value="{{ old('segundo_apellido') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('segundo_apellido') border-red-500 @enderror">
                        @error('segundo_apellido')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ci" class="block text-sm font-medium text-gray-700">Número de CI:</label>
                        <input type="text" name="ci" id="ci" value="{{ old('ci') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('ci') border-red-500 @enderror" required>
                        @error('ci')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="carrera" class="block text-sm font-medium text-gray-700">Carrera:</label>
                        <select name="carrera" id="carrera"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('carrera') border-red-500 @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="Contabilidad" {{ old('carrera') == 'Contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                            <option value="Secretariado" {{ old('carrera') == 'Secretariado' ? 'selected' : '' }}>Secretariado</option>
                            <option value="Mercadotecnia" {{ old('carrera') == 'Mercadotecnia' ? 'selected' : '' }}>Mercadotecnia</option>
                            <option value="Sistemas" {{ old('carrera') == 'Sistemas' ? 'selected' : '' }}>Sistemas</option>
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
                            <option value="Primer Año" {{ old('año') == 'Primer Año' ? 'selected' : '' }}>Primer Año</option>
                            <option value="Segundo Año" {{ old('año') == 'Segundo Año' ? 'selected' : '' }}>Segundo Año</option>
                            <option value="Tercer Año" {{ old('año') == 'Tercer Año' ? 'selected' : '' }}>Tercer Año</option>
                        </select>
                        @error('año')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento:</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fecha_nacimiento') border-red-500 @enderror" required>
                        @error('fecha_nacimiento')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo:</label>
                        <select name="sexo" id="sexo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sexo') border-red-500 @enderror" required>
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('celular') border-red-500 @enderror">
                    @error('celular')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700">Correo Electrónico:</label>
                    <input type="email" name="correo" id="correo" value="{{ old('correo') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('correo') border-red-500 @enderror" required>
                    @error('correo')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" id="submit-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
        const uidStatus = document.getElementById('uid-status');
        const submitButton = document.getElementById('submit-button');
        const form = document.getElementById('student-form');

        let typingTimer;
        const doneTypingInterval = 500; // 0.5 segundos

        uidInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            if (this.value) {
                uidStatus.textContent = 'Verificando...';
                uidStatus.classList.remove('text-red-500', 'text-green-500');
                submitButton.disabled = true; // Deshabilita el botón mientras se verifica
                typingTimer = setTimeout(checkUid, doneTypingInterval);
            } else {
                uidStatus.textContent = '';
                submitButton.disabled = false;
            }
        });

        async function checkUid() {
            const uidValue = uidInput.value.trim();
            if (!uidValue) return;

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
                    uidInput.classList.add('line-through');
                    uidInput.classList.add('text-red-500');
                    uidStatus.textContent = '¡Este UID ya está en uso!';
                    uidStatus.classList.add('text-red-500');
                    submitButton.disabled = true;
                } else {
                    uidInput.classList.remove('line-through');
                    uidInput.classList.remove('text-red-500');
                    uidStatus.textContent = 'UID disponible.';
                    uidStatus.classList.add('text-green-500');
                    submitButton.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                uidStatus.textContent = 'Error al verificar el UID. Intente de nuevo.';
                uidStatus.classList.add('text-red-500');
                submitButton.disabled = false;
            }
        }
    });
</script>
@endsection