@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">¡Error!</strong>
        <ul class="mt-3 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <x-input-label for="nombre" value="Nombre de la Materia" />
        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $materia->nombre)" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="carrera" value="Carrera" />
        <select name="carrera" id="carrera" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Seleccione una carrera...</option>
            @foreach ($carreras as $carrera)
                <option value="{{ $carrera }}" @selected(old('carrera', $materia->carrera) == $carrera)>
                    {{ $carrera }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('carrera')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ano_cursado" value="Año Cursado" />
        <select name="ano_cursado" id="ano_cursado" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Seleccione un año...</option>
            @foreach ($anos as $ano)
                <option value="{{ $ano }}" @selected(old('ano_cursado', $materia->ano_cursado) == $ano)>
                    {{ $ano }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('ano_cursado')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('admin.materias.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
        Cancelar
    </a>
    <x-primary-button>
        Guardar Materia
    </x-primary-button>
</div>