<?php

namespace App\Http\Controllers;

// Modelos
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Aula;
use App\Models\Estudiante;
use App\Models\CursoHorario;

// Imports de Excel
use App\Imports\InscripcionesImport;
use Maatwebsite\Excel\Facades\Excel;

// Otros
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // <--- ¡¡ESTA LÍNEA ES LA SOLUCIÓN!!

class CursoController extends Controller
{
    /**
     * Muestra una lista de los cursos.
     */
    public function index()
    {
        // Usamos 'join' para poder ordenar por campos de la tabla 'materias'
        // Seleccionamos todos los campos de 'cursos' y los necesarios de 'materias'
        // Es importante usar 'cursos.*' para evitar conflictos de 'id'
        $cursos = Curso::join('materias', 'cursos.materia_id', '=', 'materias.id')
                       ->select('cursos.*', 'materias.nombre as materia_nombre', 'materias.carrera', 'materias.ano_cursado')
                       ->orderBy('materias.carrera')        // Orden 1: Carrera
                       ->orderBy('materias.ano_cursado')    // Orden 2: Año
                       ->orderBy('cursos.paralelo')         // Orden 3: Paralelo
                       ->orderBy('materias.nombre')         // Orden 4: Nombre de materia (opcional)
                       ->paginate(15);                   // O el número que prefieras

        // La vista recibirá la colección paginada ya ordenada
        return view('admin.cursos.index', compact('cursos'));
    }

    /**
     * Muestra el formulario para crear un curso nuevo.
     */
    public function create()
    {
        $materias = Materia::orderBy('nombre')->get();
        return view('admin.cursos.create', compact('materias'));
    }

    /**
     * Guarda el curso nuevo en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'paralelo' => 'required|string|max:50',
            'gestion' => 'required|string|max:50',
        ]);
        
        $curso = Curso::create($request->all());

        return redirect()->route('admin.cursos.show', $curso)
                         ->with('success', 'Curso creado. Ahora puedes añadir horarios y estudiantes.');
    }

    /**
     * Muestra la página de gestión principal para un curso.
     */
    public function show(Curso $curso)
    {
        $curso->load('materia', 'horarios.periodo', 'horarios.aula', 'estudiantes');

        // Datos para los formularios <select>
        $periodos = Periodo::all();
        $aulas = Aula::orderBy('nombre')->get();
        $diasSemana = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
        ];
        
        return view('admin.cursos.show', compact('curso', 'periodos', 'aulas', 'diasSemana'));
    }

    /**
     * Muestra el formulario para editar un curso.
     */
    public function edit(Curso $curso)
    {
        $materias = Materia::orderBy('nombre')->get();
        return view('admin.cursos.edit', compact('curso', 'materias'));
    }

    /**
     * Actualiza el curso en la base de datos.
     */
    public function update(Request $request, Curso $curso)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'paralelo' => 'required|string|max:50',
            'gestion' => 'required|string|max:50',
        ]);

        $curso->update($request->all());
        
        return redirect()->route('admin.cursos.show', $curso)
                         ->with('success', 'Curso actualizado.');
    }

    /**
     * Elimina el curso.
     */
    public function destroy(Curso $curso)
    {
        $curso->delete();
        return redirect()->route('admin.cursos.index')
                         ->with('success', 'Curso eliminado exitosamente.');
    }

    // --- MÉTODOS PERSONALIZADOS ---

    public function storeHorario(Request $request, Curso $curso)
    {
        $request->validate([
            'dia_semana' => 'required|integer|between:1,7',
            'periodo_id' => 'required|exists:periodos,id',
            'aula_id' => 'required|exists:aulas,id',
        ]);
        
        $duplicado = $curso->horarios()
            ->where('dia_semana', $request->dia_semana)
            ->where('periodo_id', $request->periodo_id)
            ->where('aula_id', $request->aula_id)
            ->exists();

        if ($duplicado) {
            return back()->withErrors('Este horario ya existe para este curso.');
        }
        
        $curso->horarios()->create($request->all());
        return back()->with('success', 'Horario añadido.');
    }

    public function destroyHorario(CursoHorario $cursoHorario)
    {
        $cursoHorario->delete();
        return back()->with('success', 'Horario eliminado.');
    }

    public function storeEstudiante(Request $request, Curso $curso)
    {
        $request->validate(['ci' => 'required|string']);

        $estudiante = Estudiante::where('ci', $request->ci)->first();
        
        if (!$estudiante) {
            return back()->withErrors(['ci' => 'No se encontró ningún estudiante con ese CI.']);
        }

        $curso->estudiantes()->syncWithoutDetaching($estudiante->id);
        
        return back()->with('success', 'Estudiante inscrito.');
    }

    public function destroyEstudiante(Curso $curso, Estudiante $estudiante)
    {
        $curso->estudiantes()->detach($estudiante->id);
        return back()->with('success', 'Estudiante eliminado del curso.');
    }

    // --- MÉTODOS DE IMPORTACIÓN ---

    public function vistaImportar()
    {
        return view('admin.inscripciones.importar');
    }

    public function procesarImportacion(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|mimes:xls,xlsx'
        ]);

        try {
            Excel::import(new InscripcionesImport, $request->file('archivo_excel'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             return back()->withErrors($failures);
        } catch (\Exception $e) {
            return back()->withErrors(['archivo_excel' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
        
        return redirect()->route('admin.cursos.index')
                         ->with('success', 'Inscripciones importadas correctamente.');
    }
}