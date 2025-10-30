<?php

namespace App\Http\Controllers; // <-- CORREGIDO (Solo un namespace)

// <-- Se eliminó el 'namespace' duplicado y 'use App\Http\Controllers\Api;'

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Aula; // <-- AÑADIDO (para \App\Models\Aula)
use App\Models\Periodo; // <-- AÑADIDO (para \App\Models\Periodo)
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Services\HorarioService;
// (Se eliminaron 'use' innecesarios como Auth, Hash, etc. que no se usan en este controlador)

class AsistenciaController extends Controller
{
    protected $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    /**
     * MÉTODO "SEMÁFORO" (¡MODIFICADO!)
     * Verifica si un estudiante puede marcar asistencia en este momento.
     * Ruta: GET /api/asistencia/verificar
     */
    public function verificarEstadoAsistencia(Request $request)
    {
        Log::info('🔔 Petición semáforo recibida:', $request->all());

        $estudiante = null;
        $aulaId = null;

        // 1. IDENTIFICAR ESTUDIANTE
        if ($request->has('uid_tarjeta')) {
            $estudiante = Estudiante::where('uid', $request->uid_tarjeta)->first();
        } elseif ($request->has('student_id')) {
            $estudiante = Estudiante::find($request->student_id);
        }

        if (!$estudiante) {
            Log::warning('❌ Estudiante no encontrado en semáforo');
            return response()->json([
                'puede_marcar' => false,
                'mensaje' => 'ESTUDIANTE_NO_ENCONTRADO'
            ], 404);
        }

        // 2. VALIDAR ESTADO DEL ESTUDIANTE
        if (!$estudiante->estado) {
            Log::warning("❌ Cuenta inactiva en semáforo: {$estudiante->nombreCompleto}");
            return response()->json([
                'puede_marcar' => false,
                'mensaje' => 'CUENTA_INACTIVA'
            ], 403);
        }

        // 3. OBTENER AULA
        if ($request->has('aula_codigo')) {
            $aulaCodigo = strtoupper(trim($request->aula_codigo));
            $aula = Aula::where('codigo', $aulaCodigo)->first();
            
            if ($aula) {
                $aulaId = $aula->id;
                Log::info("✅ Aula identificada en semáforo: {$aulaCodigo} -> ID {$aulaId}");
            } else {
                Log::warning("⚠️ Código de aula no encontrado: {$aulaCodigo}");
            }
        }

        // 4. USAR EL SERVICIO PARA VERIFICACIÓN DE HORARIO
        try {
            $resultado = $this->horarioService->verificarEstadoAsistencia($estudiante->id, $aulaId);
            Log::info("📊 Resultado semáforo (Horario) para {$estudiante->nombreCompleto}:", $resultado);

            // --- INICIO: ¡MODIFICACIÓN CLAVE! VERIFICAR DUPLICADOS ---
            // Si el horario está abierto, AHORA verificamos si ya marcó.
            if ($resultado['puede_marcar']) {
                $periodo = Periodo::find($resultado['periodo_id']);
                if ($periodo) {
                    $yaMarco = $this->horarioService->verificarAsistenciaExistente(
                        $estudiante, 
                        $periodo, 
                        Carbon::today()
                    );

                    if ($yaMarco) {
                        Log::warning("⚠️ Semáforo ARDUINO denegado (Duplicado): {$estudiante->nombreCompleto}");
                        // Sobreescribimos el resultado
                        $resultado['puede_marcar'] = false;
                        $resultado['mensaje'] = 'ASISTENCIA_YA_REGISTRADA';
                    }
                }
            } 
            // Si el horario está cerrado, verificamos si es porque ya marcó
            else if ($resultado['mensaje'] == 'FUERA_DE_TOLERANCIA' && $resultado['periodo_id']) {
                 $periodo = Periodo::find($resultado['periodo_id']);
                 if ($periodo) {
                     $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodo, Carbon::today());
                     if ($yaMarco) {
                        $resultado['mensaje'] = 'ASISTENCIA_YA_REGISTRADA';
                     }
                 }
            }
            // --- FIN: ¡MODIFICACIÓN CLAVE! ---

            // Formatear respuesta estándar
            return response()->json([
                'puede_marcar' => $resultado['puede_marcar'],
                'curso_id' => $resultado['curso_id'],
                'periodo_id' => $resultado['periodo_id'],
                'mensaje' => $resultado['mensaje'],
                'estudiante' => [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->nombreCompleto,
                    'uid' => $estudiante->uid
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en servicio HorarioService: ' . $e->getMessage());
            return response()->json([
                'puede_marcar' => false,
                'mensaje' => 'ERROR_INTERNO'
            ], 500);
        }
    }

    /**
     * MÉTODO EXISTENTE (SIN CAMBIOS, ESTÁ CORRECTO)
     * Obtiene la lista de estudiantes para un dispositivo (aula)
     */
    public function getEstudiantesPorDispositivo(Request $request, $device_id)
    {
        Log::info("Iniciando sincronización de lista para aula: " . $device_id);

        $aula = Aula::where('codigo', $device_id)->first();

        if (!$aula) {
            Log::error("Aula no encontrada con codigo: " . $device_id);
            return response()->json(['error' => 'Aula no encontrada con codigo: ' . $device_id], 404);
        }

        $diaSemana = Carbon::now()->dayOfWeekIso;
        Log::info("Buscando horarios para el día (ISO): " . $diaSemana);

        $horariosHoyEnAula = \App\Models\CursoHorario::where('aula_id', $aula->id)
                                        ->where('dia_semana', $diaSemana)
                                        ->with([
                                            'curso' => function ($query) {
                                                $query->with('estudiantes');
                                            },
                                            'periodo'
                                        ])
                                        ->get();
        
        if ($horariosHoyEnAula->isEmpty()) {
             Log::info("No se encontraron horarios hoy (" . $diaSemana . ") para el aula " . $device_id);
             return response()->json([]);
        }

        $listaEstudiantes = collect();
        Log::info("Horarios encontrados: " . $horariosHoyEnAula->count());

        foreach ($horariosHoyEnAula as $horario) {
            
            if (!$horario->curso) {
                Log::warning("El horario ID " . $horario->id . " no tiene un curso asociado.");
                continue;
            }
            
            foreach ($horario->curso->estudiantes as $estudiante) {
                
                if (!$listaEstudiantes->contains('id', $estudiante->id)) {
                    
                    $yaMarco = false;
                    
                    if ($horario->periodo) {
                        $yaMarco = $this->horarioService->verificarAsistenciaExistente(
                            $estudiante, 
                            $horario->periodo,
                            Carbon::today()
                        );
                    } else {
                        Log::warning("El horario ID " . $horario->id . " no tiene un periodo asociado.");
                    }

                    $listaEstudiantes->push([
                        'id' => $estudiante->id,
                        'nombre' => $estudiante->nombre_completo,
                        'uid' => $estudiante->uid,
                        'estado' => $estudiante->estado, 
                        'marco_hoy' => $yaMarco,
                    ]);
                }
            }
        }
        Log::info("Sincronización de lista completa. Enviando " . $listaEstudiantes->count() . " estudiantes.");
        return response()->json($listaEstudiantes);
    }
    
    /**
     * MÉTODO EXISTENTE (SIN CAMBIOS, ESTÁ CORRECTO)
     * Sincronización offline (usada por tu Arduino)
     */
    public function syncOfflineAsistencias(Request $request)
    {
        Log::info('📦 Inicio de Sincronización Offline');

        $registros = $request->json()->all();
        
        if (empty($registros)) {
            Log::info('📦 Sincronización offline: Nada que sincronizar');
            return response()->json(['success' => true, 'message' => 'Nada que sincronizar.']);
        }

        Log::info("📦 Sincronización offline: " . count($registros) . " registros recibidos");

        $procesados = 0;
        $descartados_conflicto = 0;
        $descartados_error = 0;

        foreach ($registros as $index => $record) {
            Log::info("📦 Procesando registro {$index}:", $record);

            if (empty($record['uid']) || empty($record['fecha']) || empty($record['hora'])) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: Datos básicos faltantes");
                continue;
            }
            
            $estudiante = Estudiante::where('uid', $record['uid'])->where('estado', 1)->first();
            if (!$estudiante) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: UID no encontrado o inactivo - {$record['uid']}");
                continue;
            }

            try {
                $fecha = Carbon::createFromFormat('d/m/Y', $record['fecha']);
                $hora = $record['hora'];
                Log::info("📅 Registro {$index}: Fecha parseada - {$fecha->format('Y-m-d')}, Hora - {$hora}");
            } catch (\Exception $e) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: Formato de fecha/hora inválido - {$e->getMessage()}");
                continue;
            }

            $periodo = $this->horarioService->getPeriodoPorHora($hora);
            if (!$periodo) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: No se encontró período para la hora {$hora}");
                continue;
            }

            Log::info("⏰ Registro {$index}: Período encontrado - {$periodo->nombre} ({$periodo->hora_inicio} - {$periodo->hora_fin})");

            $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodo, $fecha);

            if ($yaMarco) {
                $descartados_conflicto++;
                Log::info("⚡ Registro {$index}: Descartado (conflicto/duplicado) - Ya existe asistencia");
                continue;
            }

            $clase = $this->getClaseParaEstudianteEnFechaHora($estudiante, $fecha, $hora);

            try {
                Asistencia::create([
                    'student_id' => $estudiante->id,
                    'uid' => $estudiante->uid,
                    'nombre' => $estudiante->nombreCompleto,
                    'fecha_hora' => $fecha->copy()->setTimeFromTimeString($hora),
                    'accion' => 'ENTRADA',
                    'modo' => 'OFFLINE',
                    'estado_llegada' => $this->calcularEstadoLlegada($periodo->id, $fecha->copy()->setTimeFromTimeString($hora)),
                    'periodo_id' => $periodo->id,
                    'curso_id' => $clase ? $clase->curso_id : null,
                ]);

                $procesados++;
                Log::info("✅ Registro {$index}: Procesado exitosamente");

            } catch (\Exception $e) {
                $descartados_error++;
                Log::error("❌ Registro {$index}: Error al guardar - {$e->getMessage()}");
            }
        }

        Log::info("📦 Sync Offline Completa: {$procesados} procesados, {$descartados_conflicto} conflictos, {$descartados_error} errores");
        
        return response()->json([
            'success' => true,
            'message' => 'Sincronización offline completada',
            'procesados' => $procesados,
            'descartados_conflictos' => $descartados_conflicto,
            'descartados_errores' => $descartados_error,
        ]);
    }

    /**
     * HELPER (SIN CAMBIOS)
     */
    private function getClaseParaEstudianteEnFechaHora(Estudiante $estudiante, Carbon $fecha, string $hora)
    {
        $diaSemana = $fecha->dayOfWeekIso;
        
        $periodo = $this->horarioService->getPeriodoPorHora($hora);
        if (!$periodo) {
            return null;
        }

        $clase = $estudiante->cursos()
            ->whereHas('horarios', function ($query) use ($diaSemana, $periodo) {
                $query->where('dia_semana', $diaSemana)
                      ->where('periodo_id', $periodo->id);
            })
            ->with(['horarios' => function ($query) use ($diaSemana, $periodo) {
                $query->where('dia_semana', $diaSemana)
                      ->where('periodo_id', $periodo->id)
                      ->with('curso.materia', 'aula');
            }])
            ->first();

        if ($clase && $clase->horarios->isNotEmpty()) {
            return $clase->horarios->first();
        }

        return null;
    }

    /**
     * ENDPOINT PRINCIPAL (SIN CAMBIOS, ESTÁ CORRECTO)
     * Ruta: POST /api/asistencia
     */
    public function store(Request $request)
    {
        Log::info('📡 Petición RFID recibida:', $request->all());

        try {
            $data = $request->validate([
                'uid' => 'required|string|max:255',
                'accion' => 'required|string|in:ENTRADA,SALIDA',
                'modo' => 'required|string|max:255',
                'aula_codigo' => 'nullable|string|max:20',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validación fallida RFID:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . json_encode($e->errors())
            ], 400);
        }

        $estudiante = Estudiante::where('uid', $data['uid'])->first();

        if (!$estudiante) {
            Log::warning("⚠️ UID no encontrado: {$data['uid']}");
            return response()->json([
                'success' => false,
                'message' => 'UID NO ENCONTRADO'
            ], 404);
        }

        if (!$estudiante->estado) {
            Log::warning("⚠️ Cuenta inactiva: UID {$data['uid']}");
            return response()->json([
                'success' => false,
                'message' => 'CUENTA INACTIVA'
            ], 403);
        }

        $aulaId = null;
        $aulaNombre = 'Sin aula';
        
        if (!empty($data['aula_codigo'])) {
            $aulaCodigo = strtoupper(trim($data['aula_codigo']));
            
            $aula = Aula::where('codigo', $aulaCodigo)->first();
            
            if (!$aula) {
                Log::error("❌ Código de Aula '{$aulaCodigo}' no existe en la base de datos");
                return response()->json([
                    'success' => false,
                    'message' => 'CODIGO AULA INVALIDO'
                ], 400);
            }
            
            $aulaId = $aula->id;
            $aulaNombre = $aula->nombre;
            
            Log::info("✅ Aula validada por código: {$aulaCodigo} -> ID {$aulaId} ({$aulaNombre})");
        } else {
            Log::warning("⚠️ No se recibió código de aula, se procederá sin validación específica");
        }

        // 5. VERIFICAR HORARIO (El semáforo ahora es más inteligente)
        $estado = $this->horarioService->verificarEstadoAsistencia(
            $estudiante->id, 
            $aulaId
        );

        // (Re-verificamos duplicados en el semáforo)
        if ($estado['puede_marcar']) {
             $periodo = Periodo::find($estado['periodo_id']);
             if ($periodo) {
                 $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodo, Carbon::today());
                 if ($yaMarco) {
                    $estado['puede_marcar'] = false;
                    $estado['mensaje'] = 'ASISTENCIA_YA_REGISTRADA';
                 }
             }
        }

        if (!$estado['puede_marcar']) {
            $mensajesAmigables = [
                'FUERA_DE_TOLERANCIA' => 'FUERA DE HORARIO',
                'SIN_CLASE' => 'SIN CLASE AHORA',
                'SIN_CLASE_EN_AULA' => 'AULA INCORRECTA',
                'ASISTENCIA_YA_REGISTRADA' => 'YA REGISTRADO HOY' // <-- Mensaje del semáforo
            ];
            
            $mensaje = $mensajesAmigables[$estado['mensaje']] ?? 'NO PERMITIDO';
            
            Log::warning("⚠️ Asistencia denegada: UID {$data['uid']}, Aula {$aulaNombre}, Razón: {$mensaje}");
            
            return response()->json([
                'success' => false,
                'message' => $mensaje
            ], 403);
        }

        // 6. VERIFICAR DUPLICADOS (Capa de seguridad 2, por si acaso)
        $now = Carbon::now();
        $periodoId = $estado['periodo_id'];

        $yaMarco = Asistencia::where('uid', $estudiante->uid)
            ->where('periodo_id', $periodoId)
            ->where('accion', 'ENTRADA')
            ->whereDate('fecha_hora', $now->toDateString())
            ->exists();

        if ($yaMarco) {
            Log::warning("⚠️ Asistencia duplicada (Capa 2): UID {$data['uid']}, Periodo {$periodoId}");
            return response()->json([
                'success' => false,
                'message' => 'YA REGISTRADO HOY'
            ], 409);
        }

        // 7. CALCULAR ESTADO DE LLEGADA
        $estadoLlegada = $this->calcularEstadoLlegada($estado['periodo_id'], $now);

        // 8. REGISTRAR ASISTENCIA
        $asistencia = Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto,
            'accion' => $data['accion'],
            'modo' => $data['modo'],
            'fecha_hora' => $now,
            'curso_id' => $estado['curso_id'],
            'periodo_id' => $periodoId,
            'estado_llegada' => $estadoLlegada,
        ]);

        $aulaInfo = $aulaId ? " - Aula: {$aulaNombre} (Código: {$data['aula_codigo']})" : " - Sin aula específica";
        Log::info("✅ Asistencia RFID registrada: {$estudiante->nombreCompleto}, Periodo: {$periodoId}, Estado: {$estadoLlegada}{$aulaInfo}");

        return response()->json([
            'success' => true,
            'message' => 'ASISTENCIA OK',
            'estudiante' => $estudiante->nombreCompleto,
            'estado_llegada' => $estadoLlegada,
            'aula' => $aulaNombre,
            'data' => $asistencia
        ], 201);
    }

    /**
     * ENDPOINT OFFLINE LOTE (¡MODIFICADO!)
     * Ruta: POST /api/asistencia/batch
     */
    public function storeBatch(Request $request)
    {
        Log::info('📦 Sincronización en lote (storeBatch) recibida');

        $validationRules = [
            '*.uid' => 'required|string|max:255',
            '*.accion' => 'required|string|in:ENTRADA,SALIDA',
            '*.modo' => 'required|string|max:255',
            '*.fecha' => 'required|date_format:d/m/Y',
            '*.hora' => 'required|date_format:H:i:s',
        ];

        try {
            $registros = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de lote inválidos',
                'errors' => $e->errors()
            ], 400);
        }

        $storedCount = 0;
        $failedRecords = [];

        foreach ($registros as $registro) {
            try {
                $estudiante = Estudiante::where('uid', $registro['uid'])->first();

                if (!$estudiante) {
                    $failedRecords[] = [ 'record' => $registro, 'error' => 'UID no encontrado' ];
                    continue;
                }

                if (!$estudiante->estado) {
                    $failedRecords[] = [ 'record' => $registro, 'error' => 'Cuenta inactiva' ];
                    continue;
                }

                $fecha_hora_str = $registro['fecha'] . ' ' . $registro['hora'];
                $fecha_hora = Carbon::createFromFormat('d/m/Y H:i:s', $fecha_hora_str);
                
                // --- INICIO: NUEVA VERIFICACIÓN DUPLICADOS (BATCH) ---
                $periodo = $this->horarioService->getPeriodoPorHora($fecha_hora->format('H:i:s'));
                if ($periodo) {
                    $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodo, $fecha_hora);
                    if ($yaMarco) {
                         $failedRecords[] = ['record' => $registro, 'error' => 'Conflicto: Asistencia ya registrada'];
                         Log::warning("📦 storeBatch: Registro duplicado descartado para UID {$registro['uid']}");
                         continue; // Omitir este registro
                    }
                }
                // --- FIN: NUEVA VERIFICACIÓN DUPLICADOS (BATCH) ---

                Asistencia::create([
                    'uid' => $registro['uid'],
                    'nombre' => $estudiante->nombreCompleto,
                    'accion' => $registro['accion'],
                    'modo' => $registro['modo'],
                    'fecha_hora' => $fecha_hora,
                    'periodo_id' => $periodo ? $periodo->id : null, // <-- MEJORADO
                    'curso_id' => null,
                    'estado_llegada' => $periodo ? $this->calcularEstadoLlegada($periodo->id, $fecha_hora) : null, // <-- MEJORADO
                ]);

                $storedCount++;

            } catch (\Exception $e) {
                Log::error("Error en lote para UID {$registro['uid']}: {$e->getMessage()}");
                $failedRecords[] = [
                    'record' => $registro, 
                    'error' => $e->getMessage()
                ];
            }
        }

        if ($storedCount > 0) {
            $message = "{$storedCount} registros sincronizados.";
            if (!empty($failedRecords)) {
                $message .= " " . count($failedRecords) . " fallaron.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'failed_records' => $failedRecords
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Ningún registro pudo ser procesado',
            'failed_records' => $failedRecords
        ], 400);
    }

    /**
     * ENDPOINT ANTIGUO (SIN CAMBIOS, ESTÁ CORRECTO)
     * Ruta: POST /api/asistencia/rfid
     */
    public function storeRfid(Request $request)
    {
        Log::info('📡 Intento de asistencia RFID (storeRfid):', $request->all());

        $request->validate(['uid' => 'required|string']);

        $estudiante = Estudiante::where('uid', $request->uid)->where('estado', 1)->first();

        if (!$estudiante) {
            Log::warning('❌ Tarjeta RFID no autorizada: ' . $request->uid);
            return response()->json(['success' => false, 'message' => 'ESTUDIANTE_NO_AUTORIZADO'], 404);
        }
        
        $claseActual = $this->horarioService->getClaseActualParaEstudiante($estudiante);

        if (!$claseActual) {
            Log::info("❌ Registro denegado (sin clase): {$estudiante->nombreCompleto}");
            return response()->json(['success' => false, 'message' => 'SIN_CLASE_AHORA'], 400);
        }

        $periodoActual = $claseActual->periodo;
        $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodoActual);

        if ($yaMarco) {
            Log::info("❌ Registro denegado (duplicado): {$estudiante->nombreCompleto}");
            return response()->json(['success' => false, 'message' => 'ASISTENCIA_YA_REGISTRADA'], 409);
        }
            
        try {
            $asistencia = Asistencia::create([
                'uid' => $estudiante->uid,
                'nombre' => $estudiante->nombreCompleto,
                'fecha_hora' => Carbon::now(),
                'accion' => 'ENTRADA',
                'modo' => 'ONLINE',
                'estado_llegada' => $this->calcularEstadoLlegada($periodoActual->id, Carbon::now()),
                'periodo_id' => $periodoActual->id,
                'curso_id' => $claseActual->curso_id,
            ]);

            Log::info("✅ Asistencia REGISTRADA (RFID): {$estudiante->nombreCompleto}");
            return response()->json([
                'success' => true, 
                'message' => 'ASISTENCIA_REGISTRADA',
                'data' => $asistencia
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al guardar asistencia RFID: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'ERROR_INTERNO_SERVIDOR'
            ], 500);
        }
    }

    /**
     * HELPER (SIN CAMBIOS)
     */
    private function calcularEstadoLlegada($periodoId, Carbon $horaLlegada)
    {
        $periodo = Periodo::find($periodoId);
        
        if (!$periodo) {
            return 'desconocido';
        }

        $horaInicio = Carbon::parse($periodo->hora_inicio);
        $horaFinTolerancia = $horaInicio->copy()->addMinutes($periodo->tolerancia_ingreso_minutos);

        if ($horaLlegada <= $horaInicio) {
            return 'a_tiempo';
        } elseif ($horaLlegada <= $horaFinTolerancia) {
            return 'tarde';
        } else {
            return 'falta';
        }
    }
}