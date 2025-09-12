<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante; // Importa el modelo Estudiante
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importa el Facade Log

class AsistenciaController extends Controller
{
    /**
     * Almacena un registro de asistencia individual (usado para el modo ONLINE).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|max:255', // Añadido max:255 para seguridad
            'accion' => 'required|string|in:ENTRADA,SALIDA',
            'modo' => 'required|string|max:255' // Añadido max:255 para seguridad
        ]);

        // Buscar el nombre del estudiante según el UID en la base de datos
        $nombre = $this->getNombreEstudiante($data['uid']);

        $asistencia = Asistencia::create([
            'uid' => $data['uid'],
            'nombre' => $nombre,
            'accion' => $data['accion'],
            'modo' => $data['modo'],
            'fecha_hora' => now() // Laravel automáticamente usa el timestamp actual
        ]);

        Log::info("Asistencia individual registrada: UID {$data['uid']}, Acción {$data['accion']}, Modo {$data['modo']}");

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada exitosamente.',
            'data' => $asistencia
        ], 201); // Código 201 Created para indicar que el recurso fue creado
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
            return $estudiante->nombre;
        } else {
            // Log para saber qué UIDs desconocidos están intentando registrar asistencia
            Log::warning("UID desconocido intentando registrar asistencia: {$uid}");
            return 'Desconocido';
        }
    }

    /**
     * Almacena múltiples registros de asistencia enviados en lote (usado para el modo OFFLINE_SYNC).
     */
    public function storeBatch(Request $request)
    {
        // Validar que el request sea un array de objetos con la estructura esperada
        // Los asteriscos (*) indican que la validación se aplica a cada elemento del array.
        $validationRules = [
            '*.uid' => 'required|string|max:255',
            '*.accion' => 'required|string|in:ENTRADA,SALIDA',
            '*.modo' => 'required|string|max:255',
            '*.fecha' => 'required|date_format:d/m/Y', // Formato esperado: DD/MM/YYYY
            '*.hora' => 'required|date_format:H:i:s',   // Formato esperado: HH:MM:SS
        ];

        // Si la validación falla, Laravel automáticamente lanza una excepción
        // y retorna una respuesta JSON 422 (Unprocessable Entity).
        $registros = $request->validate($validationRules);

        $storedCount = 0;
        $failedRecords = []; // Para almacenar registros que fallaron individualmente

        foreach ($registros as $registro) {
            try {
                // Reconstruir el objeto Carbon a partir de las cadenas de fecha y hora
                $fecha_hora_str = $registro['fecha'] . ' ' . $registro['hora'];
                $fecha_hora = Carbon::createFromFormat('d/m/Y H:i:s', $fecha_hora_str);

                // Obtener el nombre del estudiante de la base de datos para este registro
                $nombre = $this->getNombreEstudiante($registro['uid']);

                Asistencia::create([
                    'uid' => $registro['uid'],
                    'nombre' => $nombre, // Usar el nombre obtenido
                    'accion' => $registro['accion'],
                    'modo' => $registro['modo'],
                    'fecha_hora' => $fecha_hora,
                ]);
                $storedCount++;
            } catch (\Exception $e) {
                // Registrar el error para este registro específico, pero continuar procesando los demás
                Log::error("Error al almacenar asistencia en lote para UID {$registro['uid']}: " . $e->getMessage());
                $failedRecords[] = $registro; // Añadir el registro fallido a la lista
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
                    'success' => true, // Todavía se considera éxito si algunos se procesaron
                    'message' => "{$storedCount} registros procesados. " . count($failedRecords) . " registros fallaron.",
                    'failed_records' => $failedRecords // Opcional: devolver los registros que fallaron
                ], 200); // O podrías usar 207 Multi-Status si tu cliente lo soporta
            }
        } else {
            Log::error("Sincronización batch fallida: Ningún registro pudo ser procesado.");
            return response()->json([
                'success' => false,
                'message' => 'Ningún registro pudo ser procesado.',
                'failed_records' => $failedRecords // Devolver los registros que fallaron
            ], 400); // Código 400 Bad Request si no se pudo procesar nada
        }
    }
}