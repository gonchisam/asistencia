<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Log;

class AsistenciaMovilController extends Controller
{
    /**
     * Registra una asistencia desde la app móvil validando la geolocalización.
     */
    public function registrar(Request $request)
    {
        $data = $request->validate([
            'accion' => 'required|string|in:ENTRADA,SALIDA',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        // 1. Obtener el estudiante autenticado
        $estudiante = $request->user(); // $request->user() es el Estudiante

        // 2. Obtener la ubicación objetivo desde el .env (con valores por defecto)
        $targetLat = (float) config('app.target_latitud', -17.336540);
        $targetLng = (float) config('app.target_longitud', -66.197478);
        $targetRadio = (float) config('app.target_radio_metros', 20);

        // 3. Calcular la distancia
        $distancia = $this->calcularDistanciaHaversine(
            $data['latitud'], $data['longitud'],
            $targetLat, $targetLng
        );

        // 4. Validar el radio
        if ($distancia > $targetRadio) {
            Log::warning("Asistencia MOVIL fuera de rango: UID {$estudiante->uid}, Distancia: {$distancia}m");
            return response()->json([
                'success' => false,
                'message' => 'Estás fuera del rango permitido ('.round($distancia, 0).'m). Acércate a la ubicación.'
            ], 403); // 403 Forbidden
        }

        // 5. Si todo está OK, registrar la asistencia
        $asistencia = Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto, // Usamos el accesor del modelo Estudiante
            'accion' => $data['accion'],
            'modo' => 'MOVIL_GPS', // Nuevo modo para identificar esta asistencia
            'fecha_hora' => now()
        ]);

        Log::info("Asistencia MOVIL registrada: UID {$estudiante->uid}, Distancia: {$distancia}m");

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada exitosamente.',
            'data' => $asistencia
        ], 201);
    }

    /**
     * Calcula la distancia entre dos puntos GPS en metros (Fórmula de Haversine).
     */
    private function calcularDistanciaHaversine($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Radio de la tierra en metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; // Distancia en metros
    }
}