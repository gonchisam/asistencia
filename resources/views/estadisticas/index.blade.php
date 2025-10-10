@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        {{-- INICIO: Encabezado para impresión (PDF) --}}
        <div class="printable-header" style="display: none;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 20%; vertical-align: top;">
                        <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="width: 80px; height: auto;">
                    </td>
                    <td style="width: 80%; text-align: center; vertical-align: middle;">
                        <h4 style="margin: 0; font-size: 16px;">INSTITUTO TECNICO NACIONAL DE COMERCIO</h4>
                        <h5 style="margin: 0; font-size: 14px; font-weight: normal;">FEDERICO ALVAREZ PLATA NOCTURNO</h5>
                        <br>
                        <h4 style="margin: 0; font-size: 16px;">SISTEMA AUTOMATIZADO PARA EL CONTROL DE ASISTENCIA S.A.C.A.</h4>
                    </td>
                </tr>
            </table>
            <hr style="margin-top: 15px;">
        </div>
        {{-- FIN: Encabezado para impresión (PDF) --}}

        <div class="flex flex-wrap justify-between items-center mb-6 no-print">
            <h1 class="text-3xl font-bold text-gray-800">Reportes de Asistencia</h1>
            <div class="flex items-center gap-2">
                {{-- Botones de acción --}}
                <button id="toggleChartTypeBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Ver como Barras
                </button>
                <button id="exportPdfBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Exportar Gráficos PDF
                </button>
                <a href="{{ route('estadisticas.exportarExcel') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Exportar Datos Excel
                </a>
            </div>
        </div>

        <div id="charts-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- ... tus gráficos aquí ... --}}
            <div class="bg-white p-6 rounded-lg shadow-md chart-card">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Asistencia Diaria</h2>
                <div style="height: 300px;"><canvas id="asistenciaDiariaChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md chart-card">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Horas Pico</h2>
                <div style="height: 300px;"><canvas id="horasPicoChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md chart-card">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Asistencia por Carrera</h2>
                <div style="height: 300px;"><canvas id="asistenciaPorCarreraChart"></canvas></div>
            </div>
        </div>

        <div id="risk-students-table" class="bg-white p-6 rounded-lg shadow-md">
            {{-- ... tu tabla de estudiantes en riesgo ... --}}
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Estudiantes en Riesgo</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre Completo
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Asistencias
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($estudiantesEnRiesgo as $estudiante)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $estudiante->nombre }} {{ $estudiante->primer_apellido }} {{ $estudiante->segundo_apellido }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $estudiante->total_asistencias }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No hay estudiantes con baja asistencia en este momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
{{-- Estilos para controlar la impresión a PDF --}}
<style>
    @media print {
        /* Oculta todo por defecto */
        body * {
            visibility: hidden;
        }
        /* Hace visible solo el encabezado, los gráficos y sus contenedores */
        .printable-header, .printable-header *,
        #charts-container, #charts-container * {
            visibility: visible;
        }
        .printable-header {
            display: block !important; /* Muestra el encabezado al imprimir */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            z-index: 9999;
        }
        #charts-container {
            margin-top: 150px; /* Ajusta el margen para que no se solape con el encabezado */
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .chart-card {
            page-break-inside: avoid;
        }
        /* Oculta los elementos no deseados */
        .no-print, #risk-students-table {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
    {{-- Tu script de Chart.js permanece igual que en la versión anterior --}}
    <script>
        const asistenciaDiariaData = @json($asistenciaDiaria);
        const horasPicoData = @json($horasPico);
        const asistenciaPorCarreraData = @json($asistenciaPorCarrera);

        let currentChartType = 'pie';
        const chartInstances = {};

        const pieColors = [
            'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)'
        ];

        const chartConfigs = {
            pie: { type: 'pie', options: { responsive: true, maintainAspectRatio: false, animation: { duration: 0 }}},
            bar: { type: 'bar', options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, animation: { duration: 0 }}}
        };

        function createChart(elementId, labels, data, label, defaultType = 'pie') {
            const ctx = document.getElementById(elementId).getContext('2d');
            const config = chartConfigs[defaultType];
            
            if (chartInstances[elementId]) {
                chartInstances[elementId].destroy();
            }
            
            chartInstances[elementId] = new Chart(ctx, {
                type: config.type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: defaultType === 'pie' ? pieColors : 'rgba(75, 192, 192, 0.5)',
                        borderColor: defaultType === 'pie' ? pieColors.map(c => c.replace('0.7', '1')) : 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: config.options
            });
        }
        
        function renderCharts(type) {
            createChart('asistenciaDiariaChart', asistenciaDiariaData.map(item => item.fecha), asistenciaDiariaData.map(item => item.total_asistencias), 'Asistencias por Día', type);
            createChart('horasPicoChart', horasPicoData.map(item => `${item.hora}:00`), horasPicoData.map(item => item.total_asistencias), 'Entradas por Hora', type);
            createChart('asistenciaPorCarreraChart', asistenciaPorCarreraData.map(item => `${item.carrera} - ${item.año}`), asistenciaPorCarreraData.map(item => item.total_asistencias), 'Asistencias por Carrera y Año', type);
        }

        renderCharts(currentChartType);

        document.getElementById('toggleChartTypeBtn').addEventListener('click', () => {
            currentChartType = currentChartType === 'pie' ? 'bar' : 'pie';
            document.getElementById('toggleChartTypeBtn').textContent = currentChartType === 'pie' ? 'Ver como Barras' : 'Ver como Torta';
            renderCharts(currentChartType);
        });

        document.getElementById('exportPdfBtn').addEventListener('click', () => {
            renderCharts(currentChartType);
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
@endpush