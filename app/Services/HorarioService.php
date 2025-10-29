<?php

namespace App\Services;

use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante;
use App\Models\CursoHorario;
use App\Models\Asistencia;

class HorarioService
{
    /**
     * Verifica si un estudiante puede marcar asistencia AHORA MISMO y,
     * opcionalmente, en un AULA específica.
     *
     * @param int $estudianteId El ID del estudiante que intenta marcar.
     * @param int|null $aulaId El ID del aula (si marca desde un RFID) o null (si marca desde la app móvil).
     * @return array [puede_marcar, curso_id, periodo_id, mensaje]
     */
    public function verificarEstadoAsistencia(int $estudianteId, ?int $aulaId): array
    {
        // 1. OBTENER HORA Y DÍA ACTUAL
        $now = Carbon::now(); 
        $horaActual = $now->format('H:i:s');
        $diaSemanaActual = $now->dayOfWeekIso; 

        // 2. BUSCAR PERIODO VÁLIDO
        $periodosConfigurados = Periodo::all(); 
        $periodoValido = null;

        foreach ($periodosConfigurados as $periodo) {
            $inicio = $periodo->hora_inicio;
            $finTolerancia = Carbon::parse($periodo->hora_inicio)
                                ->addMinutes($periodo->tolerancia_ingreso_minutos)
                                ->format('H:i:s');

            if ($horaActual >= $inicio && $horaActual <= $finTolerancia) {
                $periodoValido = $periodo;
                break; 
            }
        }

        // 3. MANEJAR "FUERA DE HORARIO"
        if ($periodoValido === null) {
            return [
                'puede_marcar' => false, 
                'curso_id' => null,
                'periodo_id' => null, 
                'mensaje' => 'FUERA_DE_TOLERANCIA'
            ];
        }

        // 4. VERIFICAR INSCRIPCIÓN Y HORARIO
        $query = DB::table('curso_estudiante as ce')
            ->join('curso_horarios as ch', 'ce.curso_id', '=', 'ch.curso_id')
            ->where('ce.estudiante_id', $estudianteId)
            ->where('ch.dia_semana', $diaSemanaActual)
            ->where('ch.periodo_id', $periodoValido->id)
            ->select('ce.curso_id');

        // 5. VALIDACIÓN DE AULA (SI APLICA)
        if ($aulaId !== null) {
            $query->where('ch.aula_id', $aulaId);
        }

        $horarioEncontrado = $query->first();
        
        // 6. RETORNAR RESULTADO FINAL
        if ($horarioEncontrado) {
            return [
                'puede_marcar' => true,
                'curso_id' => $horarioEncontrado->curso_id,
                'periodo_id' => $periodoValido->id,
                'mensaje' => 'ASISTENCIA_DISPONIBLE'
            ];
        } else {
            $mensajeError = ($aulaId !== null)
                ? 'SIN_CLASE_EN_AULA'
                : 'SIN_CLASE';

            return [
                'puede_marcar' => false,
                'curso_id' => null,
                'periodo_id' => $periodoValido->id,
                'mensaje' => $mensajeError
            ];
        }
    }

    /**
     * Obtiene el período actual basado en la hora del sistema
     * Considera la tolerancia de ingreso
     *
     * @return Periodo|null
     */
    public function getPeriodoActual(): ?Periodo
    {
        $now = Carbon::now();
        $horaActual = $now->format('H:i:s');
        
        $periodosConfigurados = Periodo::all();
        
        foreach ($periodosConfigurados as $periodo) {
            $inicio = $periodo->hora_inicio;
            $finTolerancia = Carbon::parse($periodo->hora_inicio)
                                ->addMinutes($periodo->tolerancia_ingreso_minutos)
                                ->format('H:i:s');

            if ($horaActual >= $inicio && $horaActual <= $finTolerancia) {
                return $periodo;
            }
        }
        
        return null;
    }

    /**
     * ✅ NUEVO: Obtiene el período por hora específica (para sync offline)
     *
     * @param string $hora Formato "HH:MM:SS"
     * @return Periodo|null
     */
    public function getPeriodoPorHora(string $hora): ?Periodo
    {
        $periodosConfigurados = Periodo::all();
        
        foreach ($periodosConfigurados as $periodo) {
            $inicio = $periodo->hora_inicio;
            $fin = $periodo->hora_fin;

            if ($hora >= $inicio && $hora <= $fin) {
                return $periodo;
            }
        }
        
        return null;
    }

    /**
     * Busca el horario de clase (CursoHorario) específico que un estudiante 
     * debería tener en este preciso momento.
     *
     * @param Estudiante $estudiante
     * @return CursoHorario|null
     */
    public function getClaseActualParaEstudiante(Estudiante $estudiante): ?CursoHorario
    {
        $diaSemanaActual = Carbon::now()->dayOfWeekIso;
        $periodoActual = $this->getPeriodoActual();

        if (!$periodoActual) {
            return null;
        }

        $claseActual = $estudiante->cursos()
            ->whereHas('horarios', function ($query) use ($diaSemanaActual, $periodoActual) {
                $query->where('dia_semana', $diaSemanaActual)
                      ->where('periodo_id', $periodoActual->id);
            })
            ->with(['horarios' => function ($query) use ($diaSemanaActual, $periodoActual) {
                $query->where('dia_semana', $diaSemanaActual)
                      ->where('periodo_id', $periodoActual->id)
                      // --- INICIO DE LA CORRECCIÓN ---
                      ->with('curso.materia', 'aula'); // Se usa 'curso.materia' en lugar de 'materia'
                      // --- FIN DE LA CORRECCIÓN ---
            }])
            ->first();

        if ($claseActual && $claseActual->horarios->isNotEmpty()) {
            $horario = $claseActual->horarios->first();
            $horario->periodo = $periodoActual;
            return $horario;
        }

        return null;
    }

    /**
     * ✅ CORREGIDO: Verifica si ya existe un registro de asistencia
     * (SIN DUPLICADOS)
     *
     * @param Estudiante $estudiante
     * @param Periodo $periodo
     * @param Carbon|null $fecha
     * @return bool
     */
    public function verificarAsistenciaExistente(Estudiante $estudiante, Periodo $periodo, ?Carbon $fecha = null): bool
    {
        if (is_null($fecha)) {
            $fecha = Carbon::today();
        }

        // ✅ Usar 'uid' en lugar de 'student_id'
        // ✅ Usar 'fecha_hora' en lugar de 'fecha'
        return Asistencia::where('uid', $estudiante->uid)
                          ->where('periodo_id', $periodo->id)
                          ->whereDate('fecha_hora', $fecha)
                          ->exists();
    }

    /**
     * Método completo que combina ambas verificaciones
     * Para determinar si un estudiante puede marcar asistencia
     *
     * @param Estudiante $estudiante
     * @param Carbon|null $fecha
     * @return array
     */
    public function verificarAsistenciaCompleta(Estudiante $estudiante, ?Carbon $fecha = null): array
    {
        $claseActual = $this->getClaseActualParaEstudiante($estudiante);
        
        if (!$claseActual) {
            return [
                'puede_marcar' => false,
                'mensaje' => 'NO_TIENE_CLASE_AHORA',
                'clase' => null
            ];
        }

        // Pasamos la fecha al método verificarAsistenciaExistente
        $asistenciaExistente = $this->verificarAsistenciaExistente($estudiante, $claseActual->periodo, $fecha);
        
        if ($asistenciaExistente) {
            return [
                'puede_marcar' => false,
                'mensaje' => 'ASISTENCIA_YA_REGISTRADA',
                'clase' => $claseActual
            ];
        }

        return [
            'puede_marcar' => true,
            'mensaje' => 'PUEDE_MARCAR_ASISTENCIA',
            'clase' => $claseActual
        ];
    }

    /**
     * ✅ CORREGIDO: Verifica si un estudiante ya marcó asistencia hoy
     * (SIN DUPLICADOS)
     *
     * @param int $estudianteId
     * @param Carbon|null $fecha
     * @return bool
     */
    public function verificarAsistenciaHoy(int $estudianteId, ?Carbon $fecha = null): bool
    {
        if (is_null($fecha)) {
            $fecha = Carbon::today();
        }

        // Primero obtenemos el estudiante
        $estudiante = Estudiante::find($estudianteId);
        
        if (!$estudiante) {
            return false;
        }

        // ✅ Usar 'uid' en lugar de 'student_id'
        // ✅ Usar 'fecha_hora' en lugar de 'fecha'
        return Asistencia::where('uid', $estudiante->uid)
                          ->whereDate('fecha_hora', $fecha)
                          ->exists();
    }
}