@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Reportes de Asistencia</h1>

        {{-- Contenedores para los gráficos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Gráfico de Asistencia Diaria --}}
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Asistencia Diaria</h2>
                <canvas id="asistenciaDiariaChart"></canvas>
            </div>

            {{-- Gráfico de Distribución de Horas Pico --}}
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Distribución de Horas Pico</h2>
                <canvas id="horasPicoChart"></canvas>
            </div>
        </div>

        {{-- Contenedor para el tercer gráfico --}}
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Asistencia por Carrera y Año</h2>
            <canvas id="asistenciaPorCarreraChart"></canvas>
        </div>

        {{-- Tabla de Estudiantes en Riesgo --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
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

@push('scripts')
    <script>
        // Obtenemos los datos pasados desde el controlador
        const asistenciaDiariaData = @json($asistenciaDiaria);
        const horasPicoData = @json($horasPico);
        const asistenciaPorCarreraData = @json($asistenciaPorCarrera);
        
        // Función para crear un gráfico de barras
        function createBarChart(elementId, labels, data, label, backgroundColor) {
            const ctx = document.getElementById(elementId);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: backgroundColor,
                        borderColor: backgroundColor.replace('0.5', '1'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // 1. Gráfico de Asistencia Diaria
        createBarChart(
            'asistenciaDiariaChart',
            asistenciaDiariaData.map(item => item.fecha),
            asistenciaDiariaData.map(item => item.total_asistencias),
            'Asistencias por Día',
            'rgba(54, 162, 235, 0.5)'
        );

        // 2. Gráfico de Distribución de Horas Pico
        createBarChart(
            'horasPicoChart',
            horasPicoData.map(item => `${item.hora}:00 - ${item.hora + 1}:00`),
            horasPicoData.map(item => item.total_asistencias),
            'Entradas por Hora',
            'rgba(255, 99, 132, 0.5)'
        );

        // 3. Gráfico de Asistencia por Carrera y Año
        createBarChart(
            'asistenciaPorCarreraChart',
            asistenciaPorCarreraData.map(item => `${item.carrera} - ${item.año}`),
            asistenciaPorCarreraData.map(item => item.total_asistencias),
            'Asistencias por Carrera y Año',
            'rgba(75, 192, 192, 0.5)'
        );
    </script>
@endpush