<?php

namespace App\Services;

use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        // 1 = Lunes, 7 = Domingo (ISO 8601)
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
                $periodoValido = $periodo; // Guardamos el objeto completo
                break; 
            }
        }

        // 3. MANEJAR "FUERA DE HORARIO"
        if ($periodoValido === null) {
            return [
                'puede_marcar' => false, 
                'curso_id' => null,
                'periodo_id' => null, 
                'mensaje' => 'FUERA_DE_TOLERANCIA' // Mensaje clave para la App/Arduino
            ];
        }

        // 4. VERIFICAR INSCRIPCIÓN Y HORARIO (LA CONSULTA CLAVE)
        $query = DB::table('curso_estudiante as ce')
            ->join('curso_horarios as ch', 'ce.curso_id', '=', 'ch.curso_id')
            ->where('ce.estudiante_id', $estudianteId)
            ->where('ch.dia_semana', $diaSemanaActual)
            ->where('ch.periodo_id', $periodoValido->id)
            ->select('ce.curso_id'); // Seleccionamos el curso_id

        // 5. VALIDACIÓN DE AULA (SI APLICA)
        if ($aulaId !== null) {
            $query->where('ch.aula_id', $aulaId);
        }

        // --- ¡CAMBIO CLAVE! ---
        // En lugar de ->exists(), obtenemos el primer registro
        $horarioEncontrado = $query->first();
        // --- FIN CAMBIO CLAVE ---
        
        // 6. RETORNAR RESULTADO FINAL
        if ($horarioEncontrado) {
            return [
                'puede_marcar' => true,
                'curso_id' => $horarioEncontrado->curso_id, // ¡Lo devolvemos!
                'periodo_id' => $periodoValido->id,
                'mensaje' => 'ASISTENCIA_DISPONIBLE'
            ];
        } else {
            $mensajeError = ($aulaId !== null)
                ? 'SIN_CLASE_EN_AULA' // No tienes clase en esta aula ahora
                : 'SIN_CLASE'; // No tienes clase programada ahora

            return [
                'puede_marcar' => false,
                'curso_id' => null,
                'periodo_id' => $periodoValido->id, // Devolvemos el periodo para info
                'mensaje' => $mensajeError
            ];
        }
    }
}