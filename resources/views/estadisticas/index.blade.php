@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        {{-- INICIO: Encabezado para impresión (PDF) --}}
        <div class="printable-header" style="display: none;">
            <div class="encabezado">
                <div class="logo-container">
                    <img src="{{ asset('img/logoincos.png') }}" class="logo" alt="Logo INCOS">
                </div>
                <div class="encabezado-content">
                    <h1>INSTITUTO TÉCNICO NACIONAL DE COMERCIO</h1>
                    <h2>FEDERICO ALVAREZ PLATA "NOCTURNO"</h2>
                    <div class="info">
                        <strong>SISTEMA AUTOMATIZADO PARA EL CONTROL DE ASISTENCIA S.A.C.A.</strong>
                    </div>
                </div>
            </div>
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
            </div>
        </div>

        {{-- INICIO: Formulario de Filtro por Fechas --}}
        <form method="GET" action="{{ route('estadisticas.index') }}" class="mb-6 no-print">
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label for="fecha_inicio" class="text-sm font-medium text-gray-700">Desde:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                           value="{{ $fechaInicio ?? '' }}" 
                           class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <label for="fecha_fin" class="text-sm font-medium text-gray-700">Hasta:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" 
                           value="{{ $fechaFin ?? '' }}" 
                           class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar Fechas
                </button>
                <a href="{{ route('estadisticas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    Limpiar Filtro
                </a>
            </div>
        </form>
        {{-- FIN: Formulario de Filtro por Fechas --}}

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
    /* Estilos para el encabezado moderno */
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

    @media print {
        /* 1. Permitir al usuario elegir la orientación. Se elimina @page { size: landscape; } */

        /* 2. Ocultar todos los elementos que no deben imprimirse */
        .no-print,
        /* Ocultar elementos de la interfaz de usuario */
        .tab-pane:not(.active),
        /* Ocultar pestañas inactivas */
        .subtab-pane:not(.active)
        /* Ocultar subpestañas inactivas */
        {
            display: none !important;
            visibility: hidden !important;
        }

        /* 3. Asegurar que el contenido activo y el encabezado sean visibles y estáticos */
        body * {
            visibility: hidden;
        }

        .printable-header, .printable-header *,
        #tab-content .tab-pane.active,
        #tab-content .tab-pane.active * {
             visibility: visible !important;
        }
        
        .printable-header {
            display: block !important;
            position: static !important;
            width: 100% !important;
            padding: 0 !important;
            margin-bottom: 20px;
        }
        
        /* 4. Centrar el contenido de la pestaña activa */
        #tab-content {
            width: 100%;
            margin: 0 auto; /* Centrado horizontal */
            display: block !important;
            position: static !important;
        }

        /* 5. Estilos específicos para contenido de gráficos (para impresión) */
        #graficos-content.active {
            display: block !important;
            /* Intentar centrar el gráfico horizontalmente */
            text-align: center;
        }
        
        #graficos-content.active .chart-card {
            page-break-inside: avoid;
            box-shadow: none;
            /* Flexbox para centrar el gráfico dentro del contenedor */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 90%; /* Ajustar el ancho para dejar espacio */
            margin: 0 auto; /* Centrado dentro de su contenedor padre */
        }
        
        /* 6. Estilos específicos para la tabla de estudiantes (vertical) */
        #estudiantes-content.active {
            display: block !important;
            /* Para centrar la tabla */
            width: 90%;
            margin: 0 auto;
        }
        
        #estudiantes-content.active table {
            width: 100%;
        }

        /* Forzar que el canvas del gráfico se imprima correctamente */
        canvas {
            max-width: 100% !important;
            height: auto !important;
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