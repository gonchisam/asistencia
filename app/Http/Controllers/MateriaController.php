<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // <--- ¡¡ESTA LÍNEA ES LA SOLUCIÓN!!

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
        $materias = Materia::paginate(10);
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
}