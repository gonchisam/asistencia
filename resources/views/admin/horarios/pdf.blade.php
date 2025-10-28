<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario - {{ $carrera }} {{ $ano_cursado }} {{ $paralelo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            padding: 15px;
        }

        .encabezado {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo-container {
            flex: 0 0 auto;
            margin-right: 20px;
        }

        .logo {
            max-width: 200px;
            height: auto;
        }

        .encabezado-content {
            flex: 1;
            text-align: center;
        }

        .encabezado-content h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .encabezado-content h2 {
            font-size: 14pt;
            margin-bottom: 3px;
        }

        .encabezado-content .info {
            font-size: 10pt;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #4A5568;
            color: white;
            font-weight: bold;
            font-size: 9pt;
        }

        td {
            min-height: 60px;
            font-size: 8pt;
        }

        .dia-col {
            background-color: #E2E8F0;
            font-weight: bold;
            width: 80px;
        }

        .materia {
            font-weight: bold;
            color: #2D3748;
            display: block;
            margin-bottom: 2px;
        }

        .aula {
            color: #4A5568;
            font-size: 7pt;
            display: block;
            margin-bottom: 2px;
        }

        .docente {
            color: #718096;
            font-size: 7pt;
            font-style: italic;
            display: block;
        }

        .celda-vacia {
            background-color: #F7FAFC;
        }

        .pie-pagina {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #718096;
        }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="encabezado">
        <div class="logo-container">
            <img src="{{ public_path('img/logoincos.png') }}" class="logo" alt="Logo INCOS">
        </div>
        <div class="encabezado-content">
            <h1>INSTITUTO TÉCNICO NACIONAL DE COMERCIO</h1>
            <h2>FEDERICO ALVAREZ PLATA "NOCTURNO"</h2>
            <div class="info">
                <strong>HORARIO DE CLASES - GESTIÓN {{ $gestion }}</strong><br>
                {{ mb_strtoupper($carrera) }} - {{ mb_strtoupper($ano_cursado) }} - PARALELO: {{ mb_strtoupper($paralelo) }}
            </div>
        </div>
    </div>

    {{-- Tabla de Horarios --}}
    <table>
        <thead>
            <tr>
                <th class="dia-col">DÍA / PERIODO</th>
                @foreach($periodos as $periodo)
                    <th>
                        {{ $periodo->nombre }}<br>
                        <small>({{ \Carbon\Carbon::parse($periodo->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($periodo->hora_fin)->format('H:i') }})</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($diasSemana as $diaNum => $diaNombre)
                <tr>
                    <td class="dia-col">{{ $diaNombre }}</td>
                    @foreach($periodos as $periodo)
                        <td class="{{ isset($horarioPorDiaPeriodo[$diaNum][$periodo->id]) ? '' : 'celda-vacia' }}">
                            @if(isset($horarioPorDiaPeriodo[$diaNum][$periodo->id]))
                                @php
                                    $clase = $horarioPorDiaPeriodo[$diaNum][$periodo->id];
                                @endphp
                                <span class="materia">{{ $clase['materia'] }}</span>
                                <span class="aula">{{ $clase['aula'] }}</span>
                                <span class="docente">{{ $clase['docente'] }}</span>
                            @else
                                <span style="color: #CBD5E0;">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pie de página --}}
    <div class="pie-pagina">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | Sistema de Control de Asistencia
    </div>

</body>
</html>