<section>
    {{-- Contenedor y sombreado para dar un aspecto más "panel" --}}
    <div class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 transform transition-all duration-300 hover:shadow-3xl">

        {{-- Encabezado con título y botón en la misma línea --}}
        <header class="flex flex-col xs:flex-row justify-between items-start xs:items-center gap-4 mb-6 pb-4 border-b border-gray-200">
            
            {{-- Título --}}
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-extrabold text-blue-600">
                    {{ __('Docentes Registrados') }}
                </h2>
            </div>
            
            {{-- Botón de Importar --}}
            <div class="flex-shrink-0">
                <button
                    type="button"
                    onclick="window.location.href='{{ route('profile.showImportForm') }}'"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 shadow-md justify-center"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3 3m3-3l3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                    </svg>
                    {{ __('Importar Docentes') }}
                </button>
            </div>
        </header>

        {{-- La tabla de docentes --}}
        <div class="pt-2 space-y-4">
            @if($docentes->isEmpty())
                <p class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center text-gray-500 font-medium">{{ __('No se encontraron docentes registrados.') }}</p>
            @else
                {{-- Tabla con estilos más limpios y bordes --}}
                <div class="overflow-x-auto shadow-lg rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    {{ __('Nombre') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    {{ __('Email') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($docentes as $docente)
                                <tr class="hover:bg-blue-50 transition duration-150 ease-in-out">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $docente->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $docente->email }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>