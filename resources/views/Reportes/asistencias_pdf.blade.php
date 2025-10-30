<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencias</title>
    <style>
        /* --- ESTILOS GENERALES Y DE TABLA --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9pt; padding: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: middle; }
        th { background-color: #4A5568; color: white; font-weight: bold; font-size: 8pt; text-align: center; }
        td { font-size: 8pt; }
        .total-row { font-weight: bold; background-color: #e8f5e9; }
        .capitalize { text-transform: capitalize; }
        .text-center { text-align: center; }

        /* --- ESTILOS PARA ENCABEZADO --- */
        .encabezado { width: 100%; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .encabezado table { width: 100%; border: none; margin-top: 0; margin-bottom: 0; }
        .encabezado table td { border: none; padding: 0; vertical-align: middle; }
        .logo-container { width: 200px; padding-right: 20px; }
        .logo { max-width: 100%; height: auto; }
        .encabezado-content { text-align: center; }
        .encabezado-content h1 { font-size: 16pt; font-weight: bold; margin-bottom: 3px; text-align: center; }
        .encabezado-content h2 { font-size: 14pt; margin-bottom: 3px; text-align: center; }
        .encabezado-content .info { font-size: 10pt; margin-top: 8px; font-weight: bold; text-align: center; }

        /* --- Estilos para filtros --- */
        .filters { margin-bottom: 15px; border: 1px solid #eee; padding: 8px; background-color: #f9f9f9; font-size: 9pt; }
        .filters p { margin: 4px 0; }
        .filters strong { display: inline-block; min-width: 100px; }

        /* --- Estilos Pie de página --- */
         .pie-pagina { margin-top: 20px; text-align: center; font-size: 8pt; color: #718096; }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="encabezado">
        <table>
            <tr>
                <td class="logo-container">
                    <img src="{{ public_path('img/logoincos.png') }}" class="logo" alt="Logo INCOS">
                </td>
                <td class="encabezado-content">
                    <h1>INSTITUTO TÉCNICO NACIONAL DE COMERCIO</h1>
                    <h2>FEDERICO ALVAREZ PLATA "NOCTURNO"</h2>
                    <div class="info">
                        REPORTE DE ASISTENCIAS
                        @if($request->filled('fecha_desde') && $request->filled('fecha_fin'))
                            <br>
                            <span style="font-size: 8pt; font-weight: normal;">
                                (Del {{ \Carbon\Carbon::parse($request->fecha_desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') }})
                            </span>
                        @elseif($request->filled('fecha_desde'))
                            <br>
                            <span style="font-size: 8pt; font-weight: normal;">
                                (Desde el {{ \Carbon\Carbon::parse($request->fecha_desde)->format('d/m/Y') }})
                            </span>
                        @elseif($request->filled('fecha_fin'))
                            <br>
                            <span style="font-size: 8pt; font-weight: normal;">
                                (Hasta el {{ \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') }})
                            </span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Filtros Aplicados --}}
    <div class="filters">
        <p><strong>Generado el:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        @auth
        <p><strong>Generado por:</strong> {{ Auth::user()->name }} ({{ Auth::user()->role ?? 'Rol no definido' }})</p>
        @endauth

        {{-- Mostrar solo filtros aplicados --}}
        @if($request->filled('fecha_desde'))
            <p><strong>Fecha Inicio:</strong> {{ \Carbon\Carbon::parse($request->fecha_desde)->format('d/m/Y') }}</p>
        @endif
        @if($request->filled('fecha_fin'))
            <p><strong>Fecha Fin:</strong> {{ \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') }}</p>
        @endif
        @if($request->filled('ci'))
            <p><strong>CI:</strong> {{ $request->ci }}</p>
        @endif
        @if($request->filled('carrera'))
            <p><strong>Carrera:</strong> {{ $request->carrera }}</p>
        @endif
        @if($request->filled('año'))
            <p><strong>Año:</strong> {{ $request->año }}</p>
        @endif
        {{-- --- INICIO: NUEVO FILTRO EN PDF --- --}}
        @if($request->filled('paralelo'))
            <p><strong>Paralelo:</strong> {{ $request->paralelo }}</p>
        @endif
        {{-- --- FIN: NUEVO FILTRO EN PDF --- --}}
        @if($request->filled('materia_id'))
            <p><strong>Materia:</strong> {{ \App\Models\Materia::find($request->materia_id)->nombre ?? 'N/A' }}</p>
        @endif
        @if($request->filled('modo'))
            <p><strong>Modo:</strong> {{ $request->modo }}</p>
        @endif
        @if($request->filled('estado_llegada'))
            <p><strong>Estado Llegada:</strong> <span class="capitalize">{{ str_replace('_', ' ', $request->estado_llegada) }}</span></p>
        @endif
        @php
            $hasFilters = false;
            foreach(request()->except('page') as $key => $value) {
                if(!is_null($value) && $value !== '') {
                    $hasFilters = true;
                    break;
                }
            }
        @endphp
        @if(!$hasFilters)
            <p><strong>Filtros:</strong> Ninguno aplicado.</p>
        @endif
    </div>

    {{-- Tabla de Resultados (¡MODIFICADA!) --}}
    <table>
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>CI</th>
                <th>Nombre Completo</th>
                <th>Carrera</th>
                <th>Año</th>
                {{-- --- INICIO: NUEVA COLUMNA --- --}}
                <th>Paralelo</th>
                {{-- --- FIN: NUEVA COLUMNA --- --}}
                <th>Materia</th>
                <th>Modo</th>
                <th>Estado Llegada</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($asistencias as $asistencia)
                <tr>
                    <td>{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                    <td class="text-center">{{ $asistencia->estudiante->ci ?? 'N/A' }}</td>
                    <td>{{ $asistencia->nombre }}</td>
                    <td class="text-center">{{ $asistencia->estudiante->carrera ?? 'N/A' }}</td>
                    <td class="text-center">{{ $asistencia->estudiante->año ?? 'N/A' }}</td>
                    {{-- --- INICIO: NUEVA CELDA --- --}}
                    <td class="text-center">{{ $asistencia->curso->paralelo ?? 'N/A' }}</td>
                    {{-- --- FIN: NUEVA CELDA --- --}}
                    <td>{{ $asistencia->curso->materia->nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ $asistencia->modo }}</td>
                    <td class="text-center capitalize">{{ $asistencia->estado_llegada ? str_replace('_', ' ', $asistencia->estado_llegada) : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    {{-- Colspan actualizado a 9 --}}
                    <td colspan="9" class="text-center">No se encontraron asistencias con los filtros aplicados.</td>
                </tr>
            @endforelse
            @if($asistencias->isNotEmpty())
            <tr class="total-row">
                {{-- Colspan actualizado a 8 --}}
                <td colspan="8">Total de Asistencias:</td>
                <td class="text-center">{{ $asistencias->count() }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Pie de página --}}
    <div class="pie-pagina">
        Sistema de Control de Asistencia SACA - INCOS Nocturno
    </div>

</body>
</html>