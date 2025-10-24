<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Importar Carbon para manejar fechas

class EstadisticasService
{
    /**
     * Aplica el filtro de rango de fechas a una consulta.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param string $columnaFecha (por defecto 'fecha_hora')
     * @return \Illuminate\Database\Query\Builder
     */
    private function aplicarFiltroFechas($query, $fechaInicio, $fechaFin, $columnaFecha = 'asistencias.fecha_hora')
    {
        if ($fechaInicio && $fechaFin) {
            // Asegurarse de que la fecha final incluya todo el día
            $inicio = Carbon::parse($fechaInicio)->startOfDay();
            $fin = Carbon::parse($fechaFin)->endOfDay();
            $query->whereBetween($columnaFecha, [$inicio, $fin]);
        }
        return $query;
    }

    public function getAsistenciaDiariaSemanalMensual($fechaInicio = null, $fechaFin = null)
    {
        $query = Asistencia::select(
            DB::raw('DATE(fecha_hora) as fecha'),
            DB::raw('count(*) as total_asistencias')
        )
        ->groupBy('fecha')
        ->orderBy('fecha');

        $this->aplicarFiltroFechas($query, $fechaInicio, $fechaFin, 'fecha_hora');

        return $query->get();
    }

    public function getDistribucionHorasPico($fechaInicio = null, $fechaFin = null)
    {
        $query = Asistencia::select(
            DB::raw('HOUR(fecha_hora) as hora'),
            DB::raw('count(*) as total_asistencias')
        )
        ->groupBy('hora')
        ->orderBy('hora');

        $this->aplicarFiltroFechas($query, $fechaInicio, $fechaFin, 'fecha_hora');

        return $query->get();
    }

    public function getAsistenciaPorCarreraYAnio($fechaInicio = null, $fechaFin = null)
    {
        $query = Asistencia::join('students', 'asistencias.uid', '=', 'students.uid')
                            ->select('students.carrera', 'students.año', DB::raw('count(*) as total_asistencias'))
                            ->groupBy('students.carrera', 'students.año');
        
        $this->aplicarFiltroFechas($query, $fechaInicio, $fechaFin); // Usa 'asistencias.fecha_hora' por defecto

        return $query->get();
    }

    /**
     * Obtiene estudiantes en riesgo según la nueva lógica (<= 50% del estudiante top).
     */
    public function getEstudiantesEnRiesgo($fechaInicio = null, $fechaFin = null)
    {
        // 1. Crear la consulta base con el filtro de fecha aplicado
        $queryBase = Asistencia::query(); // Empezar desde Asistencia
        $this->aplicarFiltroFechas($queryBase, $fechaInicio, $fechaFin, 'asistencias.fecha_hora');

        // 2. Obtener los conteos de *todos* los estudiantes que tienen asistencias en ese rango
        $todosLosEstudiantes = $queryBase
            ->join('students', 'asistencias.uid', '=', 'students.uid')
            ->select(
                'students.nombre', 
                'students.primer_apellido', 
                'students.segundo_apellido', 
                'asistencias.uid', // Usamos uid para agrupar
                DB::raw('count(asistencias.id) as total_asistencias')
            )
            ->groupBy('asistencias.uid', 'students.nombre', 'students.primer_apellido', 'students.segundo_apellido')
            ->get();

        // 3. Encontrar la asistencia máxima (el "estudiante top")
        $maxAsistencias = $todosLosEstudiantes->max('total_asistencias');

        // 4. Si no hay asistencias (nadie asistió en ese rango), $maxAsistencias será null.
        // Devolvemos una colección vacía.
        if (is_null($maxAsistencias) || $maxAsistencias == 0) {
            return collect(); // Devolver una colección vacía
        }

        // 5. Calcular el umbral (50% del máximo)
        $umbralRiesgo = $maxAsistencias * 0.5;

        // 6. Filtrar la colección original
        $estudiantesEnRiesgo = $todosLosEstudiantes->filter(function ($estudiante) use ($umbralRiesgo) {
            // Aplicar la regla: "menor o igual al 50%"
            return $estudiante->total_asistencias <= $umbralRiesgo;
        });

        return $estudiantesEnRiesgo;
    }
}