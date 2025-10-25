<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\HorarioService; // <-- ¡NUEVO!

class AsistenciaController extends Controller
{
    // --- ¡NUEVO! Inyección del servicio ---
    protected $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    /**
     * --- ¡MÉTODO MODIFICADO! ---
     * Almacena un registro de asistencia desde el RFID (Arduino).
     * Esta ruta es la de 'POST /asistencia'
     */
    public function store(Request $request)
    {
        // 1. Validar que el RFID envió 'uid' y 'aula_id'
        $data = $request->validate([
            'uid' => 'required|string|max:255',
            'aula_id' => 'required|integer|exists:aulas,id', // Asumo que el RFID sabe su ID de aula
        ]);

        // Buscar el estudiante según el UID
        $estudiante = Estudiante::where('uid', $data['uid'])->first();

        // 2. Validar si el estudiante existe
        if (!$estudiante) {
            Log::warning("Asistencia RFID denegada (UID No Encontrado): {$data['uid']}");
            // Esta respuesta JSON es para el Arduino
            return response()->json([
                'success' => false,
                'message' => 'Estudiante NO ENCONTRADO'
            ], 404);
        }

        // 3. Validar estado del estudiante
        if (!$estudiante->estado) {
            Log::warning("Asistencia RFID denegada (Cuenta Inactiva): UID {$data['uid']}");
            // Esta respuesta JSON es para el Arduino
            return response()->json([
                'success' => false,
                'message' => 'ESTUDIANTE INACTIVO'
            ], 403);
        }
        
        $aulaId = $data['aula_id'];

        // 4. ¡NUEVA LÓGICA! Verificar si puede marcar (¡Aquí SÍ pasamos el $aulaId!)
        $estado = $this->horarioService->verificarEstadoAsistencia($estudiante->id, $aulaId);

        if ($estado['puede_marcar'] === false) {
            Log::warning("Asistencia RFID denegada (Fuera de Horario/Aula): UID {$data['uid']}, AulaID {$aulaId}, Msj: {$estado['mensaje']}");
            // El Arduino debe estar programado para leer este JSON y mostrar el 'message'
            return response()->json([
                'success' => false,
                'message' => $estado['mensaje'] // Ej: "No tienes clase en ESTA AULA..."
            ], 403); // 403 Forbidden
        }

        // 5. Si PUDO marcar, obtenemos el periodo_id
        $periodoId = $estado['periodo_id'];
        $now = Carbon::now();

        // 6. Verificar si ya marcó ENTRADA para este periodo hoy
        $yaMarco = Asistencia::where('uid', $estudiante->uid)
            ->where('periodo_id', $periodoId)
            ->where('accion', 'ENTRADA')
            ->whereDate('fecha_hora', $now->toDateString())
            ->exists();

        if ($yaMarco) {
            Log::warning("Asistencia RFID duplicada: UID {$data['uid']}, PeriodoID {$periodoId}");
            return response()->json([
                'success' => false,
                'message' => 'Ya registraste asistencia.'
            ], 409); // 409 Conflict
        }

        // 7. Registrar la asistencia (¡Ahora con periodo_id!)
        $asistencia = Asistencia::create([
            'uid' => $estudiante->uid,
            'periodo_id' => $periodoId, // <-- ¡NUEVO!
            'nombre' => $estudiante->nombreCompleto,
            'accion' => 'ENTRADA', // <-- Siempre ENTRADA
            'modo' => 'RFID_AULA_' . $aulaId, // Modo dinámico
            'fecha_hora' => $now
        ]);

        Log::info("Asistencia RFID registrada: UID {$data['uid']}, PeriodoID {$periodoId}, AulaID {$aulaId}");

        // Respuesta exitosa para el Arduino
        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada.',
            'estudiante' => $estudiante->nombreCompleto,
            'data' => $asistencia
        ], 201);
    }
    
    
    // --- MÉTODOS SIN MODIFICAR (LOS DEJAMOS COMO ESTABAN) ---
    // (Aunque 'registrarAsistenciaRfid' ya no se usa, lo dejamos por si acaso)
    
    /**
     * Endpoint para registrar asistencia desde el Arduino (RFID).
     * Recibe el UID (código RFID) directamente.
     * --- ESTE MÉTODO ES PARTE DE TU LÓGICA ANTIGUA ---
     */
    public function registrarAsistenciaRfid(Request $request)
    {
        Log::info('Intento de asistencia RFID (ANTIGUO) recibido:', $request->all());

        $data = $request->validate([
            'uid' => 'required|string|max:50'
        ]);
        $uid = $data['uid'];
        $estudiante = Estudiante::where('uid', $uid)->first();
        if (!$estudiante) {
            Log::warning("Asistencia RFID (ANTIGUO) denegada (UID No Encontrado): {$uid}");
            return response()->json(['success' => false, 'message' => 'Estudiante NO ENCONTRADO'], 404);
        }
        if (!$estudiante->estado) {
            Log::warning("Asistencia RFID (ANTIGUO) denegada (Cuenta Inactiva): UID {$uid}");
            return response()->json(['success' => false, 'message' => 'ESTUDIANTE INACTIVO'], 403);
        }
        $ultimaAsistencia = Asistencia::where('uid', $uid)->orderBy('fecha_hora', 'desc')->first();
        $accion = 'ENTRADA'; 
        if ($ultimaAsistencia && $ultimaAsistencia->accion === 'ENTRADA') {
            $accion = 'SALIDA';
        }
        Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto ?? $estudiante->nombre,
            'accion' => $accion,
            'modo' => 'RFID',
            'fecha_hora' => now()
        ]);
        Log::info("Asistencia RFID (ANTIGUO) registrada: UID {$uid}, Accion: {$accion}");
        return response()->json([
            'success' => true,
            'message' => "Asistencia Registrada ({$accion})",
            'estudiante' => $estudiante->nombreCompleto ?? $estudiante->nombre
        ], 201);
    }

    /**
     * Almacena múltiples registros de asistencia enviados en lote (usado para el modo OFFLINE_SYNC).
     * --- ESTE MÉTODO QUEDA IGUAL, PERO NO FUNCIONARÁ CON LA NUEVA LÓGICA DE PERIODOS ---
     * --- Habría que adaptarlo luego si se necesita la sincronización offline ---
     */
    public function storeBatch(Request $request)
    {
        $validationRules = [
            '*.uid' => 'required|string|max:255',
            '*.accion' => 'required|string|in:ENTRADA,SALIDA',
            '*.modo' => 'required|string|max:255',
            '*.fecha' => 'required|date_format:d/m/Y',
            '*.hora' => 'required|date_format:H:i:s',
        ];
        $registros = $request->validate($validationRules);
        // ... (Tu lógica de storeBatch sigue aquí)
        // ...
        $storedCount = 0;
        $failedRecords = [];

        foreach ($registros as $registro) {
            try {
                $estudiante = Estudiante::where('uid', $registro['uid'])->first();
                if (!$estudiante) {
                    $failedRecords[] = ['record' => $registro, 'error' => 'Estudiante NO ENCONTRADO'];
                    continue;
                }
                if (!$estudiante->estado) {
                    $failedRecords[] = ['record' => $registro, 'error' => 'ESTUDIANTE INACTIVO'];
                    continue;
                }
                $fecha_hora_str = $registro['fecha'] . ' ' . $registro['hora'];
                $fecha_hora = Carbon::createFromFormat('d/m/Y H:i:s', $fecha_hora_str);
                
                // --- NOTA: Esta inserción fallará si 'periodo_id' es 'NOT NULL' en tu BD
                // --- Por eso lo pusimos como 'nullable' en el Paso 1
                Asistencia::create([
                    'uid' => $registro['uid'],
                    'nombre' => $estudiante->nombreCompleto ?? $estudiante->nombre,
                    'accion' => $registro['accion'],
                    'modo' => $registro['modo'],
                    'fecha_hora' => $fecha_hora,
                    // 'periodo_id' quedará NULL
                ]);
                $storedCount++;
            } catch (\Exception $e) {
                Log::error("Error al almacenar asistencia en lote para UID {$registro['uid']}: " . $e->getMessage());
                $failedRecords[] = ['record' => $registro, 'error' => $e->getMessage()];
            }
        }
        // ... (El resto de tu lógica de respuesta)
        if ($storedCount > 0) {
            if (empty($failedRecords)) {
                return response()->json(['success' => true, 'message' => "{$storedCount} registros procesados exitosamente."], 200);
            } else {
                return response()->json(['success' => true, 'message' => "{$storedCount} registros procesados. " . count($failedRecords) . " registros fallaron.", 'failed_records' => $failedRecords], 200);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Ningún registro pudo ser procesado.', 'failed_records' => $failedRecords], 400);
        }
    }

    private function getNombreEstudiante($uid)
    {
        $estudiante = Estudiante::where('uid', $uid)->first();
        if ($estudiante) {
            return $estudiante->nombreCompleto ?? $estudiante->nombre;
        } else {
            Log::warning("UID desconocido intentando registrar asistencia: {$uid}");
            return 'Desconocido';
        }
    }
}