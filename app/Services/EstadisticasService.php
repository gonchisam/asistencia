<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\DiaNoLaborable; // Asegúrate de importar el modelo
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadisticasService
{
    /**
     * Aplica el filtro de rango de fechas a una consulta.
     */
    private function aplicarFiltroFechas($query, $fechaInicio, $fechaFin, $columnaFecha = 'asistencias.fecha_hora')
    {
        if ($fechaInicio && $fechaFin) {
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
        
        $this->aplicarFiltroFechas($query, $fechaInicio, $fechaFin);
        return $query->get();
    }

    /**
     * Calcula los días laborables (Lunes a Viernes) excluyendo feriados.
     */
    private function getDiasLaborablesCount($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        // Obtener lista de feriados en el rango (array de strings 'Y-m-d')
        $feriados = DiaNoLaborable::whereBetween('fecha', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
                                  ->pluck('fecha')
                                  ->toArray();

        $diasHabiles = 0;
        $curr = $inicio->copy();

        while ($curr->lte($fin)) {
            // Consideramos día hábil si NO es fin de semana (Sáb/Dom) y NO está en feriados
            if (!$curr->isWeekend() && !in_array($curr->format('Y-m-d'), $feriados)) {
                $diasHabiles++;
            }
            $curr->addDay();
        }

        return $diasHabiles;
    }

    /**
     * Obtiene estudiantes con asistencia menor al 80% de los días laborables del periodo.
     */
    public function getEstudiantesEnRiesgo($fechaInicio = null, $fechaFin = null)
    {
        // Si no hay fechas definidas, usar el mes actual por defecto para el cálculo
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // 1. Calcular total de días laborables (El 100% teórico)
        $totalDiasLaborables = $this->getDiasLaborablesCount($fechaInicio, $fechaFin);

        if ($totalDiasLaborables === 0) {
            return collect(); // No hay riesgo si no hubo clases
        }

        // 2. Calcular el umbral del 80%
        $umbralMinimo = $totalDiasLaborables * 0.80;

        // 3. Obtener asistencias reales de los estudiantes
        $queryBase = Asistencia::query(); 
        $this->aplicarFiltroFechas($queryBase, $fechaInicio, $fechaFin, 'asistencias.fecha_hora');

        $estudiantes = $queryBase
            ->join('students', 'asistencias.uid', '=', 'students.uid')
            ->select(
                'students.nombre', 
                'students.primer_apellido', 
                'students.segundo_apellido', 
                'asistencias.uid',
                DB::raw('count(asistencias.id) as total_asistencias')
            )
            ->groupBy('asistencias.uid', 'students.nombre', 'students.primer_apellido', 'students.segundo_apellido')
            ->get();

        // 4. Filtrar estudiantes que no cumplen el 80%
        $estudiantesEnRiesgo = $estudiantes->filter(function ($estudiante) use ($umbralMinimo) {
            return $estudiante->total_asistencias < $umbralMinimo;
        });

        // 5. Agregar datos calculados para mostrar en la vista
        $estudiantesEnRiesgo->transform(function ($estudiante) use ($totalDiasLaborables) {
            $estudiante->porcentaje = ($estudiante->total_asistencias / $totalDiasLaborables) * 100;
            $estudiante->dias_totales_periodo = $totalDiasLaborables;
            return $estudiante;
        });

        return $estudiantesEnRiesgo;
    }
}