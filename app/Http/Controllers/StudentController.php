<?php

namespace App\Http\Controllers;

use App\Models\Estudiante; // Asegúrate de importar tu modelo Estudiante
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Para generar el archivo de estudiantes para Arduino
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index()
    {
        $estudiantes = Estudiante::paginate(10); // O Estudiante::where('estado', 1)->paginate(10);
        return view('students.index', compact('estudiantes'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'uid' => 'required|string|unique:students,uid',
        ]);

        Estudiante::create([
            'nombre' => $request->nombre,
            'uid' => $request->uid,
            'estado' => 1, // Por defecto activo
        ]);

        $this->generateStudentsListForArduino(); // Generar archivo actualizado

        return redirect()->route('students.index')->with('status', 'Estudiante registrado exitosamente!');
    }

    public function show(Estudiante $student)
    {
        // Si necesitas una vista para mostrar un estudiante individual
        return view('students.show', compact('student'));
    }

    public function edit(Estudiante $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Estudiante $student)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'uid' => 'required|string|unique:students,uid,' . $student->id, // Ignorar el UID actual para el propio estudiante
            'estado' => 'required|boolean', // Si quieres permitir cambiar el estado desde el formulario de edición
        ]);

        $student->update($request->all());

        $this->generateStudentsListForArduino(); // Generar archivo actualizado

        return redirect()->route('students.index')->with('status', 'Estudiante actualizado exitosamente!');
    }

    public function destroy(Estudiante $student)
    {
        $student->update(['estado' => 0]); // Cambia el estado a inactivo

        $this->generateStudentsListForArduino(); // Generar archivo actualizado

        return redirect()->route('students.index')->with('status', 'Estudiante dado de baja exitosamente!');
    }

    public function restore(Estudiante $student)
    {
        $student->update(['estado' => 1]); // Reactiva al estudiante

        $this->generateStudentsListForArduino(); // Generar archivo actualizado

        return redirect()->route('students.index')->with('status', 'Estudiante reactivado exitosamente!');
    }

    // Método para generar el archivo de estudiantes para Arduino
    private function generateStudentsListForArduino()
    {
        $students = Estudiante::where('estado', 1)->get(); // Solo estudiantes activos
        $content = "UID,NOMBRE\n"; // Encabezado

        foreach ($students as $student) {
            $content .= $student->uid . "," . $student->nombre . "\n";
        }

        $filePath = public_path('lista_estudiantes.txt'); // Guarda en la carpeta public
        File::put($filePath, $content);

        Log::info('Archivo lista_estudiantes.txt generado y actualizado correctamente.');
    }
}