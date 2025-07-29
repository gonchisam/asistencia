@extends('layouts.app') {{-- Importante: extiende el layout principal --}}

@section('content')
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Editar Estudiante: {{ $student->nombre }}</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('students.update', $student->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Usar método PUT para actualizar --}}

                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $student->nombre) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nombre') border-red-500 @enderror" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="uid" class="block text-gray-700 text-sm font-bold mb-2">UID (Código RFID):</label>
                    <input type="text" name="uid" id="uid" value="{{ old('uid', $student->uid) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('uid') border-red-500 @enderror" required>
                    @error('uid')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campo para el estado (eliminación lógica) --}}
                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="estado" id="estado" value="1" {{ old('estado', $student->estado) ? 'checked' : '' }} class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="estado" class="block text-gray-700 text-sm font-bold">Estudiante Activo</label>
                </div>

                {{-- Campo para la última acción (opcional, para edición manual) --}}
                <div class="mb-4">
                    <label for="last_action" class="block text-gray-700 text-sm font-bold mb-2">Última Acción Registrada:</label>
                    <select name="last_action" id="last_action" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('last_action') border-red-500 @enderror">
                        <option value="">Ninguna</option>
                        <option value="ENTRADA" {{ old('last_action', $student->last_action) == 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                        <option value="SALIDA" {{ old('last_action', $student->last_action) == 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    </select>
                    @error('last_action')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Actualizar Estudiante
                    </button>
                    <a href="{{ route('students.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
@endsection