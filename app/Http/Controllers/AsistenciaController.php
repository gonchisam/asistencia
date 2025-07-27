<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante; // Import the Estudiante model
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string',
            'accion' => 'required|string|in:ENTRADA,SALIDA',
            'modo' => 'required|string'
        ]);

        // Buscar el nombre del estudiante según el UID en la base de datos
        $nombre = $this->getNombreEstudiante($data['uid']);

        $asistencia = Asistencia::create([
            'uid' => $data['uid'],
            'nombre' => $nombre,
            'accion' => $data['accion'],
            'modo' => $data['modo'],
            'fecha_hora' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada',
            'data' => $asistencia
        ], 201);
    }

    private function getNombreEstudiante($uid)
    {
        // Buscar el estudiante en la base de datos
        $estudiante = Estudiante::where('uid', $uid)->first();

        if ($estudiante) {
            return $estudiante->nombre;
        } else {
            return 'Desconocido'; // Or handle as an error if UID not found
        }
    }

    // ... (rest of your AsistenciaController code, including storeBatch)
    public function storeBatch(Request $request)
    {
        $registros = $request->validate([
            '*.uid' => 'required|string',
            '*.accion' => 'required|string|in:ENTRADA,SALIDA',
            '*.modo' => 'required|string',
            '*.fecha' => 'required|string', // Assuming 'fecha' is in 'DD/MM/YYYY'
            '*.hora' => 'required|string',  // Assuming 'hora' is in 'HH:MM:SS'
        ]);

        $storedCount = 0;
        foreach ($registros as $registro) {
            // Reconstruct datetime from separate date and time strings
            $fecha_hora_str = $registro['fecha'] . ' ' . $registro['hora'];
            $fecha_hora = Carbon::createFromFormat('d/m/Y H:i:s', $fecha_hora_str);

            // Fetch student name from database for batch records as well
            $nombre = $this->getNombreEstudiante($registro['uid']);

            try {
                Asistencia::create([
                    'uid' => $registro['uid'],
                    'nombre' => $nombre, // Use the fetched name
                    'accion' => $registro['accion'],
                    'modo' => $registro['modo'],
                    'fecha_hora' => $fecha_hora,
                ]);
                $storedCount++;
            } catch (\Exception $e) {
                // Log the error for this specific record, but continue processing others
                \Log::error("Error storing batch attendance for UID {$registro['uid']}: " . $e->getMessage());
            }
        }

        if ($storedCount > 0) {
            return response()->json(['success' => true, 'message' => "$storedCount registros procesados."]);
        } else {
            return response()->json(['success' => false, 'message' => 'Ningún registro pudo ser procesado.'], 400);
        }
    }
}