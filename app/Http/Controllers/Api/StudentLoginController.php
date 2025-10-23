<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Hash; // Aunque no lo usamos para CI, es buena práctica tenerlo
use Illuminate\Validation\ValidationException;

class StudentLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'ci' => 'required|string',
        ]);

        // 1. Buscar al estudiante por su correo
        $student = Estudiante::where('correo', $request->correo)->first();

        // 2. Verificar si el estudiante existe y si el CI (contraseña) coincide
        //    IMPORTANTE: Esto asume que el CI se guarda como texto plano.
        if (! $student || $student->ci !== $request->ci) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        
        // 3. Si todo es correcto, crear un token de API para ESE estudiante
        $token = $student->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'student_name' => $student->nombreCompleto // Opcional: devolver el nombre
        ]);
    }

    public function logout(Request $request)
    {
        // El $request->user() aquí será el Estudiante autenticado
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }
}