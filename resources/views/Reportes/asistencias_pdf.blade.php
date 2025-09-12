<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencias</title>
    <style>
        /* Estilos básicos para el PDF - Dompdf tiene soporte limitado para CSS */
        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .filters {
            margin-bottom: 20px;
            border: 1px solid #eee;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .filters p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>Reporte de Asistencias - SACA</h1>

    <div class="filters">
        <p><strong>Generado el:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        <p><strong>Fecha Inicio:</strong> {{ $request->fecha_inicio ? \Carbon\Carbon::parse($request->fecha_inicio)->format('d/m/Y') : 'Todas' }}</p>
        <p><strong>Fecha Fin:</strong> {{ $request->fecha_fin ? \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') : 'Todas' }}</p>
        <p><strong>Estudiante:</strong>
            @if ($request->estudiante_id)
                @php
                    $estudiante = \App\Models\Estudiante::find($request->estudiante_id);
                @endphp
                {{ $estudiante ? $estudiante->nombre . ' (UID: ' . $estudiante->uid . ')' : 'N/A' }}
            @else
                Todos
            @endif
        </p>
        <p><strong>Acción:</strong> {{ $request->accion ?: 'Todas' }}</p>
        <p><strong>Modo:</strong> {{ $request->modo ?: 'Todos' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>UID</th>
                <th>Nombre</th>
                <th>Acción</th>
                <th>Modo</th>
                <th>Fecha y Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($asistencias as $asistencia)
                <tr>
                    <td>{{ $asistencia->uid }}</td>
                    <td>{{ $asistencia->nombre }}</td>
                    <td>{{ $asistencia->accion }}</td>
                    <td>{{ $asistencia->modo }}</td>
                    <td>{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No se encontraron asistencias con los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>