<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Aula;
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
     * ENDPOINT PRINCIPAL PARA ARDUINO/RFID - OPCIÓN B (CÓDIGO DE AULA)
     * Ruta: POST /api/asistencia
     * 
     * El Arduino debe enviar JSON:
     * {
     *   "uid": "A1B2C3D4",
     *   "accion": "ENTRADA",
     *   "modo": "ONLINE",
     *   "aula_codigo": "AULA-101"
     * }
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
                // ✅ CAMBIO CLAVE: Aceptar código (string) en lugar de ID (integer)
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

        // 5. VERIFICAR HORARIO Y PERMISOS
        $estado = $this->horarioService->verificarEstadoAsistencia(
            $estudiante->id, 
            $aulaId // Ahora es el ID obtenido del código
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
     * HELPER: Calcula estado de llegada
     */
    private function calcularEstadoLlegada($periodoId, Carbon $horaLlegada)
    {
        $periodo = \App\Models\Periodo::find($periodoId);
        
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