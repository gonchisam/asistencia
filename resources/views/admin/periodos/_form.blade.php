@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">¡Error!</strong>
        <span class="block sm:inline">Hay problemas con los datos ingresados.</span>
        <ul class="mt-3 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="nombre" value="Nombre del Periodo *" />
        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $periodo->nombre ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hora_inicio" value="Hora de Inicio *" />
        <x-text-input id="hora_inicio" class="block mt-1 w-full" type="time" name="hora_inicio" :value="old('hora_inicio', $periodo->hora_inicio ?? '')" required />
        <x-input-error :messages="$errors->get('hora_inicio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hora_fin" value="Hora de Fin *" />
        <x-text-input id="hora_fin" class="block mt-1 w-full" type="time" name="hora_fin" :value="old('hora_fin', $periodo->hora_fin ?? '')" required />
        <x-input-error :messages="$errors->get('hora_fin')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="tolerancia_ingreso_minutos" value="Tolerancia de Ingreso (minutos) *" />
        <x-text-input id="tolerancia_ingreso_minutos" class="block mt-1 w-full" type="number" name="tolerancia_ingreso_minutos" 
                     :value="old('tolerancia_ingreso_minutos', $periodo->tolerancia_ingreso_minutos ?? 15)" 
                     min="0" max="60" required />
        <x-input-error :messages="$errors->get('tolerancia_ingreso_minutos')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('admin.periodos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
        Cancelar
    </a>
    <x-primary-button>
        {{ isset($periodo) && $periodo->exists ? 'Actualizar' : 'Crear' }} Periodo
    </x-primary-button>
</div>