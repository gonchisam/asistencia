<script setup>
import { computed } from 'vue';
import { Bar, Pie, Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

// Registrar los componentes de Chart.js que vamos a usar
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    asistenciaMensual: Array,
    horasPico: Array,
    asistenciaPorCarrera: Array, // <--- Este prop ahora será usado para barras agrupadas
    estudiantesEnRiesgo: Array,
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};

// Datos para el gráfico de Asistencia Mensual (Líneas) - SIN CAMBIOS
const asistenciaMensualData = computed(() => {
    return {
        labels: props.asistenciaMensual.map(item => item.mes),
        datasets: [{
            label: 'Asistencias por Mes',
            backgroundColor: '#4299e1',
            data: props.asistenciaMensual.map(item => item.total),
        }]
    };
});

// Datos para el gráfico de Horas Pico (Barras) - SIN CAMBIOS
const horasPicoData = computed(() => {
    return {
        labels: props.horasPico.map(item => `${item.hora}:00`),
        datasets: [{
            label: 'Asistencias por Hora',
            backgroundColor: '#f6ad55',
            data: props.horasPico.map(item => item.total),
        }]
    };
});

// ----- INICIO DE LA MODIFICACIÓN -----
// Datos para el gráfico de Asistencia por Carrera (Barras Agrupadas)
const asistenciaPorCarreraData = computed(() => {
    const rawData = props.asistenciaPorCarrera; // Viene del backend
    
    // 1. Definir los años (grupos) y sus colores/etiquetas
    // Asumimos que los valores en la BD son 'PRIMERO', 'SEGUNDO', 'TERCERO'
    const añosDefinidos = ['PRIMERO', 'SEGUNDO', 'TERCERO']; 
    
    const añoConfig = {
        'PRIMERO': { label: 'Primer Año', color: '#4299e1' }, // Azul
        'SEGUNDO': { label: 'Segundo Año', color: '#48bb78' }, // Verde
        'TERCERO': { label: 'Tercer Año', color: '#f6ad55' }, // Naranja
    };

    // 2. Obtener las carreras únicas (labels del eje X)
    // Usamos Set para valores únicos y luego ordenamos
    const carreras = [...new Set(rawData.map(item => item.carrera))].sort();

    // 3. Crear los datasets (uno por cada año definido)
    const datasets = añosDefinidos.map(año => {
        
        // 4. Para cada año, buscar el valor correspondiente a cada carrera
        const data = carreras.map(carrera => {
            const item = rawData.find(d => d.carrera === carrera && d.año === año);
            
            // 5. ----- CAMBIO AQUÍ -----
            // Usar 'porcentaje' en lugar de 'total_asistencias'
            // Redondeamos a 1 decimal para que se vea limpio
            return item ? parseFloat(item.porcentaje.toFixed(1)) : 0;
            // ----- FIN DEL CAMBIO -----
        });

        return {
            label: añoConfig[año].label,
            backgroundColor: añoConfig[año].color,
            data: data
        };
    });

    return {
        labels: carreras,
        datasets: datasets
    };
});
// ----- FIN DE LA MODIFICACIÓN -----

</script>

<template>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Panel de Estadísticas</h2>
        
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Asistencia Mensual (últimos 6 meses)</h3>
            <div class="h-80">
                <Line :data="asistenciaMensualData" :options="chartOptions" />
            </div>
        </div>

        <hr class="my-8" />

        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-xl font-semibold mb-4">Distribución de Horas Pico</h3>
                <div class="h-80">
                    <Bar :data="horasPicoData" :options="chartOptions" />
                </div>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold mb-4">Asistencia por Carrera y Año</h3>
                <div class="h-80">
                    <Bar :data="asistenciaPorCarreraData" :options="chartOptions" />
                </div>
            </div>
            </div>

        <hr class="my-8" />

        <div>
            <h3 class="text-xl font-semibold mb-4">Estudiantes en Riesgo (Asistencia &lt; 70%)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Nombre</th>
                            <th class="py-3 px-6 text-left">Carrera</th>
                            <th class="py-3 px-6 text-left">Asistencias (últimos 30 días)</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-if="estudiantesEnRiesgo.length === 0">
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No hay estudiantes en riesgo.</td>
                        </tr>
                        <tr v-else v-for="student in estudiantesEnRiesgo" :key="student.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left">{{ student.nombre }} {{ student.primer_apellido }} {{ student.segundo_apellido }}</td>
                            <td class="py-3 px-6 text-left">{{ student.carrera }}</td>
                            <td class="py-3 px-6 text-left">{{ student.asistencias_count }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>