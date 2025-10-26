<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // <--- ¡¡ESTA LÍNEA ES LA SOLUCIÓN!!
use Maatwebsite\Excel\Facades\Excel; // <-- AÑADIR
use App\Imports\MateriasImport;      // <-- AÑADIR
use Maatwebsite\Excel\Validators\ValidationException;

class MateriaController extends Controller
{
    // Opciones para los <select> del formulario
    private function getOpcionesFormulario()
    {
        return [
            'carreras' => ['Sistemas', 'Contabilidad', 'Secretariado', 'Mercadotecnia'],
            'anos' => ['Primer Año', 'Segundo Año', 'Tercer Año']
        ];
    }

    public function index()
    {
        // Obtenemos todas las materias, ordenadas primero por carrera y luego por año.
        $materias = Materia::orderBy('carrera')
                           ->orderBy('ano_cursado') // Puedes ajustar este orden si prefieres (ej. por nombre)
                           ->orderBy('nombre')
                           ->get(); // O el número que prefieras por página

        // Simplemente pasamos la colección paginada a la vista
        return view('admin.materias.index', compact('materias'));
    }

    public function create()
    {
        // (Solución para el error '$materia')
        $materia = new Materia();
        
        return view('admin.materias.create', array_merge(compact('materia'), $this->getOpcionesFormulario()));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'carrera' => 'required|string|max:255',
            'ano_cursado' => 'required|string|max:255',
        ]);

        Materia::create($request->all());

        return redirect()->route('admin.materias.index')
                         ->with('success', 'Materia creada exitosamente.');
    }

    public function edit(Materia $materia)
    {
        return view('admin.materias.edit', array_merge(compact('materia'), $this->getOpcionesFormulario()));
    }

    public function update(Request $request, Materia $materia)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'carrera' => 'required|string|max:255',
            'ano_cursado' => 'required|string|max:255',
        ]);

        $materia->update($request->all());

        return redirect()->route('admin.materias.index')
                         ->with('success', 'Materia actualizada exitosamente.');
    }

    public function destroy(Materia $materia)
    {
        try {
            $materia->delete();
            return redirect()->route('admin.materias.index')
                             ->with('success', 'Materia eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors('No se puede eliminar la materia porque está siendo usada en un curso.');
        }
    }

    public function vistaImportar()
    {
        return view('admin.materias.importar');
    }

    /**
     * Procesa el archivo Excel de materias.
     */
    public function procesarImportar(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|mimes:xlsx,xls,csv'
        ]);

        $import = new MateriasImport();

        try {
            Excel::import($import, $request->file('archivo_excel'));

            $failures = $import->getFailures();

            if (count($failures) > 0) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $errorMessages[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors()) . " (Valor: '{$failure->values()[$failure->attribute()]}')";
                }
                
                return back()->with('import_errors', $errorMessages)
                             ->with('warning', 'Algunas materias no se pudieron importar.');
            }

            return redirect()->route('admin.materias.index')
                             ->with('success', 'Materias importadas exitosamente.');

        } catch (ValidationException $e) {
             $failures = $e->failures();
             // Manejar errores de validación
             return back()->with('import_errors', $failures)
                          ->with('warning', 'Ocurrió un error de validación durante la importación.');
        } catch (\Exception $e) {
            // Captura de errores generales (ej. cabeceras incorrectas)
            return back()->withErrors('Error: ' . $e->getMessage());
        }
    }

    public function destroyAll()
    {
        try {
            // Truncate es más rápido que un delete masivo y resetea el ID.
            // Si prefieres no resetear el ID, usa: Materia::query()->delete();
            Materia::truncate();
            
            return redirect()->route('admin.materias.index')
                             ->with('success', 'Todas las materias han sido eliminadas exitosamente.');
        
        } catch (\Illuminate\Database\QueryException $e) {
            // Esto probablemente ocurra si una materia está siendo usada
            // por un curso y tiene una restricción de llave foránea.
            return back()->withErrors('No se pueden eliminar todas las materias porque al menos una está siendo usada en un curso.');
        }
    }
}