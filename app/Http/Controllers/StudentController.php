<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use Illuminate\Support\Facades\File;

class StudentController extends Controller
{
    /**
     * Muestra un listado paginado de todos los estudiantes (activos e inactivos).
     */
    public function index()
    {
        $estudiantes = Estudiante::orderBy('nombre')->paginate(10);
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
            'nombre' => 'required|string|max:255',
            'uid' => 'required|string|unique:students,uid|max:255',
        ]);

        Estudiante::create([
            'nombre' => $request->nombre,
            'uid' => $request->uid,
            'estado' => 1, // Activo por defecto
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
            'uid' => 'required|string|unique:students,uid,' . $student->id . '|max:255',
        ]);

        $student->update([
            'nombre' => $request->nombre,
            'uid' => $request->uid,
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
     */
    private function generateStudentsListForArduino()
    {
        $students = Estudiante::where('estado', 1)->get();
        $content = "UID,NOMBRE\n";

        foreach ($students as $student) {
            $content .= $student->uid . "," . $student->nombre . "\n";
        }

        $filePath = public_path('lista_estudiantes.txt');
        File::put($filePath, $content);

        \Log::info('Archivo lista_estudiantes.txt generado correctamente.');
    }

    /**
     * Endpoint API: retorna la lista de estudiantes activos en formato JSON para Arduino.
     */
    public function getStudentsList()
    {
        $students = Estudiante::where('estado', 1)->get(['uid', 'nombre']);
        return response()->json($students);
    }
}
