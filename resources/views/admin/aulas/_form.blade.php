<div class="space-y-6">
    <!-- Campo Nombre -->
    <div>
        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Aula *</label>
        <input type="text" name="nombre" id="nombre" 
               value="{{ old('nombre', $aula->nombre ?? '') }}"
               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
               required>
        @error('nombre')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Campo Código -->
    <div>
        <label for="codigo" class="block text-sm font-medium text-gray-700">Código</label>
        <input type="text" name="codigo" id="codigo" 
               value="{{ old('codigo', $aula->codigo ?? '') }}"
               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        @error('codigo')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Campo Ubicación -->
    <div>
        <label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicación</label>
        <input type="text" name="ubicacion" id="ubicacion" 
               value="{{ old('ubicacion', $aula->ubicacion ?? '') }}"
               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        @error('ubicacion')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Botones -->
    <div class="flex justify-end space-x-3 pt-4">
        <a href="{{ route('admin.aulas.index') }}" 
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
            Cancelar
        </a>
        <button type="submit" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ isset($aula) && $aula->exists ? 'Actualizar' : 'Crear' }} Aula
        </button>
    </div>
</div>