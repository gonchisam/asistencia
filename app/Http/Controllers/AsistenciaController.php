<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Aula;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\HorarioService;

class AsistenciaController extends Controller
{
    protected $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    /**
     * NUEVO MÉTODO "SEMÁFORO"
     * Verifica si un estudiante puede marcar asistencia en este momento.
     * Usado por Arduino (online) y la App Móvil.
     * Ruta: GET /api/asistencia/verificar
     */
    public function verificarEstadoAsistencia(Request $request)
    {
        Log::info('🔔 Petición semáforo recibida:', $request->all());

        $estudiante = null;
        $aulaId = null;

        // 1. IDENTIFICAR ESTUDIANTE (por UID o ID)
        if ($request->has('uid_tarjeta')) {
            // Búsqueda para Arduino por UID de tarjeta
            $estudiante = Estudiante::where('uid', $request->uid_tarjeta)->first();
        } elseif ($request->has('student_id')) {
            // Búsqueda para App Móvil por ID
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

        // 3. OBTENER AULA (si se proporciona código)
        if ($request->has('aula_codigo')) {
            $aulaCodigo = strtoupper(trim($request->aula_codigo));
            $aula = Aula::where('codigo', $aulaCodigo)->first();
            
            if ($aula) {
                $aulaId = $aula->id;
                Log::info("✅ Aula identificada en semáforo: {$aulaCodigo} -> ID {$aulaId}");
            } else {
                Log::warning("⚠️ Código de aula no encontrado: {$aulaCodigo}");
                // No retornamos error aquí, solo continuamos sin aula específica
            }
        }

        // 4. USAR EL SERVICIO PARA VERIFICACIÓN COMPLETA
        try {
            $resultado = $this->horarioService->verificarEstadoAsistencia($estudiante->id, $aulaId);
            
            Log::info("📊 Resultado semáforo para {$estudiante->nombreCompleto}:", $resultado);

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
     * MÉTODO EXISTENTE MODIFICADO (para Arduino)
     * Obtiene la lista de estudiantes para un dispositivo (aula) y
     * AÑADE EL ESTADO 'marco_hoy' para la lógica offline.
     *
     * (VERSIÓN CORREGIDA - MÁS ROBUSTA)
     */
    public function getEstudiantesPorDispositivo(Request $request, $device_id)
    {
        Log::info("Iniciando sincronización de lista para aula: " . $device_id);

        $aula = \App\Models\Aula::where('codigo', $device_id)->first();

        if (!$aula) {
            Log::error("Aula no encontrada con codigo: " . $device_id);
            return response()->json(['error' => 'Aula no encontrada con codigo: ' . $device_id], 404);
        }

        // --- INICIO DE CORRECCIÓN ---
        // $diaSemana = Carbon::now()->locale('es')->dayName; // <-- ANTIGUO (Devolvía "miércoles")
        $diaSemana = Carbon::now()->dayOfWeekIso; // <-- CORRECCIÓN 1 (Devuelve 3 para miércoles)
        Log::info("Buscando horarios para el día (ISO): " . $diaSemana);

        // 1. Buscamos todos los horarios que (A) son en esta aula Y (B) son hoy.
        $horariosHoyEnAula = \App\Models\CursoHorario::where('aula_id', $aula->id)
                                    // ->where('dia', $diaSemana) // <-- ANTIGUO (Columna 'dia' no existe)
                                    ->where('dia_semana', $diaSemana) // <-- CORRECCIÓN 2 (Columna 'dia_semana')
                                    ->with([
                                        // 2. Traemos el curso de ese horario y SUS estudiantes
                                        'curso' => function ($query) {
                                            $query->with('estudiantes');
                                        },
                                        // 3. Traemos el periodo de ese horario
                                        'periodo'
                                    ])
                                    ->get();
        
        if ($horariosHoyEnAula->isEmpty()) {
             Log::info("No se encontraron horarios hoy (" . $diaSemana . ") para el aula " . $device_id);
             return response()->json([]); // Devolver lista vacía, no es un error
        }

        $listaEstudiantes = collect();
        Log::info("Horarios encontrados: " . $horariosHoyEnAula->count());

        // Iteramos sobre los horarios encontrados
        foreach ($horariosHoyEnAula as $horario) {
            
            if (!$horario->curso) {
                Log::warning("El horario ID " . $horario->id . " no tiene un curso asociado.");
                continue;
            }
            
            // Iteramos sobre los estudiantes de ESE curso
            foreach ($horario->curso->estudiantes as $estudiante) {
                
                // Evitar duplicados (un estudiante puede tener 2 clases en la misma aula)
                if (!$listaEstudiantes->contains('id', $estudiante->id)) {
                    
                    $yaMarco = false;
                    
                    // Verificamos si el estudiante ya marcó en ESTE periodo específico HOY
                    if ($horario->periodo) {
                        $yaMarco = $this->horarioService->verificarAsistenciaExistente(
                            $estudiante, 
                            $horario->periodo, // Usamos el periodo del horario
                            Carbon::today() // Nos aseguramos que sea hoy
                        );
                    } else {
                        Log::warning("El horario ID " . $horario->id . " no tiene un periodo asociado.");
                    }

                    $listaEstudiantes->push([
                        'id' => $estudiante->id,
                        'nombre' => $estudiante->nombre_completo,
                        'uid' => $estudiante->uid,
                        'estado' => $estudiante->estado, 
                        'marco_hoy' => $yaMarco, // El campo clave
                    ]);
                }
            }
        }
        // --- FIN DE CORRECCIÓN ---

        Log::info("Sincronización de lista completa. Enviando " . $listaEstudiantes->count() . " estudiantes.");
        return response()->json($listaEstudiantes);
    }
    
    public function syncOfflineAsistencias(Request $request)
    {
        Log::info('📦 Inicio de Sincronización Offline');

        // El Arduino debe enviar un JSON Array
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

            // 1. Validar datos básicos
            if (empty($record['uid']) || empty($record['fecha']) || empty($record['hora'])) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: Datos básicos faltantes");
                continue;
            }
            
            // 2. Encontrar al estudiante
            $estudiante = Estudiante::where('uid', $record['uid'])->where('estado', 1)->first();
            if (!$estudiante) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: UID no encontrado o inactivo - {$record['uid']}");
                continue;
            }

            // 3. Parsear fecha y hora
            // Formato de Arduino: "dd/mm/YYYY" y "HH:MM:SS"
            try {
                $fecha = Carbon::createFromFormat('d/m/Y', $record['fecha']);
                $hora = $record['hora']; // ej: "10:30:00"
                Log::info("📅 Registro {$index}: Fecha parseada - {$fecha->format('Y-m-d')}, Hora - {$hora}");
            } catch (\Exception $e) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: Formato de fecha/hora inválido - {$e->getMessage()}");
                continue;
            }

            // 4. Encontrar el Periodo
            $periodo = $this->horarioService->getPeriodoPorHora($hora);
            if (!$periodo) {
                $descartados_error++;
                Log::warning("❌ Registro {$index}: No se encontró período para la hora {$hora}");
                continue;
            }

            Log::info("⏰ Registro {$index}: Período encontrado - {$periodo->nombre} ({$periodo->hora_inicio} - {$periodo->hora_fin})");

            // 5. --- LÓGICA DE CONFLICTO ---
            // Revisamos si ya existe una asistencia para ese estudiante,
            // en ese período, en esa fecha.
            $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodo, $fecha);

            if ($yaMarco) {
                // CONFLICTO: La App (u otro registro) ganó. Descartamos el registro offline.
                $descartados_conflicto++;
                Log::info("⚡ Registro {$index}: Descartado (conflicto/duplicado) - Ya existe asistencia");
                continue; // Siguiente registro
            }

            // 6. --- REGISTRO VÁLIDO ---
            // Si no hay conflicto, buscamos la clase y la guardamos
            // Para obtener el contexto de la clase, necesitamos simular el horario en esa fecha/hora
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
     * HELPER: Obtiene la clase de un estudiante en una fecha y hora específica
     */
    private function getClaseParaEstudianteEnFechaHora(Estudiante $estudiante, Carbon $fecha, string $hora)
    {
        $diaSemana = $fecha->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
        
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
                      // --- INICIO DE LA CORRECCIÓN ---
                      ->with('curso.materia', 'aula'); // Se usa 'curso.materia' en lugar de 'materia'
                      // --- FIN DE LA CORRECCIÓN ---
            }])
            ->first();

        if ($clase && $clase->horarios->isNotEmpty()) {
            return $clase->horarios->first();
        }

        return null;
    }

    /**
     * ENDPOINT PRINCIPAL PARA ARDUINO/RFID - OPCIÓN B (CÓDIGO DE AULA)
     * Ruta: POST /api/asistencia
     */
    public function store(Request $request)
    {
        Log::info('📡 Petición RFID recibida:', $request->all());

        // 1. VALIDAR DATOS DE ENTRADA
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

        // 2. BUSCAR ESTUDIANTE
        $estudiante = Estudiante::where('uid', $data['uid'])->first();

        if (!$estudiante) {
            Log::warning("⚠️ UID no encontrado: {$data['uid']}");
            return response()->json([
                'success' => false,
                'message' => 'UID NO ENCONTRADO'
            ], 404);
        }

        // 3. VALIDAR ESTADO ACTIVO
        if (!$estudiante->estado) {
            Log::warning("⚠️ Cuenta inactiva: UID {$data['uid']}");
            return response()->json([
                'success' => false,
                'message' => 'CUENTA INACTIVA'
            ], 403);
        }

        // 4. ✅ BUSCAR AULA POR CÓDIGO (NO POR ID)
        $aulaId = null;
        $aulaNombre = 'Sin aula';
        
        if (!empty($data['aula_codigo'])) {
            $aulaCodigo = strtoupper(trim($data['aula_codigo']));
            
            // Buscar el aula por su código
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

        // 5. VERIFICAR HORARIO Y PERMISOS (CONFIANZA CERO)
        $estado = $this->horarioService->verificarEstadoAsistencia(
            $estudiante->id, 
            $aulaId
        );

        if (!$estado['puede_marcar']) {
            // Mensajes amigables para el LCD
            $mensajesAmigables = [
                'FUERA_DE_TOLERANCIA' => 'FUERA DE HORARIO',
                'SIN_CLASE' => 'SIN CLASE AHORA',
                'SIN_CLASE_EN_AULA' => 'AULA INCORRECTA'
            ];
            
            $mensaje = $mensajesAmigables[$estado['mensaje']] ?? 'NO PERMITIDO';
            
            Log::warning("⚠️ Asistencia denegada: UID {$data['uid']}, Aula {$aulaNombre}, Razón: {$mensaje}");
            
            return response()->json([
                'success' => false,
                'message' => $mensaje
            ], 403);
        }

        // 6. VERIFICAR DUPLICADOS
        $now = Carbon::now();
        $periodoId = $estado['periodo_id'];

        $yaMarco = Asistencia::where('uid', $estudiante->uid)
            ->where('periodo_id', $periodoId)
            ->where('accion', 'ENTRADA')
            ->whereDate('fecha_hora', $now->toDateString())
            ->exists();

        if ($yaMarco) {
            Log::warning("⚠️ Asistencia duplicada: UID {$data['uid']}, Periodo {$periodoId}");
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
     * ENDPOINT PARA SINCRONIZACIÓN OFFLINE (LOTE)
     * Ruta: POST /api/asistencia/batch
     */
    public function storeBatch(Request $request)
    {
        Log::info('📦 Sincronización en lote recibida');

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
                    $failedRecords[] = [
                        'record' => $registro, 
                        'error' => 'UID no encontrado'
                    ];
                    continue;
                }

                if (!$estudiante->estado) {
                    $failedRecords[] = [
                        'record' => $registro, 
                        'error' => 'Cuenta inactiva'
                    ];
                    continue;
                }

                $fecha_hora_str = $registro['fecha'] . ' ' . $registro['hora'];
                $fecha_hora = Carbon::createFromFormat('d/m/Y H:i:s', $fecha_hora_str);

                Asistencia::create([
                    'uid' => $registro['uid'],
                    'nombre' => $estudiante->nombreCompleto,
                    'accion' => $registro['accion'],
                    'modo' => $registro['modo'],
                    'fecha_hora' => $fecha_hora,
                    'periodo_id' => null,
                    'curso_id' => null,
                    'estado_llegada' => null,
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
     * MÉTODO EXISTENTE MODIFICADO (para Arduino RFID)
     * Almacena una asistencia proveniente de un dispositivo RFID.
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

        // --- INICIO DE LÓGICA DE VERIFICACIÓN (Confianza Cero) ---
        
        // 1. ¿Tiene clase ahora?
        $claseActual = $this->horarioService->getClaseActualParaEstudiante($estudiante);

        if (!$claseActual) {
            Log::info("❌ Registro denegado (sin clase): {$estudiante->nombreCompleto}");
            return response()->json(['success' => false, 'message' => 'SIN_CLASE_AHORA'], 400);
        }

        // 2. ¿Ya marcó hoy en este periodo?
        // 2. ¿Ya marcó hoy en este periodo?
        $periodoActual = $claseActual->periodo;
        $yaMarco = $this->horarioService->verificarAsistenciaExistente($estudiante, $periodoActual);

        if ($yaMarco) {
            Log::info("❌ Registro denegado (duplicado): {$estudiante->nombreCompleto}");
            return response()->json(['success' => false, 'message' => 'ASISTENCIA_YA_REGISTRADA'], 409);
        }
            
        // --- FIN DE LÓGICA DE VERIFICACIÓN ---

        // Si pasó las validaciones, registramos
        try {
            $asistencia = Asistencia::create([
                // ✅ ELIMINADO: 'student_id' (no existe en la tabla)
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
     * HELPER: Calcula estado de llegada
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