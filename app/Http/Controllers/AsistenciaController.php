<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AsistenciaController extends Controller
{
    /**
     * Almacena un registro de asistencia individual (usado para el modo ONLINE).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|max:255',
            'accion' => 'required|string|in:ENTRADA,SALIDA',
            'modo' => 'required|string|max:255'
        ]);

        // Buscar el estudiante según el UID
        $estudiante = Estudiante::where('uid', $data['uid'])->first();

        // Validar si el estudiante existe
        if (!$estudiante) {
            Log::warning("Asistencia denegada (UID No Encontrado): {$data['uid']}");
            return response()->json([
                'success' => false,
                'message' => 'Estudiante NO ENCONTRADO'
            ], 404);
        }

        // Validar estado del estudiante
        if (!$estudiante->estado) {
            Log::warning("Asistencia denegada (Cuenta Inactiva): UID {$data['uid']}");
            return response()->json([
                'success' => false,
                'message' => 'ESTUDIANTE INACTIVO'
            ], 403);
        }

        $asistencia = Asistencia::create([
            'uid' => $data['uid'],
            'nombre' => $estudiante->nombreCompleto ?? $estudiante->nombre,
            'accion' => $data['accion'],
            'modo' => $data['modo'],
            'fecha_hora' => now()
        ]);

        Log::info("Asistencia individual registrada: UID {$data['uid']}, Acción {$data['accion']}, Modo {$data['modo']}");

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada exitosamente.',
            'data' => $asistencia
        ], 201);
    }

    /**
     * Endpoint para registrar asistencia desde el Arduino (RFID).
     * Recibe el UID (código RFID) directamente.
     */
    public function registrarAsistenciaRfid(Request $request)
    {
        Log::info('Intento de asistencia RFID recibido:', $request->all());

        // Validar que el 'uid' (código RFID) venga en la petición
        $data = $request->validate([
            'uid' => 'required|string|max:50'
        ]);

        $uid = $data['uid'];

        // Buscar al estudiante por su UID (código RFID)
        $estudiante = Estudiante::where('uid', $uid)->first();

        // Validar si el estudiante existe
        if (!$estudiante) {
            Log::warning("Asistencia RFID denegada (UID No Encontrado): {$uid}");
            return response()->json([
                'success' => false, 
                'message' => 'Estudiante NO ENCONTRADO'
            ], 404);
        }

        // Validar estado del estudiante
        if (!$estudiante->estado) {
            Log::warning("Asistencia RFID denegada (Cuenta Inactiva): UID {$uid}");
            return response()->json([
                'success' => false, 
                'message' => 'ESTUDIANTE INACTIVO'
            ], 403);
        }

        // Determinar la acción (ENTRADA o SALIDA) - Lógica automática
        $ultimaAsistencia = Asistencia::where('uid', $uid)
                                     ->orderBy('fecha_hora', 'desc')
                                     ->first();

        $accion = 'ENTRADA'; // Por defecto es ENTRADA
        if ($ultimaAsistencia && $ultimaAsistencia->accion === 'ENTRADA') {
            // Si la última acción fue ENTRADA, la nueva es SALIDA
            $accion = 'SALIDA';
        }

        // Registrar la asistencia
        Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto ?? $estudiante->nombre,
            'accion' => $accion,
            'modo' => 'RFID',
            'fecha_hora' => now()
        ]);

        Log::info("Asistencia RFID registrada: UID {$uid}, Nombre: {$estudiante->nombreCompleto}, Accion: {$accion}");

        // Respuesta exitosa para el Arduino
        return response()->json([
            'success' => true,
            'message' => "Asistencia Registrada ({$accion})",
            'estudiante' => $estudiante->nombreCompleto ?? $estudiante->nombre
        ], 201);
    }

    /**
     * Almacena múltiples registros de asistencia enviados en lote (usado para el modo OFFLINE_SYNC).
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

        $storedCount = 0;
        $failedRecords = [];

        foreach ($registros as $registro) {
            try {
                // Buscar estudiante para validar estado
                $estudiante = Estudiante::where('uid', $registro['uid'])->first();

                // Validar si el estudiante existe y está activo
                if (!$estudiante) {
                    Log::warning("Asistencia batch denegada (UID No Encontrado): {$registro['uid']}");
                    $failedRecords[] = [
                        'record' => $registro,
                        'error' => 'Estudiante NO ENCONTRADO'
                    ];
                    continue;
                }

                if (!$estudiante->estado) {
                    Log::warning("Asistencia batch denegada (Cuenta Inactiva): UID {$registro['uid']}");
                    $failedRecords[] = [
                        'record' => $registro,
                        'error' => 'ESTUDIANTE INACTIVO'
                    ];
                    continue;
                }

                // Reconstruir el objeto Carbon
                $fecha_hora_str = $registro['fecha'] . ' ' . $registro['hora'];
                $fecha_hora = Carbon::createFromFormat('d/m/Y H:i:s', $fecha_hora_str);

                Asistencia::create([
                    'uid' => $registro['uid'],
                    'nombre' => $estudiante->nombreCompleto ?? $estudiante->nombre,
                    'accion' => $registro['accion'],
                    'modo' => $registro['modo'],
                    'fecha_hora' => $fecha_hora,
                ]);
                $storedCount++;
            } catch (\Exception $e) {
                Log::error("Error al almacenar asistencia en lote para UID {$registro['uid']}: " . $e->getMessage());
                $failedRecords[] = [
                    'record' => $registro,
                    'error' => $e->getMessage()
                ];
            }
        }

        if ($storedCount > 0) {
            if (empty($failedRecords)) {
                Log::info("Sincronización batch exitosa: {$storedCount} registros procesados.");
                return response()->json([
                    'success' => true,
                    'message' => "{$storedCount} registros procesados exitosamente."
                ], 200);
            } else {
                Log::warning("Sincronización batch parcial: {$storedCount} exitosos, " . count($failedRecords) . " fallidos.");
                return response()->json([
                    'success' => true,
                    'message' => "{$storedCount} registros procesados. " . count($failedRecords) . " registros fallaron.",
                    'failed_records' => $failedRecords
                ], 200);
            }
        } else {
            Log::error("Sincronización batch fallida: Ningún registro pudo ser procesado.");
            return response()->json([
                'success' => false,
                'message' => 'Ningún registro pudo ser procesado.',
                'failed_records' => $failedRecords
            ], 400);
        }
    }

    /**
     * Busca el nombre de un estudiante por su UID.
     * @param string $uid El UID del estudiante a buscar.
     * @return string El nombre del estudiante o 'Desconocido' si no se encuentra.
     */
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