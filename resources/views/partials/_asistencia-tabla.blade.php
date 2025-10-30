{{-- resources/views/partials/_asistencia-tabla.blade.php --}}

{{-- Título de la sección --}}
<h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">
    @if(isset($isFiltered) && $isFiltered)
        Resultados del Filtro
    @else
        Asistencias Recientes (Mostrando 15 por página)
    @endif
</h3>

{{-- La Tabla de Asistencia --}}
<div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <table class="min-w-full bg-white">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-4 px-6 text-left font-semibold">Fecha y Hora</th>
                <th class="py-4 px-6 text-left font-semibold">UID</th>
                <th class="py-4 px-6 text-left font-semibold">Nombre Completo</th>
                <th class="py-4 px-6 text-left font-semibold">Carrera</th>
                <th class="py-4 px-6 text-left font-semibold">Año Cursado</th>
                <th class="py-4 px-6 text-left font-semibold">Materia</th>
                <th class="py-4 px-6 text-left font-semibold">Modo</th>
                <th class="py-4 px-6 text-left font-semibold">Estado Llegada</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            @forelse ($asistencias as $asistencia)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-4 px-6 text-left whitespace-nowrap">
                        {{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="py-4 px-6 text-left">
                        <span class="font-medium text-blue-600">{{ $asistencia->uid }}</span>
                    </td>
                    <td class="py-4 px-6 text-left font-medium text-gray-900">{{ $asistencia->nombre }}</td>
                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->carrera ?? 'N/A' }}</td>
                    <td class="py-4 px-6 text-left">{{ $asistencia->estudiante->año ?? 'N/A' }}</td>
                    <td class="py-4 px-6 text-left">
                        {{ $asistencia->curso->materia->nombre ?? 'N/A' }}
                    </td>
                    <td class="py-4 px-6 text-left">
                         <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($asistencia->modo == 'MOVIL') bg-blue-100 text-blue-800
                            @elseif($asistencia->modo == 'ONLINE') bg-green-100 text-green-800
                            @elseif($asistencia->modo == 'OFFLINE') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $asistencia->modo }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-left">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium capitalize
                            @if($asistencia->estado_llegada == 'a_tiempo') bg-green-100 text-green-800
                            @elseif($asistencia->estado_llegada == 'tarde') bg-yellow-100 text-yellow-800
                            @elseif($asistencia->estado_llegada == 'falta') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $asistencia->estado_llegada ? str_replace('_', ' ', $asistencia->estado_llegada) : 'N/A' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg text-gray-600">No hay registros de asistencia</p>
                            <p class="text-sm text-gray-500 mt-1">No se encontraron resultados que coincidan con los filtros aplicados.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación (Se actualiza también) --}}
@if(isset($isFiltered) && !$isFiltered && $asistencias instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="mt-6 flex justify-center">
        <div class="bg-white rounded-lg shadow-sm p-4">
            {{ $asistencias->appends(request()->query())->links() }}
        </div>
    </div>
@endif