<?php

namespace App\Services;

use App\Models\Periodo; // Para leer la configuración de periodos
use Carbon\Carbon; // Para manejar fechas y horas
use Illuminate\Support\Facades\DB; // Para hacer la consulta 'JOIN'

class HorarioService
{
    /**
     * Verifica si un estudiante puede marcar asistencia AHORA MISMO y,
     * opcionalmente, en un AULA específica.
     *
     * @param int $estudianteId El ID del estudiante que intenta marcar.
     * @param int|null $aulaId El ID del aula (si marca desde un RFID) o null (si marca desde la app móvil).
     * @return array [puede_marcar, periodo_id, mensaje]
     */
    public function verificarEstadoAsistencia(int $estudianteId, ?int $aulaId): array
    {
        // 1. OBTENER HORA Y DÍA ACTUAL
        // Usamos la zona horaria de tu app (config/app.php)
        $now = Carbon::now(); 
        $horaActual = $now->format('H:i:s');
        $diaSemanaActual = $now->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

        
        // 2. BUSCAR PERIODO VÁLIDO
        // Leemos la configuración de la BD (tabla 'periodos')
        $periodosConfigurados = Periodo::all(); 
        
        $idPeriodoValido = null;

        foreach ($periodosConfigurados as $periodo) {
            $inicio = $periodo->hora_inicio;
            
            // Calculamos la hora fin de tolerancia dinámicamente
            // Ej: 18:30:00 + 15 minutos = 18:45:00
            $finTolerancia = Carbon::parse($periodo->hora_inicio)
                                ->addMinutes($periodo->tolerancia_ingreso_minutos)
                                ->format('H:i:s');

            // Comprobamos si la hora actual está dentro de la ventana de ingreso
            if ($horaActual >= $inicio && $horaActual <= $finTolerancia) {
                $idPeriodoValido = $periodo->id;
                break; // Encontramos un periodo válido, dejamos de buscar
            }
        }

        // 3. MANEJAR "FUERA DE HORARIO"
        // Si no se encontró ningún periodo válido, bloqueamos.
        if ($idPeriodoValido === null) {
            return [
                'puede_marcar' => false, 
                'periodo_id' => null, 
                'mensaje' => 'Estás fuera del horario de ingreso.'
            ];
        }

        // 4. VERIFICAR INSCRIPCIÓN Y HORARIO (LA CONSULTA CLAVE)
        
        // Construimos la consulta base
        $query = DB::table('curso_estudiante as ce')
            ->join('curso_horarios as ch', 'ce.curso_id', '=', 'ch.curso_id')
            ->where('ce.estudiante_id', $estudianteId)
            ->where('ch.dia_semana', $diaSemanaActual)
            ->where('ch.periodo_id', $idPeriodoValido);

        // 5. ¡VALIDACIÓN DE AULA (SI APLICA)!
        // Si la petición vino de un RFID ($aulaId no es null), 
        // filtramos también por el aula.
        if ($aulaId !== null) {
            $query->where('ch.aula_id', $aulaId);
        }

        // Ejecutamos la consulta. Solo necesitamos saber si existe (true) o no (false).
        $tieneClase = $query->exists();

        
        // 6. RETORNAR RESULTADO FINAL
        if ($tieneClase) {
            return [
                'puede_marcar' => true,
                'periodo_id' => $idPeriodoValido,
                'mensaje' => 'Asistencia permitida.'
            ];
        } else {
            // Si no tiene clase, personalizamos el mensaje de error
            $mensajeError = ($aulaId !== null)
                ? 'No tienes clase programada en ESTA AULA para este periodo.'
                : 'No tienes ninguna clase programada para este periodo.';

            return [
                'puede_marcar' => false,
                'periodo_id' => $idPeriodoValido, // Devolvemos el periodo para info
                'mensaje' => $mensajeError
            ];
        }
    }
}