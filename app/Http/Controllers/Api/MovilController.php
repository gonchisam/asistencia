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
use App\Services\HorarioService;
use Illuminate\Support\Facades\Auth;

class MovilController extends Controller
{
    protected $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    /**
     * (El método login() no cambia)
     */
    public function login(Request $request)
    {
        Log::info('Intento de login móvil:', $request->all());
        $request->validate([
            'correo' => 'required|email',
            'ci' => 'required|string',
            'device_id' => 'required|string|max:255', 
        ]);
        $correo = $request->correo;
        $ci = $request->ci;
        $deviceIdRecibido = $request->device_id;

        $estudiante = Estudiante::where('correo', $correo)->first();
        if (! $estudiante || $ci !== $estudiante->ci) {
            Log::warning("Fallo de login móvil (Credenciales): correo {$correo}");
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        
        if (!$estudiante->estado) { 
            Log::warning("Fallo de login móvil (Cuenta Inactiva): correo {$correo}");
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta se encuentra desactivada. Contacta a administración.'
            ], 403);
        }

        if (empty($estudiante->uid)) {
            Log::error("Fallo de login móvil (Sin UID): Estudiante {$estudiante->id}");
            return response()->json([
                'success' => false,
                'message' => 'Error de cuenta: Falta el identificador (UID). Contacte a administración.'
            ], 403);
        }

        $deviceIdGuardado = $estudiante->device_id;
        if ($deviceIdGuardado) {
            if ($deviceIdGuardado !== $deviceIdRecibido) {
                Log::warning("Fallo de login móvil (Dispositivo no coincide): UID {$estudiante->uid}, Device Recibido {$deviceIdRecibido}, Guardado {$deviceIdGuardado}");
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta está vinculada a otro dispositivo. Si perdiste o cambiaste tu teléfono, contacta a administración para desvincularlo.'
                ], 403);
            }
            Log::info("Login móvil: UID {$estudiante->uid} - Dispositivo verificado.");
        } else {
            $otroEstudiante = Estudiante::where('device_id', $deviceIdRecibido)->first();
            if ($otroEstudiante) {
                 Log::warning("Fallo de login móvil (Device ID ya en uso): UID {$estudiante->uid} intentó usar Device ID {$deviceIdRecibido} perteneciente a UID {$otroEstudiante->uid}");
                return response()->json([
                    'success' => false,
                    'message' => 'Este dispositivo ya está vinculado a otra cuenta. Contacta a administración.'
                ], 409);
            }

            $estudiante->device_id = $deviceIdRecibido;
            $estudiante->save();
            Log::info("Login móvil: UID {$estudiante->uid} - Nuevo dispositivo vinculado: {$deviceIdRecibido}");
        }

        $estudiante->tokens()->delete();
        $token = $estudiante->createToken('auth_token_movil')->plainTextToken;
        Log::info("Login móvil exitoso y token generado: {$estudiante->uid}");
        return response()->json([
            'success' => true,
            'token' => $token,
            'estudiante' => $estudiante
        ]);
    }


    /**
     * Endpoint para que la APP consulte si el botón de marcar debe estar activo.
     * (¡MÉTODO MODIFICADO CON LA LÓGICA CORRECTA!)
     */
    public function getEstadoAsistencia()
    {
        $estudiante = Auth::user(); // Es un modelo Estudiante
        
        // 1. VERIFICAR HORARIO USANDO EL SERVICIO
        $estado = $this->horarioService->verificarEstadoAsistencia($estudiante->id, null);
        
        // --- INICIO: ¡MODIFICACIÓN CLAVE! LÓGICA ANTI-DUPLICADOS ---

        // 2. SI EL HORARIO ESTÁ ABIERTO, VERIFICAR DUPLICADOS
        if ($estado['puede_marcar']) {
            $periodo = \App\Models\Periodo::find($estado['periodo_id']);
            if ($periodo) {
                // Usamos el mismo servicio que usa el AsistenciaController
                $yaMarco = $this->horarioService->verificarAsistenciaExistente(
                    $estudiante, 
                    $periodo, 
                    Carbon::today()
                );

                if ($yaMarco) {
                    Log::warning("⚠️ Estado App denegado (Duplicado): {$estudiante->uid}");
                    // Sobreescribimos el estado
                    $estado['puede_marcar'] = false;
                    $estado['mensaje'] = 'ASISTENCIA_YA_REGISTRADA';
                }
            }
        } 
        // 3. SI EL HORARIO ESTÁ CERRADO, VERIFICAR SI FUE PORQUE YA MARCÓ
        // (Esto es para que el botón de la app muestre "Ya Registrado"
        // en lugar de "Fuera de Horario" si el usuario vuelve a abrir la app)
        else if ($estado['mensaje'] == 'FUERA_DE_TOLERANCIA' && $estado['periodo_id']) {
            $periodo = \App\Models\Periodo::find($estado['periodo_id']);
            if ($periodo) {
                $yaMarco = $this->horarioService->verificarAsistenciaExistente(
                    $estudiante, 
                    $periodo, 
                    Carbon::today()
                );

                if ($yaMarco) {
                    $estado['mensaje'] = 'ASISTENCIA_YA_REGISTRADA';
                }
            }
        }
        // --- FIN: MODIFICACIÓN LÓGICA ANTI-DUPLICADOS ---

        // 4. DEVOLVER EL ESTADO FINAL
        return response()->json($estado);
    }


    /**
     * Registra la asistencia desde la APP MÓVIL.
     * (Este método ya tenía el chequeo anti-duplicados, lo cual es bueno)
     */
    public function registrarAsistencia(Request $request, HorarioService $horarioService)
    {
        $estudiante = $request->user();
        
        if (!$estudiante->estado) {
            Log::warning("Asistencia MOVIL denegada (Cuenta Inactiva): UID {$estudiante->uid}");
            return response()->json([
                'success' => false,
                'message' => 'No puedes registrar asistencia, tu cuenta está desactivada.'
            ], 403);
        }

        $data = $request->validate([
            'accion' => 'required|string|in:ENTRADA',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);
        
        // 3. VERIFICAR HORARIO
        $verificacion = $horarioService->verificarEstadoAsistencia($estudiante->id, null);

        if (!$verificacion['puede_marcar']) {
            // (Lógica de chequeo de duplicados si el horario está CERRADO)
             $mensajeApp = 'Error de horario';
             if ($verificacion['mensaje'] == 'FUERA_DE_TOLERANCIA') {
                 $mensajeApp = 'El tiempo de tolerancia ha finalizado.';
                 
                 // (Verificar si fue porque ya marcó)
                 if ($verificacion['periodo_id']) {
                    $periodo = \App\Models\Periodo::find($verificacion['periodo_id']);
                    if ($periodo && $this->horarioService->verificarAsistenciaExistente($estudiante, $periodo, Carbon::today())) {
                        $mensajeApp = 'Ya has registrado tu asistencia para este período hoy.';
                    }
                 }
                 
             } else if ($verificacion['mensaje'] == 'SIN_CLASE') {
                  $mensajeApp = 'No tienes ninguna clase programada ahora.';
             } else if ($verificacion['mensaje'] == 'ASISTENCIA_YA_REGISTRADA') { // <-- Mensaje del semáforo
                  $mensajeApp = 'Ya has registrado tu asistencia para este período hoy.';
             }
            
            Log::warning("Asistencia MOVIL denegada (Semáforo): UID {$estudiante->uid}, Mensaje: {$verificacion['mensaje']}");
            return response()->json([
                'success' => false,
                'message' => $mensajeApp
            ], 403);
        }
        
        // --- VERIFICACIÓN DE DUPLICADOS (Capa de seguridad 2) ---
        $periodoId = $verificacion['periodo_id'];
        $yaMarco = Asistencia::where('uid', $estudiante->uid)
            ->where('periodo_id', $periodoId)
            ->where('accion', 'ENTRADA')
            ->whereDate('fecha_hora', now()->toDateString())
            ->exists();

        if ($yaMarco) {
            Log::warning("Asistencia MOVIL denegada (Duplicado en POST): UID {$estudiante->uid}, Periodo {$periodoId}");
            return response()->json([
                'success' => false,
                'message' => 'Ya has registrado tu asistencia para este período hoy.'
            ], 409); // 409 Conflict
        }
        // --- FIN VERIFICACIÓN DE DUPLICADOS ---

        // 4. VERIFICAR GEOLOCALIZACIÓN
        $targetLat = (float) config('app.target_latitud', -17.33672);
        $targetLng = (float) config('app.target_longitud', -66.1972);
        $targetRadio = (float) config('app.target_radio_metros', 40);
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

        // 5. GUARDAR ASISTENCIA
        $asistencia = Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto,
            'accion' => $data['accion'],
            'modo' => 'MOVIL', 
            'fecha_hora' => now(),
            'curso_id' => $verificacion['curso_id'],
            'periodo_id' => $verificacion['periodo_id'],
            'estado_llegada' => 'a_tiempo'
        ]);
        Log::info("Asistencia MOVIL registrada: UID {$estudiante->uid}, CursoID: {$asistencia->curso_id}, Distancia: {$distancia}m");

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada exitosamente.',
            'data' => $asistencia
        ], 201);
    }

    
    // --- MÉTODOS SIN CAMBIOS (getPerfil, logout, calcularDistancia, getHistorial) ---
    
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
            
            $historial = $query->with('periodo:id,nombre')
                                ->orderBy('fecha_hora', 'desc')
                                ->get();
            
            Log::info("Historial solicitado para UID: {$estudiante->uid} (Periodo: $periodo, Rango: $fechaInicio-$fechaFin), encontrados: {$historial->count()}");
            return response()->json(['success' => true, 'historial' => $historial]);
        } catch (\Exception $e) {
            Log::error("Error al obtener historial para UID: {$request->user()->uid}. Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener el historial: ' . $e->getMessage()], 500);
        }
    }
}