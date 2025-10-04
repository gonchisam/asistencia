<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Estudiante::query();

        // Aplicar filtros existentes
        if ($request->has('carrera') && $request->carrera != '') {
            $query->where('carrera', $request->carrera);
        }

        if ($request->has('año') && $request->año != '') {
            $query->where('año', $request->año);
        }

        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        // Obtener parámetros de ordenación
        $sortColumn = $request->get('sort', 'nombre');
        $sortDirection = $request->get('direction', 'asc');

        // Validar que la columna exista para evitar errores
        if (in_array($sortColumn, ['nombre', 'uid', 'carrera', 'año', 'estado'])) {
            // Manejar la ordenación especial para el campo 'nombre' que incluye apellidos
            if ($sortColumn === 'nombre') {
                $query->orderBy('nombre', $sortDirection)
                      ->orderBy('primer_apellido', $sortDirection)
                      ->orderBy('segundo_apellido', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        } else {
            // Ordenación por defecto si la columna no es válida
            $query->orderBy('nombre');
        }

        $estudiantes = $query->paginate(10)->appends($request->query());

        return view('students.index', compact('estudiantes'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Intento de guardar estudiante. Datos recibidos: ' . json_encode($request->all()));
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
            'celular' => 'nullable|string|max:20|regex:/^[\d\s\+\(\)\-]*$/',
            'correo' => 'required|email|max:255|unique:students,correo',
        ]);

        \Log::info('Validación del estudiante exitosa.');

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
     * Show the form for editing a specific student.
     */
    public function edit(Estudiante $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update a student in the database.
     */
    public function update(Request $request, Estudiante $student)
    {
        $validatedData = $request->validate([
            'uid' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'uid')->ignore($student->id),
            ],
            'nombre' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'ci' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\-]+$/',
                Rule::unique('students', 'ci')->ignore($student->id),
            ],
            'fecha_nacimiento' => 'required|date',
            'carrera' => 'required|in:Contabilidad,Secretariado,Mercadotecnia,Sistemas',
            'año' => 'required|in:Primer Año,Segundo Año,Tercer Año',
            'sexo' => 'required|in:MASCULINO,FEMENINO',
            'celular' => 'nullable|string|max:20|regex:/^[\d\s\+\(\)\-]*$/',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'correo')->ignore($student->id),
            ],
            'last_action' => 'nullable|string|max:255',
            'estado' => 'nullable',
        ]);

        $oldUid = $student->uid;
        
        $estado = $request->has('estado') && $request->estado == '1' ? 1 : 0;
        
        $student->update([
            'nombre' => strtoupper($validatedData['nombre']),
            'primer_apellido' => strtoupper($validatedData['primer_apellido']),
            'segundo_apellido' => $validatedData['segundo_apellido'] ? strtoupper($validatedData['segundo_apellido']) : null,
            'ci' => $validatedData['ci'],
            'fecha_nacimiento' => $validatedData['fecha_nacimiento'],
            'carrera' => $validatedData['carrera'],
            'año' => $validatedData['año'],
            'sexo' => $validatedData['sexo'],
            'celular' => $validatedData['celular'],
            'correo' => $validatedData['correo'],
            'uid' => strtoupper($validatedData['uid']),
            'estado' => $estado,
            'last_action' => $validatedData['last_action'],
        ]);

        if ($request->uid !== $oldUid) {
            try {
                DB::table('asistencias')
                    ->where('uid', $oldUid)
                    ->update(['uid' => strtoupper($request->uid)]);
            } catch (\Exception $e) {
                Log::error('Error al actualizar asistencias: ' . $e->getMessage());
                return redirect()->back()->with('error', 'El estudiante fue actualizado, pero no se pudo actualizar su asistencia. Contacte a un administrador.');
            }
        }

        $this->generateStudentsListForArduino();

        return redirect()->route('students.index')->with('status', 'Estudiante actualizado exitosamente!');
    }

    /**
     * Logical delete: deactivates a student (estado = 0).
     */
    public function destroy(Estudiante $student)
    {
        $student->update(['estado' => 0]);

        $this->generateStudentsListForArduino();

        return redirect()->route('students.index')->with('status', 'Estudiante dado de baja exitosamente!');
    }

    /**
     * Reactivates a previously deactivated student (estado = 1).
     */
    public function restore(Estudiante $student)
    {
        $student->update(['estado' => 1]);

        $this->generateStudentsListForArduino();

        return redirect()->route('students.index')->with('status', 'Estudiante reactivado exitosamente!');
    }

    /**
     * Generates a text file with active students (UID and name) for the Arduino.
     */
    private function generateStudentsListForArduino()
    {
        try {
            $students = Estudiante::where('estado', 1)->get();
            $content = "UID,NOMBRE\n";

            foreach ($students as $student) {
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
     * API Endpoint: returns the list of active students in JSON format for Arduino.
     */
    public function getStudentsList()
    {
        try {
            $students = Estudiante::where('estado', 1)->get(['uid', 'nombre']);
            return response()->json($students);
        } catch (\Exception $e) {
            Log::error("Error al obtener la lista de estudiantes para Arduino: " . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor al obtener la lista de estudiantes'], 500);
        }
    }

    /**
     * Checks if a UID already exists in the database.
     */
    public function checkUid(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:255',
        ]);

        $exists = Estudiante::where('uid', $request->uid)->exists();

        return response()->json(['exists' => $exists]);
    }
    
    /**
     * NEW METHOD: Receives the UID from the RFID reader and stores it temporarily in the cache.
     */
    public function receiveUid(Request $request)
    {
        Log::info('Solicitud recibida en receiveUid con datos: ' . json_encode($request->all()));

        $request->validate([
            'uid' => 'required|string|max:255',
        ]);

        $uid = $request->input('uid');

        $exists = Estudiante::where('uid', $uid)->exists();

        // Almacena el UID en el caché SIN IMPORTAR si existe o no.
        Cache::put('temp_uid_for_form', $uid, 30);
        
        if ($exists) {
            return response()->json([
                'status' => 'registered',
                'message' => 'UID already registered.'
            ]);
        } else {
            return response()->json([
                'status' => 'new',
                'message' => 'New UID received and ready to register.'
            ]);
        }
    }

    /**
     * NEW METHOD: Gets the temporary UID from the cache to fill the form.
     */
    public function getTempUid()
    {
        $uid = Cache::get('temp_uid_for_form');
        return response()->json(['uid' => $uid]);
    }
}