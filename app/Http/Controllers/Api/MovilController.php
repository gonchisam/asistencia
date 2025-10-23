<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\Estudiante; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Validation\ValidationException; 
use Illuminate\Support\Carbon; 

class MovilController extends Controller
{
    /**
     * Maneja el inicio de sesión desde la app móvil.
     * --- ¡MODIFICADO CON LÓGICA ANTIFRAUDE! ---
     */
    public function login(Request $request)
    {
        Log::info('Intento de login móvil:', $request->all());

        // 1. VALIDAR DATOS DE ENTRADA (AÑADIMOS device_id)
        $request->validate([
            'correo' => 'required|email',
            'ci' => 'required|string',
            'device_id' => 'required|string|max:255', // Asegura que el device_id venga y no sea muy largo
        ]);

        $correo = $request->correo;
        $ci = $request->ci;
        $deviceIdRecibido = $request->device_id;

        // 2. BUSCAR AL ESTUDIANTE Y VALIDAR CREDENCIALES
        $estudiante = Estudiante::where('correo', $correo)->first();

        if (! $estudiante || $ci !== $estudiante->ci) { // Asumiendo que CI no está encriptado
            Log::warning("Fallo de login móvil (Credenciales): correo {$correo}");
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        
        // Verificar si tiene UID (necesario para la asistencia)
        if (empty($estudiante->uid)) {
            Log::error("Fallo de login móvil (Sin UID): Estudiante {$estudiante->id}");
             return response()->json([
                'success' => false,
                'message' => 'Error de cuenta: Falta el identificador (UID). Contacte a administración.'
            ], 403); // Forbidden
        }

        // --- INICIO: LÓGICA ANTIFRAUDE ---

        $deviceIdGuardado = $estudiante->device_id;

        // 3. VERIFICAR EL DEVICE_ID
        if ($deviceIdGuardado) {
            // El estudiante YA tiene un dispositivo vinculado
            if ($deviceIdGuardado !== $deviceIdRecibido) {
                // El ID del dispositivo actual NO coincide con el guardado
                Log::warning("Fallo de login móvil (Dispositivo no coincide): UID {$estudiante->uid}, Device Recibido {$deviceIdRecibido}, Guardado {$deviceIdGuardado}");
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta está vinculada a otro dispositivo. Si perdiste o cambiaste tu teléfono, contacta a administración para desvincularlo.'
                ], 403); // Forbidden
            }
            // Si coincide, todo bien, continuamos.
            Log::info("Login móvil: UID {$estudiante->uid} - Dispositivo verificado.");

        } else {
            // El estudiante NO tiene un dispositivo vinculado (es NULL en la BD)
            // Es el primer login desde un móvil O se desvinculó su dispositivo anterior.

            // 3.1 (Opcional pero recomendado) Verificar si este device_id ya está en uso por OTRO estudiante
            $otroEstudiante = Estudiante::where('device_id', $deviceIdRecibido)->first();
            if ($otroEstudiante) {
                 Log::warning("Fallo de login móvil (Device ID ya en uso): UID {$estudiante->uid} intentó usar Device ID {$deviceIdRecibido} perteneciente a UID {$otroEstudiante->uid}");
                 return response()->json([
                    'success' => false,
                    'message' => 'Este dispositivo ya está vinculado a otra cuenta. Contacta a administración.'
                ], 409); // Conflict
            }

            // 3.2 Si el device_id está libre, lo VINCULAMOS al estudiante actual
            $estudiante->device_id = $deviceIdRecibido;
            $estudiante->save(); // Guardamos el nuevo device_id en la base de datos
            Log::info("Login móvil: UID {$estudiante->uid} - Nuevo dispositivo vinculado: {$deviceIdRecibido}");
        }

        // --- FIN: LÓGICA ANTIFRAUDE ---


        // 4. GENERAR TOKEN Y RESPONDER (Si pasó todas las validaciones)
        $estudiante->tokens()->delete(); // Elimina tokens antiguos
        $token = $estudiante->createToken('auth_token_movil')->plainTextToken;

        Log::info("Login móvil exitoso y token generado: {$estudiante->uid}");

        return response()->json([
            'success' => true,
            'token' => $token,
            'estudiante' => $estudiante // Devuelve los datos del estudiante
        ]);
    }

    // ... (El resto de tus métodos: getPerfil, logout, registrarAsistencia, getHistorial, calcularDistanciaHaversine... quedan igual)
    public function getPerfil(Request $request)
    {
        return response()->json([
            'success' => true,
            'estudiante' => $request->user() 
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            Log::info("Logout móvil exitoso: {$request->user()->uid}");
            return response()->json(['success' => true, 'message' => 'Sesión cerrada exitosamente.']);
        } catch (\Exception $e) {
            Log::error("Error en logout móvil: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cerrar sesión.'], 500);
        }
    }

    public function registrarAsistencia(Request $request)
    {
        // CONSIDERACIÓN: Podrías añadir aquí también la validación del device_id
        // $deviceIdRecibido = $request->input('device_id'); // Necesitarías que Flutter lo envíe
        // $estudiante = $request->user();
        // if ($estudiante->device_id !== $deviceIdRecibido) { ... return error ... }
        
        $data = $request->validate([
            'accion' => 'required|string|in:ENTRADA,SALIDA',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        $estudiante = $request->user(); 

        $targetLat = (float) config('app.target_latitud', -17.336540);
        $targetLng = (float) config('app.target_longitud', -66.197478);
        $targetRadio = (float) config('app.target_radio_metros', 20);

        $distancia = $this->calcularDistanciaHaversine(
            $data['latitud'], $data['longitud'],
            $targetLat, $targetLng
        );

        if ($distancia > $targetRadio) {
            Log::warning("Asistencia MOVIL fuera de rango: UID {$estudiante->uid}, Distancia: {$distancia}m");
            return response()->json([
                'success' => false,
                'message' => 'Estás fuera del rango permitido ('.round($distancia, 0).'m). Acércate a la ubicación.'
            ], 403); 
        }

        $asistencia = Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto,
            'accion' => $data['accion'],
            'modo' => 'MOVIL', 
            'fecha_hora' => now()
        ]);

        Log::info("Asistencia MOVIL registrada: UID {$estudiante->uid}, Distancia: {$distancia}m");

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada exitosamente.',
            'data' => $asistencia
        ], 201);
    }

    private function calcularDistanciaHaversine($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; 
    }
    
    public function getHistorial(Request $request)
    {
        try {
            $estudiante = $request->user();
            if (!$estudiante) return response()->json(['success' => false, 'message' => 'Usuario no autenticado.'], 401);

            $periodo = $request->query('periodo', 'todos');
            $fechaInicio = $request->query('fecha_inicio'); 
            $fechaFin = $request->query('fecha_fin');    

            $query = Asistencia::where('uid', $estudiante->uid);

            if ($fechaInicio && $fechaFin) {
                try {
                    $inicio = Carbon::parse($fechaInicio)->startOfDay(); 
                    $fin = Carbon::parse($fechaFin)->endOfDay();     
                    $query->whereBetween('fecha_hora', [$inicio, $fin]);
                    Log::info("Historial solicitado por RANGO: {$inicio} a {$fin}");
                } catch (\Exception $e) {
                    Log::warning("Fechas de rango inválidas: $fechaInicio, $fechaFin");
                }
            } else {
                switch ($periodo) {
                    case 'dia': $query->whereDate('fecha_hora', Carbon::today()); break;
                    case 'semana': $query->whereBetween('fecha_hora', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); break;
                    case 'mes': $query->whereBetween('fecha_hora', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]); break;
                }
            }

            $historial = $query->orderBy('fecha_hora', 'desc')->get();
            Log::info("Historial solicitado para UID: {$estudiante->uid} (Periodo: $periodo, Rango: $fechaInicio-$fechaFin), encontrados: {$historial->count()}");

            return response()->json(['success' => true, 'historial' => $historial]);
        } catch (\Exception $e) {
            Log::error("Error al obtener historial para UID: {$request->user()->uid}. Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener el historial: ' . $e->getMessage()], 500);
        }
    }
}