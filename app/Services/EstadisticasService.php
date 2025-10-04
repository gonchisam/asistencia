<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;

class EstadisticasService
{
    public function getAsistenciaDiariaSemanalMensual()
    {
        return Asistencia::select(
            DB::raw('DATE(fecha_hora) as fecha'),
            DB::raw('count(*) as total_asistencias')
        )
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();
    }

    public function getDistribucionHorasPico()
    {
        return Asistencia::select(
            DB::raw('HOUR(fecha_hora) as hora'),
            DB::raw('count(*) as total_asistencias')
        )
        ->groupBy('hora')
        ->orderBy('hora')
        ->get();
    }

    public function getAsistenciaPorCarreraYAnio()
    {
        // Join 'asistencias' with 'students' on the 'uid' column
        return Asistencia::join('students', 'asistencias.uid', '=', 'students.uid')
                            ->select('students.carrera', 'students.año', DB::raw('count(*) as total_asistencias'))
                            ->groupBy('students.carrera', 'students.año')
                            ->get();
    }

    public function getEstudiantesEnRiesgo()
    {
        // Lógica para identificar estudiantes con baja asistencia
        return Asistencia::join('students', 'asistencias.uid', '=', 'students.uid')
                            ->select('students.nombre', 'students.primer_apellido', 'students.segundo_apellido', DB::raw('count(*) as total_asistencias'))
                            ->groupBy('students.nombre', 'students.primer_apellido', 'students.segundo_apellido')
                            // **CORRECCIÓN:** Proporciona un valor para el umbral, por ejemplo 10
                            ->havingRaw('count(*) < ?', [10])
                            ->get();
    }
}