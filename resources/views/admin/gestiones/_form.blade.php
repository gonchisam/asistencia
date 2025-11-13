@csrf
<div class="space-y-8">
    
    {{-- SECCIÓN 1: Datos Principales --}}
    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">📅 Configuración General</h3>
        
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-input-label for="nombre" value="Nombre de la Gestión" />
                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" 
                    placeholder="Ej: Gestión 2025" :value="old('nombre', $gestione->nombre ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="fecha_inicio" value="Inicio de Clases (Primer día)" />
                    <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1 block w-full cursor-pointer" 
                        :value="old('fecha_inicio', isset($gestione) ? $gestione->fecha_inicio->format('Y-m-d') : '')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('fecha_inicio')" />
                </div>
                <div>
                    <x-input-label for="fecha_fin" value="Fin de Clases (Último día)" />
                    <x-text-input id="fecha_fin" name="fecha_fin" type="date" class="mt-1 block w-full cursor-pointer" 
                        :value="old('fecha_fin', isset($gestione) ? $gestione->fecha_fin->format('Y-m-d') : '')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('fecha_fin')" />
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 2: Configuración Automática del Calendario (Solo al CREAR) --}}
    @if(!isset($gestione)) 
    <div class="bg-blue-50 p-5 rounded-lg border border-blue-200 shadow-sm">
        <div class="flex items-center gap-2 mb-4 border-b border-blue-200 pb-2">
            <span class="text-xl">🇧🇴</span>
            <h3 class="text-lg font-bold text-blue-900">Fechas Especiales (Configuración Rápida)</h3>
        </div>
        <p class="text-sm text-blue-700 mb-6 bg-blue-100 p-3 rounded">
            ℹ️ Ingresa estas fechas ahora para que el sistema genere automáticamente los días no laborables en el calendario.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-4 rounded border border-blue-100">
                <h4 class="font-bold text-md text-gray-700 mb-3 flex items-center gap-2">🎭 Feriado de Carnaval</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Lunes de Carnaval</label>
                        <input type="date" name="lunes_carnaval" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Martes de Carnaval</label>
                        <input type="date" name="martes_carnaval" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded border border-blue-100">
                <h4 class="font-bold text-md text-gray-700 mb-3 flex items-center gap-2">❄️ Vacación de Invierno</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Fecha Inicio (Lunes)</label>
                        <input type="date" name="vacacion_inicio" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Fecha Fin (Viernes)</label>
                        <input type="date" name="vacacion_fin" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Checkbox Activo --}}
    <div class="flex items-start p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex h-6 items-center">
            <input id="actual" name="actual" type="checkbox" value="1" 
                class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                {{ old('actual', $gestione->actual ?? false) ? 'checked' : '' }}>
        </div>
        <div class="ml-3 text-sm leading-6">
            <label for="actual" class="font-bold text-gray-900 text-base">Establecer como Gestión Activa</label>
            <p id="offers-description" class="text-gray-500">Si marcas esta opción, el sistema empezará a trabajar con esta gestión inmediatamente y desactivará la anterior.</p>
        </div>
    </div>
</div>