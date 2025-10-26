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
use App\Services\HorarioService; // <-- ¡NUEVO!
use Illuminate\Support\Facades\Auth; // <-- ¡NUEVO!

class MovilController extends Controller
{
    // --- ¡NUEVO! Inyección del servicio ---
    protected $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    /**
     * Maneja el inicio de sesión desde la app móvil.
     * (Este método se queda como lo tenías)
     */
    public function login(Request $request)
    {
        Log::info('Intento de login móvil:', $request->all());

        // 1. VALIDAR DATOS DE ENTRADA (AÑADIMOS device_id)
        $request->validate([
            'correo' => 'required|email',
            'ci' => 'required|string',
            'device_id' => 'required|string|max:255', 
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
        
        if (!$estudiante->estado) { 
            Log::warning("Fallo de login móvil (Cuenta Inactiva): correo {$correo}");
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta se encuentra desactivada. Contacta a administración.'
            ], 403); // Forbidden
        }

        if (empty($estudiante->uid)) {
            Log::error("Fallo de login móvil (Sin UID): Estudiante {$estudiante->id}");
             return response()->json([
                'success' => false,
                'message' => 'Error de cuenta: Falta el identificador (UID). Contacte a administración.'
            ], 403); // Forbidden
        }

        // --- INICIO: LÓGICA ANTIFRAUDE ---
        $deviceIdGuardado = $estudiante->device_id;

        if ($deviceIdGuardado) {
            if ($deviceIdGuardado !== $deviceIdRecibido) {
                Log::warning("Fallo de login móvil (Dispositivo no coincide): UID {$estudiante->uid}, Device Recibido {$deviceIdRecibido}, Guardado {$deviceIdGuardado}");
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta está vinculada a otro dispositivo. Si perdiste o cambiaste tu teléfono, contacta a administración para desvincularlo.'
                ], 403); // Forbidden
            }
            Log::info("Login móvil: UID {$estudiante->uid} - Dispositivo verificado.");

        } else {
            $otroEstudiante = Estudiante::where('device_id', $deviceIdRecibido)->first();
            if ($otroEstudiante) {
                 Log::warning("Fallo de login móvil (Device ID ya en uso): UID {$estudiante->uid} intentó usar Device ID {$deviceIdRecibido} perteneciente a UID {$otroEstudiante->uid}");
                 return response()->json([
                    'success' => false,
                    'message' => 'Este dispositivo ya está vinculado a otra cuenta. Contacta a administración.'
                ], 409); // Conflict
            }

            $estudiante->device_id = $deviceIdRecibido;
            $estudiante->save();
            Log::info("Login móvil: UID {$estudiante->uid} - Nuevo dispositivo vinculado: {$deviceIdRecibido}");
        }
        // --- FIN: LÓGICA ANTIFRAUDE ---

        // 4. GENERAR TOKEN Y RESPONDER
        $estudiante->tokens()->delete();
        $token = $estudiante->createToken('auth_token_movil')->plainTextToken;
        Log::info("Login móvil exitoso y token generado: {$estudiante->uid}");

        return response()->json([
            'success' => true,
            'token' => $token,
            'estudiante' => $estudiante
        ]);
    }


    // --- ¡NUEVO MÉTODO! ---
    /**
     * Endpoint para que la APP consulte si el botón de marcar debe estar activo.
     */
    public function getEstadoAsistencia()
    {
        $estudiante = Auth::user(); // Es un modelo Estudiante (ya que usamos auth:sanctum)
        
        // Llamamos al servicio (aulaId = null porque es la app)
        $estado = $this->horarioService->verificarEstadoAsistencia($estudiante->id, null);
        
        // Devolvemos el JSON { "puede_marcar": bool, "periodo_id": int, "mensaje": "..." }
        // La app leerá 'puede_marcar' para activar/desactivar el botón
        return response()->json($estado);
    }


    // --- ¡MÉTODO MODIFICADO! ---
    /**
     * Registra la asistencia desde la APP MÓVIL.
     * Ya no recibe 'accion', solo valida geolocalización.
     */
    public function registrarAsistencia(Request $request, HorarioService $horarioService)
    {
        $estudiante = $request->user(); // Obtener el estudiante autenticado
        
        // 1. VALIDACIÓN DE ESTADO DE CUENTA (Como ya lo tenías)
        if (!$estudiante->estado) {
            Log::warning("Asistencia MOVIL denegada (Cuenta Inactiva): UID {$estudiante->uid}");
            return response()->json([
                'success' => false,
                'message' => 'No puedes registrar asistencia, tu cuenta está desactivada.'
            ], 403); 
        }

        // 2. VALIDACIÓN DE DATOS DE ENTRADA
        // ¡Se elimina 'SALIDA' de la validación!
        $data = $request->validate([
            'accion' => 'required|string|in:ENTRADA', // <-- ¡REQUERIMIENTO CUMPLIDO!
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        // -----------------------------------------------------------------
        // INICIO: NUEVA LÓGICA DE HORARIOS
        // -----------------------------------------------------------------

        // 3. VERIFICAR HORARIO
        // Se llama al servicio. Se pasa 'null' como $aulaId porque desde
        // la app móvil no sabemos en qué aula física está.
        $verificacion = $horarioService->verificarEstadoAsistencia($estudiante->id, null);

        if (!$verificacion['puede_marcar']) {
            Log::warning("Asistencia MOVIL denegada (Fuera de Horario): UID {$estudiante->uid}, Mensaje: {$verificacion['mensaje']}");
            
            // Personalizamos el mensaje de error para la app
            $mensajeApp = 'Error de horario';
            if ($verificacion['mensaje'] == 'FUERA_DE_TOLERANCIA') {
                $mensajeApp = 'El tiempo de tolerancia ha finalizado.';
            } else if ($verificacion['mensaje'] == 'SIN_CLASE') {
                 $mensajeApp = 'No tienes ninguna clase programada ahora.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $mensajeApp
            ], 403); // 403 Forbidden
        }
        
        // -----------------------------------------------------------------
        // FIN: NUEVA LÓGICA DE HORARIOS
        // -----------------------------------------------------------------

        // 4. VERIFICAR GEOLOCALIZACIÓN (Tu lógica actual)
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
        // ¡Guardamos con los nuevos campos!
        $asistencia = Asistencia::create([
            'uid' => $estudiante->uid,
            'nombre' => $estudiante->nombreCompleto,
            'accion' => $data['accion'],
            'modo' => 'MOVIL', 
            'fecha_hora' => now(),
            
            // --- NUEVOS CAMPOS ---
            'curso_id' => $verificacion['curso_id'],
            'periodo_id' => $verificacion['periodo_id'],
            'estado_llegada' => 'a_tiempo' // Si llegó aquí, está a tiempo
        ]);

        Log::info("Asistencia MOVIL registrada: UID {$estudiante->uid}, CursoID: {$asistencia->curso_id}, Distancia: {$distancia}m");

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada exitosamente.',
            'data' => $asistencia
        ], 201);
    }

    
    // --- MÉTODOS SIN CAMBIOS ---
    
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
        // ... (Tu lógica de historial está bien y no necesita cambios)
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
            
            // --- ¡MEJORA! ---
            // Incluimos el nombre del periodo en la consulta del historial
            $historial = $query->with('periodo:id,nombre') // Carga la relación 'periodo'
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