<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Periodo;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\DiaNoLaborable; // <--- 1. IMPORTANTE: Agregamos el modelo del calendario
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcesarFaltas extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     */
    protected $signature = 'asistencia:procesar-faltas';

    /**
     * La descripción del comando de consola.
     */
    protected $description = 'Verifica periodos finalizados y marca "AUSENTE" a los estudiantes, respetando el calendario académico.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $now = Carbon::now();
        $fechaHoy = $now->toDateString();
        $horaActual = $now->format('H:i:s');

        // ==============================================================================
        // --- PASO 4: VERIFICACIÓN DE CALENDARIO (Lógica de Protección) ---
        // ==============================================================================
        
        // Buscamos si la fecha de hoy coincide con algún día no laborable registrado
        $diaNoLaborable = DiaNoLaborable::where(function($query) use ($fechaHoy) {
            // Caso A: Coincidencia exacta de fecha (Ej: Bloqueo de hoy, Feriado móvil de este año)
            $query->where('fecha', $fechaHoy)
            
            // Caso B: Evento Recurrente (Ej: 14 de Septiembre, Navidad)
            // Comparamos solo mes y día, ignorando el año
                  ->orWhere(function($q) use ($fechaHoy) {
                      $q->where('recurrente', true)
                        ->whereRaw("DATE_FORMAT(fecha, '%m-%d') = DATE_FORMAT(?, '%m-%d')", [$fechaHoy]);
                  });
        })->first();

        // Si encontramos un evento...
        if ($diaNoLaborable) {
            // Si es Tolerancia, seguimos (sí hay clases, pero con permiso de llegar tarde)
            if ($diaNoLaborable->tipo === 'TOLERANCIA') {
                $this->info("⚠️ HOY ES DÍA DE TOLERANCIA ({$diaNoLaborable->descripcion}). Se procesarán faltas, pero ten en cuenta la flexibilidad.");
                // Aquí el código sigue ejecutándose...
            } 
            // Si es Emergencia, Feriado o Vacación -> ¡SE SUSPENDE TODO!
            else {
                $mensaje = "🛑 HOY NO SE PROCESAN FALTAS. Motivo: {$diaNoLaborable->descripcion} ({$diaNoLaborable->tipo})";
                $this->info($mensaje);
                Log::info("Cron Asistencia: " . $mensaje);
                
                return 0; // ¡DETENEMOS EL COMANDO AQUÍ! Nadie recibe falta hoy.
            }
        }
        // ==============================================================================

        $diaSemanaActual = $now->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

        // 1. Encontrar periodos que ya terminaron hoy (hora_fin < hora_actual)
        $periodosFinalizados = Periodo::where('hora_fin', '<', $horaActual)->get();

        if ($periodosFinalizados->isEmpty()) {
            $this->info('No hay periodos finalizados para procesar en este momento.');
            return 0;
        }

        $this->info("Iniciando procesamiento para {$periodosFinalizados->count()} periodos finalizados...");

        foreach ($periodosFinalizados as $periodo) {
            
            // 2. ¿Ya procesamos las faltas de este periodo HOY?
            $yaProcesado = Asistencia::where('periodo_id', $periodo->id)
                ->where('accion', 'AUSENTE')
                ->whereDate('fecha_hora', $fechaHoy)
                ->exists();

            if ($yaProcesado) {
                continue; 
            }

            // 3. Obtener la "LISTA MAESTRA"
            $estudiantesInscritos_IDs = DB::table('curso_estudiante as ce')
                ->join('curso_horarios as ch', 'ce.curso_id', '=', 'ch.curso_id')
                ->where('ch.dia_semana', $diaSemanaActual)
                ->where('ch.periodo_id', $periodo->id)
                ->pluck('ce.estudiante_id');

            if ($estudiantesInscritos_IDs->isEmpty()) {
                continue;
            }

            // 4. Convertir IDs a UIDs
            $estudiantesInscritos_UIDs = Estudiante::whereIn('id', $estudiantesInscritos_IDs)
                                                 ->pluck('uid');

            // 5. Obtener la "LISTA DE PRESENTES"
            $estudiantesPresentes_UIDs = Asistencia::where('periodo_id', $periodo->id)
                ->where('accion', 'ENTRADA')
                ->whereDate('fecha_hora', $fechaHoy)
                ->pluck('uid');

            // 6. Calcular los AUSENTES
            $uidsAusentes = $estudiantesInscritos_UIDs->diff($estudiantesPresentes_UIDs);

            if ($uidsAusentes->isEmpty()) {
                $this->info("Periodo {$periodo->nombre}: Todos presentes.");
                Asistencia::create([
                    'uid' => 'SISTEMA',
                    'periodo_id' => $periodo->id,
                    'accion' => 'AUSENTE',
                    'modo' => 'PROCESADO_SIN_FALTAS',
                    'fecha_hora' => $now
                ]);
                continue;
            }

            // 7. Registrar las faltas (AUSENTE)
            $registrosFaltas = [];
            $fechaHoraFalta = Carbon::parse($fechaHoy . ' ' . $periodo->hora_fin);

            foreach ($uidsAusentes as $uid) {
                $registrosFaltas[] = [
                    'uid' => $uid,
                    'periodo_id' => $periodo->id,
                    'nombre' => null,
                    'accion' => 'AUSENTE',
                    'modo' => 'SISTEMA',
                    'fecha_hora' => $fechaHoraFalta,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Asistencia::insert($registrosFaltas);

            $mensajeLog = "Periodo {$periodo->nombre}: Se marcaron " . count($registrosFaltas) . " faltas.";
            $this->info($mensajeLog);
            Log::info($mensajeLog);
        }

        $this->info('Procesamiento de faltas completado.');
        return 0;
    }
}