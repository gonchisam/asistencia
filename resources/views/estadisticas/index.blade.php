@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ======================================================================== --}}
        {{-- SECCIÓN IMPRIMIBLE (Oculta en pantalla, Visible al imprimir)             --}}
        {{-- ======================================================================== --}}
        <div class="printable-content" style="display: none;">
            {{-- Encabezado idéntico al reporte de asistencias --}}
            <div class="encabezado">
                <table>
                    <tr>
                        <td class="logo-container">
                            <img src="{{ asset('img/logoincos.png') }}" class="logo" alt="Logo INCOS">
                        </td>
                        <td class="encabezado-content">
                            <h1>INSTITUTO TÉCNICO NACIONAL DE COMERCIO</h1>
                            <h2>FEDERICO ALVAREZ PLATA "NOCTURNO"</h2>
                            <div class="info">
                                REPORTE GRÁFICO DE ASISTENCIA Y DENSIDAD
                                <br>
                                @if($fechaInicio && $fechaFin)
                                    <span style="font-size: 8pt; font-weight: normal;">
                                        (Del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }})
                                    </span>
                                @else
                                    <span style="font-size: 8pt; font-weight: normal;">(Histórico General)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Información de generación --}}
            <div class="filters-print">
                <p><strong>Generado el:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
                @auth
                <p><strong>Generado por:</strong> {{ Auth::user()->name }}</p>
                @endauth
            </div>
        </div>
        {{-- ================= FIN SECCIÓN IMPRIMIBLE ================= --}}


        {{-- ======================================================================== --}}
        {{-- SECCIÓN WEB (Visible en pantalla, Oculta al imprimir selectivamente)     --}}
        {{-- ======================================================================== --}}

        <div class="flex flex-wrap justify-between items-center mb-6 no-print">
            <h1 class="text-3xl font-bold text-gray-800">
                <span class="text-blue-600">Reportes de Asistencia</span>
            </h1>
            <div class="flex items-center gap-2">
                <button id="exportPdfBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow-lg transition duration-150">
                    <i class="fas fa-file-pdf mr-2"></i> Imprimir / PDF
                </button>
            </div>
        </div>

        {{-- Formulario de Filtros --}}
        <form method="GET" action="{{ route('estadisticas.index') }}" class="mb-6 no-print" id="filtroForm">
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-wrap items-end gap-4">
                <div class="flex flex-col gap-1">
                    <label for="fecha_inicio" class="text-sm font-medium text-gray-700">Desde:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                           value="{{ $fechaInicio ?? '' }}" 
                           class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex flex-col gap-1">
                    <label for="fecha_fin" class="text-sm font-medium text-gray-700">Hasta:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" 
                           value="{{ $fechaFin ?? '' }}" 
                           class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="flex gap-2 pb-0.5">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <a href="{{ route('estadisticas.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded transition duration-150">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        {{-- Pestañas principales --}}
        <div class="mb-6 no-print">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button id="tab-graficos" class="tab-button py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 focus:outline-none active">
                        Gráficos de Densidad
                    </button>
                    <button id="tab-estudiantes" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                        Estudiantes en Riesgo
                    </button>
                </nav>
            </div>
        </div>

        {{-- Contenido de las pestañas --}}
        <div id="tab-content">
            {{-- Contenido de la pestaña Gráficos --}}
            <div id="graficos-content" class="tab-pane active">
                {{-- Subpestañas --}}
                <div class="mb-6 no-print">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-6">
                            <button id="subtab-diaria" class="subtab-button py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 focus:outline-none active">
                                Asistencia Diaria
                            </button>
                            <button id="subtab-horas" class="subtab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Horas Pico
                            </button>
                            <button id="subtab-carrera" class="subtab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Por Carrera
                            </button>
                        </nav>
                    </div>
                </div>

                {{-- Contenido de subpestañas --}}
                <div id="subtab-content">
                    
                    {{-- 1. Gráfico Diario --}}
                    <div id="diaria-content" class="subtab-pane active">
                        <div class="bg-white p-6 rounded-lg shadow-md chart-card relative">
                            <h2 class="text-xl font-semibold text-gray-700 mb-4 chart-title-print">Densidad de Asistencia Diaria</h2>
                            <div class="chart-container">
                                <canvas id="asistenciaDiariaChart"></canvas>
                            </div>
                            {{-- EXPLICACIÓN AGREGADA --}}
                            <div class="chart-explanation">
                                <p><strong>Interpretación:</strong> Este gráfico visualiza el "flujo" de estudiantes por día. Las montañas altas indican días con asistencia masiva, mientras que los valles representan días con mayor ausentismo. Sirve para identificar patrones de deserción en días específicos de la semana.</p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Gráfico Horas Pico --}}
                    <div id="horas-content" class="subtab-pane hidden">
                        <div class="bg-white p-6 rounded-lg shadow-md chart-card relative">
                            <h2 class="text-xl font-semibold text-gray-700 mb-4 chart-title-print">Distribución de Horas Pico</h2>
                            <div class="chart-container">
                                <canvas id="horasPicoChart"></canvas>
                            </div>
                            {{-- EXPLICACIÓN AGREGADA --}}
                            <div class="chart-explanation">
                                <p><strong>Interpretación:</strong> Muestra la concentración de entradas por hora. El punto más alto del gráfico señala el momento exacto de mayor congestión en el ingreso. Útil para evaluar la puntualidad general y la necesidad de reforzar el control en horarios clave.</p>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Gráfico por Carrera --}}
                    <div id="carrera-content" class="subtab-pane hidden">
                        <div class="bg-white p-6 rounded-lg shadow-md chart-card relative">
                            <h2 class="text-xl font-semibold text-gray-700 mb-4 chart-title-print">Volumen por Carrera</h2>
                            <div class="chart-container">
                                <canvas id="asistenciaPorCarreraChart"></canvas>
                            </div>
                            {{-- EXPLICACIÓN AGREGADA --}}
                            <div class="chart-explanation">
                                <p><strong>Interpretación:</strong> Compara el volumen total de asistencias acumuladas por carrera y año. Un área más grande indica una mayor participación y regularidad de ese grupo estudiantil en comparación con los demás durante el periodo seleccionado.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Contenido de la pestaña Estudiantes en Riesgo --}}
            <div id="estudiantes-content" class="tab-pane hidden">
                <div id="risk-students-table" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-4 no-print">
                        <h2 class="text-xl font-semibold text-gray-700">
                            Estudiantes en Riesgo (< 80% Asistencia)
                        </h2>
                        @if(isset($estudiantesEnRiesgo) && $estudiantesEnRiesgo->isNotEmpty())
                            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                Base Calculada: {{ $estudiantesEnRiesgo->first()->dias_totales_periodo }} días hábiles
                            </span>
                        @endif
                    </div>

                    <h3 class="printable-only text-center font-bold mb-4" style="font-size: 12pt; margin-bottom: 10px;">LISTA DE ESTUDIANTES EN RIESGO (ASISTENCIA < 80%)</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 print-table">
                            <thead class="bg-gray-50 print-thead">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estudiante
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Asistencias
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Porcentaje
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider print-hide-column">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 print-tbody">
                                @forelse ($estudiantesEnRiesgo as $estudiante)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $estudiante->nombre }} {{ $estudiante->primer_apellido }} {{ $estudiante->segundo_apellido }}
                                            </div>
                                            <div class="text-xs text-gray-500">UID: {{ $estudiante->uid }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                            {{ $estudiante->total_asistencias }} / {{ $estudiante->dias_totales_periodo }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-bold text-red-600">
                                                {{ number_format($estudiante->porcentaje, 1) }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap align-middle print-hide-column">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-200 mt-1">
                                                <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $estudiante->porcentaje }}%"></div>
                                            </div>
                                            <span class="text-xs text-red-500 mt-1 block text-center">Riesgo Crítico</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No hay estudiantes con baja asistencia en este periodo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Leyenda de la tabla --}}
                    <div class="mt-4 p-4 bg-yellow-50 rounded-md border border-yellow-200 chart-explanation">
                        <p><strong>Criterio de Riesgo:</strong> Se listan los estudiantes cuya asistencia total es inferior al <strong>80%</strong> de los días hábiles contabilizados en el rango de fechas seleccionado.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pie de Página Imprimible --}}
        <div class="printable-footer" style="display: none;">
            Sistema de Control de Asistencia SACA - INCOS Nocturno
        </div>

    </div>
@endsection

@push('styles')
<style>
    .chart-container { height: 500px; width: 100%; }
    .subtab-pane { transition: opacity 0.3s ease; }
    
    /* Estilos para la explicación en pantalla normal */
    .chart-explanation {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #3b82f6; /* Azul bonito */
        font-size: 0.95rem;
        color: #4b5563;
    }

    /* ================================================================================= */
    /* ESTILOS DE IMPRESIÓN                                                              */
    /* ================================================================================= */
    @media print {
        @page { margin: 20px; size: auto; }
        body { font-family: Arial, sans-serif; font-size: 9pt; background-color: white; }
        
        .no-print, header, nav, footer, .tab-pane:not(.active), .subtab-pane:not(.active), .print-hide-column {
            display: none !important;
        }

        .printable-content, .printable-footer, .printable-only {
            display: block !important;
        }

        /* Encabezado */
        .encabezado { width: 100%; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .encabezado table { width: 100%; border: none; }
        .encabezado table td { border: none; padding: 0; vertical-align: middle; }
        .logo-container { width: 200px; padding-right: 20px; }
        .logo { max-width: 100%; height: auto; max-height: 80px; } 
        .encabezado-content { text-align: center; }
        .encabezado-content h1 { font-size: 16pt; font-weight: bold; margin: 0; }
        .encabezado-content h2 { font-size: 14pt; margin: 3px 0; }
        .encabezado-content .info { font-size: 10pt; margin-top: 8px; font-weight: bold; }

        .filters-print { margin-bottom: 15px; border: 1px solid #eee; padding: 8px; background-color: #f9f9f9; font-size: 9pt; }
        .filters-print p { margin: 4px 0; }

        /* Tablas */
        table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; margin-top: 15px; font-size: 9pt; }
        table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: middle; }
        table.print-table th { background-color: #4A5568 !important; color: white !important; font-weight: bold; font-size: 8pt; text-align: center; -webkit-print-color-adjust: exact; }
        
        .shadow-md, .rounded-lg { box-shadow: none !important; border-radius: 0 !important; border: none !important; }
        
        /* Gráficos */
        #graficos-content.active .chart-card {
            border: 1px solid #ddd;
            page-break-inside: avoid;
            padding: 10px;
            margin-bottom: 20px;
        }
        .chart-title-print {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #000 !important;
            margin-bottom: 15px;
            text-decoration: underline;
        }
        canvas { max-width: 100% !important; height: auto !important; max-height: 350px; }

        /* Estilo específico para las explicaciones al imprimir */
        .chart-explanation {
            display: block !important;
            margin-top: 15px;
            padding: 10px;
            background-color: white !important; /* Sin fondo gris para ahorrar tinta */
            border: none !important;
            border-top: 1px solid #000 !important; /* Línea separadora simple */
            border-radius: 0 !important;
            font-size: 10pt;
            font-style: italic;
            color: #000 !important;
            text-align: justify;
        }
        .chart-explanation p { margin: 0; }

        /* Pie de Página */
        .printable-footer { margin-top: 20px; text-align: center; font-size: 8pt; color: #718096; position: fixed; bottom: 0; width: 100%; }
    }
</style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const asistenciaDiariaData = @json($asistenciaDiaria);
        const horasPicoData = @json($horasPico);
        const asistenciaPorCarreraData = @json($asistenciaPorCarrera);

        const chartInstances = {};
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            },
            elements: { line: { tension: 0.4 }, point: { radius: 6, hoverRadius: 8 } }
        };

        function createGradient(ctx, colorStart, colorEnd) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, colorStart);
            gradient.addColorStop(1, colorEnd);
            return gradient;
        }

        function createDensityChart(elementId, labels, data, label, baseColor) {
            const canvas = document.getElementById(elementId);
            const ctx = canvas.getContext('2d');
            
            let borderColor, gradientStart, gradientEnd;
            if (baseColor === 'blue') {
                borderColor = 'rgba(59, 130, 246, 1)'; gradientStart = 'rgba(59, 130, 246, 0.5)'; gradientEnd = 'rgba(59, 130, 246, 0.05)';
            } else if (baseColor === 'purple') {
                borderColor = 'rgba(139, 92, 246, 1)'; gradientStart = 'rgba(139, 92, 246, 0.5)'; gradientEnd = 'rgba(139, 92, 246, 0.05)';
            } else {
                borderColor = 'rgba(16, 185, 129, 1)'; gradientStart = 'rgba(16, 185, 129, 0.5)'; gradientEnd = 'rgba(16, 185, 129, 0.05)';
            }

            if (chartInstances[elementId]) chartInstances[elementId].destroy();

            chartInstances[elementId] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label, data: data, borderColor: borderColor,
                        backgroundColor: createGradient(ctx, gradientStart, gradientEnd),
                        fill: true, borderWidth: 3, pointBackgroundColor: '#fff', pointBorderColor: borderColor,
                    }]
                },
                options: commonOptions
            });
        }
        
        function renderCharts() {
            if (asistenciaDiariaData.length > 0) {
                createDensityChart('asistenciaDiariaChart', asistenciaDiariaData.map(i => i.fecha), asistenciaDiariaData.map(i => i.total_asistencias), 'Asistencias', 'blue');
            }
            if (horasPicoData.length > 0) {
                createDensityChart('horasPicoChart', horasPicoData.map(i => `${i.hora}:00`), horasPicoData.map(i => i.total_asistencias), 'Entradas', 'purple');
            }
            if (asistenciaPorCarreraData.length > 0) {
                createDensityChart('asistenciaPorCarreraChart', asistenciaPorCarreraData.map(i => `${i.carrera} (${i.año})`), asistenciaPorCarreraData.map(i => i.total_asistencias), 'Asistencias', 'green');
            }
        }

        document.addEventListener('DOMContentLoaded', renderCharts);

        document.getElementById('exportPdfBtn').addEventListener('click', () => {
            setTimeout(() => { window.print(); }, 200);
        });

        function switchTab(tabId) {
            document.querySelectorAll('.tab-pane').forEach(p => { p.classList.add('hidden'); p.classList.remove('active'); });
            document.querySelectorAll('.tab-button').forEach(b => { b.classList.remove('border-blue-500', 'text-blue-600'); b.classList.add('border-transparent', 'text-gray-500'); });
            document.getElementById(`${tabId}-content`).classList.remove('hidden');
            document.getElementById(`${tabId}-content`).classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.remove('border-transparent', 'text-gray-500');
            document.getElementById(`tab-${tabId}`).classList.add('border-blue-500', 'text-blue-600');
        }

        function switchSubTab(subTabId) {
            document.querySelectorAll('.subtab-pane').forEach(p => { p.classList.add('hidden'); p.classList.remove('active'); });
            document.querySelectorAll('.subtab-button').forEach(b => { b.classList.remove('border-blue-500', 'text-blue-600'); b.classList.add('border-transparent', 'text-gray-500'); });
            document.getElementById(`${subTabId}-content`).classList.remove('hidden');
            document.getElementById(`${subTabId}-content`).classList.add('active');
            document.getElementById(`subtab-${subTabId}`).classList.remove('border-transparent', 'text-gray-500');
            document.getElementById(`subtab-${subTabId}`).classList.add('border-blue-500', 'text-blue-600');
        }

        document.getElementById('tab-graficos').addEventListener('click', () => switchTab('graficos'));
        document.getElementById('tab-estudiantes').addEventListener('click', () => switchTab('estudiantes'));
        document.getElementById('subtab-diaria').addEventListener('click', () => switchSubTab('diaria'));
        document.getElementById('subtab-horas').addEventListener('click', () => switchSubTab('horas'));
        document.getElementById('subtab-carrera').addEventListener('click', () => switchSubTab('carrera'));
    </script>
@endpush