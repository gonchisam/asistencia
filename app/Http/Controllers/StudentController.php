<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // Ensure Log facade is imported

class StudentController extends Controller
{
    /**
     * Muestra un listado paginado de todos los estudiantes (activos e inactivos).
     */
    public function index(Request $request)
    {
    $query = Estudiante::query();
    
    // Aplicar filtro de carrera si existe
    if ($request->has('carrera') && $request->carrera != '') {
        $query->where('carrera', $request->carrera);
    }
    
    // Aplicar filtro de año si existe
    if ($request->has('año') && $request->año != '') {
        $query->where('año', $request->año);
    }
    
    // Aplicar filtro de estado si existe
    if ($request->has('estado') && $request->estado != '') {
        $query->where('estado', $request->estado);
    }
    
    // Ordenar y paginar los resultados
    $estudiantes = $query->orderBy('nombre')->paginate(10)->appends($request->query());
    
    return view('students.index', compact('estudiantes'));
    }

    /**
     * Muestra el formulario para crear un nuevo estudiante.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Guarda un nuevo estudiante en la base de datos.
     */
    public function store(Request $request)
    {
    $request->validate([
        'uid' => 'required|string|unique:students,uid|max:255',
        'nombre' => 'required|string|max:255',
        'primer_apellido' => 'required|string|max:255',
        'segundo_apellido' => 'nullable|string|max:255',
        'ci' => 'required|string|unique:students,ci|max:255|regex:/^[a-zA-Z0-9\-]+$/',
        'fecha_nacimiento' => 'required|date',
        'carrera' => 'required|in:Contabilidad,Secretariado,Mercadotecnia,Sistemas',
        'año' => 'required|in:Primer Año,Segundo Año,Tercer Año',
        'sexo' => 'required|in:MASCULINO,FEMENINO',
        'celular' => 'nullable|string|max:20',
        'correo' => 'required|email|max:255',
    ]);

    Estudiante::create([
        'nombre' => strtoupper($request->nombre),
        'primer_apellido' => strtoupper($request->primer_apellido),
        'segundo_apellido' => $request->segundo_apellido ? strtoupper($request->segundo_apellido) : null,
        'ci' => $request->ci,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'carrera' => $request->carrera,
        'año' => $request->año,
        'sexo' => $request->sexo,
        'celular' => $request->celular,
        'correo' => $request->correo,
        'uid' => $request->uid,
        'estado' => true,
        
    ]);

    $this->generateStudentsListForArduino();

    return redirect()->route('students.index')->with('status', 'Estudiante registrado exitosamente!');
    }

    /**
     * Muestra el formulario de edición para un estudiante específico.
     */
    public function edit(Estudiante $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Actualiza un estudiante en la base de datos.
     */
    public function update(Request $request, Estudiante $student)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'uid' => 'required|string|unique:students,uid,' . $student->id . '|max:255', // Ignore current student's UID
        ]);

        $student->update([
            'nombre' => $request->nombre,
            'uid' => $request->uid,
            // 'estado' => $request->estado, // Uncomment if you want to allow changing state from edit form
        ]);

        $this->generateStudentsListForArduino();

        return redirect()->route('students.index')->with('status', 'Estudiante actualizado exitosamente!');
    }

    /**
     * Baja lógica: desactiva a un estudiante (estado = 0).
     */
    public function destroy(Estudiante $student)
    {
        $student->update(['estado' => 0]);

        $this->generateStudentsListForArduino();

        return redirect()->route('students.index')->with('status', 'Estudiante dado de baja exitosamente!');
    }

    /**
     * Reactiva a un estudiante previamente desactivado (estado = 1).
     */
    public function restore(Estudiante $student)
    {
        $student->update(['estado' => 1]);

        $this->generateStudentsListForArduino();

        return redirect()->route('students.index')->with('status', 'Estudiante reactivado exitosamente!');
    }

    /**
     * Genera un archivo de texto con los estudiantes activos (UID y nombre) para el Arduino.
     * Este archivo se guarda en la carpeta 'public'.
     */
    private function generateStudentsListForArduino()
    {
        try {
            $students = Estudiante::where('estado', 1)->get(); // Only active students
            $content = "UID,NOMBRE\n"; // Header for the file

            foreach ($students as $student) {
                // Ensure no commas in names to avoid breaking CSV format unless handled by Arduino
                $sanitizedNombre = str_replace(',', '', $student->nombre);
                $content .= $student->uid . "," . $sanitizedNombre . "\n";
            }

            $filePath = public_path('lista_estudiantes.txt');
            File::put($filePath, $content);

            Log::info('Archivo lista_estudiantes.txt generado y actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al generar lista_estudiantes.txt: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint API: retorna la lista de estudiantes activos en formato JSON para Arduino.
     */
    public function getStudentsList()
    {
        try {
            // Only select necessary columns for the Arduino
            $students = Estudiante::where('estado', 1)->get(['uid', 'nombre']);
            return response()->json($students);
        } catch (\Exception $e) {
            Log::error("Error al obtener la lista de estudiantes para Arduino: " . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor al obtener la lista de estudiantes'], 500);
        }
    }

    public function checkUid(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:255',
        ]);

        $exists = Estudiante::where('uid', $request->uid)->exists();

        return response()->json(['exists' => $exists]);
    }
}