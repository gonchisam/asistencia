<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estudiante;
use Illuminate\Validation\ValidationException;

class AuthMovilController extends Controller
{
    /**
     * Maneja el intento de login del estudiante desde la app móvil.
     */
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'ci' => 'required|string',
        ]);

        // 1. Buscar al estudiante por su correo
        $estudiante = Estudiante::where('correo', $request->correo)->first();

        // 2. Verificar si el estudiante existe y si el CI (contraseña) coincide
        if (! $estudiante || $estudiante->ci !== $request->ci) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        
        // 3. Crear token de API para el estudiante
        $token = $estudiante->createToken('token-app-movil')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'estudiante' => $estudiante // Devolvemos los datos del estudiante
        ]);
    }

    /**
     * Obtiene el perfil del estudiante autenticado.
     */
    public function perfil(Request $request)
    {
        // Gracias al guard, $request->user() ES el modelo Estudiante
        return response()->json([
            'success' => true,
            'estudiante' => $request->user()
        ]);
    }

    /**
     * Cierra la sesión del estudiante eliminando sus tokens.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }
}