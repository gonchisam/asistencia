<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Periodo;
use App\Models\Asistencia;
use App\Models\Estudiante; // Necesitamos este modelo
use Illuminate\Support\Facades\DB; // Para la consulta JOIN
use Illuminate\Support\Carbon; // Para manejar fechas y horas
use Illuminate\Support\Facades\Log; // Para registrar la actividad

class ProcesarFaltas extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     * (Así lo llamaremos: php artisan asistencia:procesar-faltas)
     */
    protected $signature = 'asistencia:procesar-faltas';

    /**
     * La descripción del comando de consola.
     */
    protected $description = 'Verifica periodos finalizados y marca "AUSENTE" a los estudiantes que no marcaron ENTRADA';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $now = Carbon::now();
        $diaSemanaActual = $now->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
        $fechaHoy = $now->toDateString();
        $horaActual = $now->format('H:i:s');

        // 1. Encontrar periodos que ya terminaron hoy (hora_fin < hora_actual)
        $periodosFinalizados = Periodo::where('hora_fin', '<', $horaActual)->get();

        if ($periodosFinalizados->isEmpty()) {
            $this->info('No hay periodos finalizados para procesar en este momento.');
            return 0; // Termina el comando
        }

        $this->info("Iniciando procesamiento para {$periodosFinalizados->count()} periodos finalizados...");

        foreach ($periodosFinalizados as $periodo) {
            
            // 2. ¿Ya procesamos las faltas de este periodo HOY?
            // Esto evita que el comando registre faltas duplicadas si se ejecuta varias veces
            $yaProcesado = Asistencia::where('periodo_id', $periodo->id)
                ->where('accion', 'AUSENTE') // Buscamos si ya hay faltas...
                ->whereDate('fecha_hora', $fechaHoy) // ...del día de hoy.
                ->exists();

            if ($yaProcesado) {
                // $this->info("Periodo {$periodo->nombre} (ID: {$periodo->id}) ya procesado hoy. Saltando.");
                continue; // Saltar al siguiente periodo
            }

            // 3. Obtener la "LISTA MAESTRA"
            // ¿Quiénes debían asistir hoy (diaSemanaActual) a este periodo (periodo->id)?
            // Usamos 'estudiante_id' porque la tabla 'curso_estudiante' usa el ID numérico
            $estudiantesInscritos_IDs = DB::table('curso_estudiante as ce')
                ->join('curso_horarios as ch', 'ce.curso_id', '=', 'ch.curso_id')
                ->where('ch.dia_semana', $diaSemanaActual)
                ->where('ch.periodo_id', $periodo->id)
                ->pluck('ce.estudiante_id'); // -> [1, 5, 12, 23]

            if ($estudiantesInscritos_IDs->isEmpty()) {
                // $this->info("Periodo {$periodo->nombre} (ID: {$periodo->id}) no tiene clases programadas hoy. Saltando.");
                continue; // No hay nadie inscrito
            }

            // 4. Convertir IDs a UIDs
            // La tabla 'asistencias' usa 'uid', no 'estudiante_id'.
            $estudiantesInscritos_UIDs = Estudiante::whereIn('id', $estudiantesInscritos_IDs)
                                                 ->pluck('uid'); // -> ['uid-1', 'uid-5', 'uid-12', 'uid-23']

            // 5. Obtener la "LISTA DE PRESENTES"
            // ¿Quiénes SÍ marcaron 'ENTRADA' para este periodo hoy?
            $estudiantesPresentes_UIDs = Asistencia::where('periodo_id', $periodo->id)
                ->where('accion', 'ENTRADA')
                ->whereDate('fecha_hora', $fechaHoy)
                ->pluck('uid'); // -> ['uid-1', 'uid-12']

            // 6. Calcular los AUSENTES
            // Comparamos la "Lista Maestra" (Paso 4) con la "Lista de Presentes" (Paso 5)
            $uidsAusentes = $estudiantesInscritos_UIDs->diff($estudiantesPresentes_UIDs); // -> ['uid-5', 'uid-23']

            if ($uidsAusentes->isEmpty()) {
                $this->info("Periodo {$periodo->nombre} (ID: {$periodo->id}): Todos presentes.");
                // Marcamos como "procesado" creando un registro falso para evitar re-chequeo
                // Esto es opcional pero es buena práctica
                Asistencia::create([
                    'uid' => 'SISTEMA',
                    'periodo_id' => $periodo->id,
                    'accion' => 'AUSENTE',
                    'modo' => 'PROCESADO_SIN_FALTAS',
                    'fecha_hora' => $now
                ]);
                continue;
            }

            // 7. Registrar las faltas (AUSENTE) en un solo query (batch insert)
            $registrosFaltas = [];
            // La falta se registra a la hora que terminó el periodo
            $fechaHoraFalta = Carbon::parse($fechaHoy . ' ' . $periodo->hora_fin);

            foreach ($uidsAusentes as $uid) {
                $registrosFaltas[] = [
                    'uid' => $uid,
                    'periodo_id' => $periodo->id,
                    'nombre' => null, // No es necesario, se obtiene por la relación
                    'accion' => 'AUSENTE',
                    'modo' => 'SISTEMA',
                    'fecha_hora' => $fechaHoraFalta,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insertar todas las faltas
            Asistencia::insert($registrosFaltas);

            $mensajeLog = "Periodo {$periodo->nombre} (ID: {$periodo->id}): Se marcaron " . count($registrosFaltas) . " faltas.";
            $this->info($mensajeLog);
            Log::info($mensajeLog); // Escribe en el archivo de log (storage/logs/laravel.log)
        }

        $this->info('Procesamiento de faltas completado.');
        return 0; // 0 = Éxito
    }
}
