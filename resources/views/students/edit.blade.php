@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Editar Estudiante: {{ $student->nombre }}</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
        <form action="{{ route('students.update', $student->id) }}" method="POST" onsubmit="return convertirMayusculas()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label for="uid" class="block text-sm font-medium text-gray-700">UID (Código RFID):</label>
                        <input type="text" name="uid" id="uid" value="{{ old('uid', $student->uid) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('uid') border-red-500 @enderror" required>
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
                        <label for="carrera" class="block text-sm font-medium text-gray-700">Carrera:</label>
                        <select name="carrera" id="carrera"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('carrera') border-red-500 @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="Contabilidad" {{ old('carrera', $student->carrera) == 'Contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                            <option value="Secretariado" {{ old('carrera', $student->carrera) == 'Secretariado' ? 'selected' : '' }}>Secretariado</option>
                            <option value="Mercadotecnia" {{ old('carrera', $student->carrera) == 'Mercadotecnia' ? 'selected' : '' }}>Mercadotecnia</option>
                            <option value="Sistemas" {{ old('carrera', $student->carrera) == 'Sistemas' ? 'selected' : '' }}>Sistemas</option>
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
                            <option value="Primer Año" {{ old('año', $student->año) == 'Primer Año' ? 'selected' : '' }}>Primer Año</option>
                            <option value="Segundo Año" {{ old('año', $student->año) == 'Segundo Año' ? 'selected' : '' }}>Segundo Año</option>
                            <option value="Tercer Año" {{ old('año', $student->año) == 'Tercer Año' ? 'selected' : '' }}>Tercer Año</option>
                        </select>
                        @error('año')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento:</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $student->fecha_nacimiento) }}"
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
                            <option value="MASCULINO" {{ old('sexo', $student->sexo) == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                            <option value="FEMENINO" {{ old('sexo', $student->sexo) == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="flex items-center">
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
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
    const campos = ['nombre', 'primer_apellido', 'segundo_apellido'];
    campos.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) campo.value = campo.value.toUpperCase();
    });
    return true;
}
</script>
@endsection