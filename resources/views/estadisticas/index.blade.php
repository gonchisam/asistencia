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
            <h1 class="text-3xl font-bold text-gray-800">
            <span class="text-blue-600">Reportes de Asistencia</span>
            </h1>
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

        {{-- Pestañas principales --}}
        <div class="mb-6 no-print">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button id="tab-graficos" class="tab-button py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 focus:outline-none active">
                        Gráficos de Asistencia
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
                {{-- Subpestañas para gráficos --}}
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
                                Asistencia por Carrera
                            </button>
                        </nav>
                    </div>
                </div>

                {{-- Contenido de subpestañas --}}
                <div id="subtab-content">
                    {{-- Subpestaña Asistencia Diaria --}}
                    <div id="diaria-content" class="subtab-pane active">
                        <div class="bg-white p-6 rounded-lg shadow-md chart-card">
                            <h2 class="text-xl font-semibold text-gray-700 mb-4">Asistencia Diaria</h2>
                            <div style="height: 500px;"><canvas id="asistenciaDiariaChart"></canvas></div>
                        </div>
                    </div>

                    {{-- Subpestaña Horas Pico --}}
                    <div id="horas-content" class="subtab-pane hidden">
                        <div class="bg-white p-6 rounded-lg shadow-md chart-card">
                            <h2 class="text-xl font-semibold text-gray-700 mb-4">Horas Pico</h2>
                            <div style="height: 500px;"><canvas id="horasPicoChart"></canvas></div>
                        </div>
                    </div>

                    {{-- Subpestaña Asistencia por Carrera --}}
                    <div id="carrera-content" class="subtab-pane hidden">
                        <div class="bg-white p-6 rounded-lg shadow-md chart-card">
                            <h2 class="text-xl font-semibold text-gray-700 mb-4">Asistencia por Carrera</h2>
                            <div style="height: 500px;"><canvas id="asistenciaPorCarreraChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contenido de la pestaña Estudiantes en Riesgo --}}
            <div id="estudiantes-content" class="tab-pane hidden">
                <div id="risk-students-table" class="bg-white p-6 rounded-lg shadow-md">
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
        #subtab-content, #subtab-content * {
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
        #subtab-content {
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
        /* Muestra todas las subpestañas al imprimir */
        .subtab-pane {
            display: block !important;
            margin-bottom: 30px;
        }
    }

    /* Estilos para mejorar la visualización de las subpestañas */
    .subtab-pane {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
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
            pie: { 
                type: 'pie', 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    animation: { duration: 0 },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 14
                                }
                            }
                        }
                    }
                }
            },
            bar: { 
                type: 'bar', 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 14
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 14
                                }
                            }
                        }
                    }, 
                    animation: { duration: 0 },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            }
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

        // Inicializar gráficos
        renderCharts(currentChartType);

        // Funcionalidad para cambiar tipo de gráfico
        document.getElementById('toggleChartTypeBtn').addEventListener('click', () => {
            currentChartType = currentChartType === 'pie' ? 'bar' : 'pie';
            document.getElementById('toggleChartTypeBtn').textContent = currentChartType === 'pie' ? 'Ver como Barras' : 'Ver como Torta';
            renderCharts(currentChartType);
        });

        // Funcionalidad para exportar PDF
        document.getElementById('exportPdfBtn').addEventListener('click', () => {
            // Asegurarse de que estamos en la pestaña de gráficos para la exportación
            switchTab('graficos');
            renderCharts(currentChartType);
            setTimeout(() => {
                window.print();
            }, 500);
        });

        // Funcionalidad para las pestañas principales
        function switchTab(tabId) {
            // Ocultar todos los contenidos de pestañas
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
                pane.classList.remove('active');
            });
            
            // Remover estilos activos de todas las pestañas
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Mostrar el contenido de la pestaña seleccionada
            document.getElementById(`${tabId}-content`).classList.remove('hidden');
            document.getElementById(`${tabId}-content`).classList.add('active');
            
            // Aplicar estilos a la pestaña activa
            document.getElementById(`tab-${tabId}`).classList.remove('border-transparent', 'text-gray-500');
            document.getElementById(`tab-${tabId}`).classList.add('border-blue-500', 'text-blue-600');
        }

        // Funcionalidad para las subpestañas
        function switchSubTab(subTabId) {
            // Ocultar todos los contenidos de subpestañas
            document.querySelectorAll('.subtab-pane').forEach(pane => {
                pane.classList.add('hidden');
                pane.classList.remove('active');
            });
            
            // Remover estilos activos de todas las subpestañas
            document.querySelectorAll('.subtab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Mostrar el contenido de la subpestaña seleccionada
            document.getElementById(`${subTabId}-content`).classList.remove('hidden');
            document.getElementById(`${subTabId}-content`).classList.add('active');
            
            // Aplicar estilos a la subpestaña activa
            document.getElementById(`subtab-${subTabId}`).classList.remove('border-transparent', 'text-gray-500');
            document.getElementById(`subtab-${subTabId}`).classList.add('border-blue-500', 'text-blue-600');
        }

        // Agregar event listeners a las pestañas principales
        document.getElementById('tab-graficos').addEventListener('click', () => switchTab('graficos'));
        document.getElementById('tab-estudiantes').addEventListener('click', () => switchTab('estudiantes'));

        // Agregar event listeners a las subpestañas
        document.getElementById('subtab-diaria').addEventListener('click', () => switchSubTab('diaria'));
        document.getElementById('subtab-horas').addEventListener('click', () => switchSubTab('horas'));
        document.getElementById('subtab-carrera').addEventListener('click', () => switchSubTab('carrera'));

        // Inicializar con la primera subpestaña activa
        switchSubTab('diaria');
    </script>
@endpush    