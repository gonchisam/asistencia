<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    /**
     * Muestra una lista de las aulas.
     */
    public function index()
    {
        // Obtenemos todas las aulas, ordenadas por ubicación y luego por nombre.
        $aulas = Aula::orderBy('ubicacion') // Ordena primero por ubicación
                      ->orderBy('nombre')     // Luego por nombre dentro de cada ubicación
                      ->get();     // O el número que prefieras

        return view('admin.aulas.index', compact('aulas'));
    }

    /**
     * Muestra el formulario para crear un aula nueva.
     */
    public function create()
    {
        $aula = new Aula(); // Para el formulario reutilizable
        return view('admin.aulas.create', compact('aula'));
    }

    /**
     * Guarda el aula nueva en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50|unique:aulas',
            'ubicacion' => 'nullable|string|max:255',
        ]);

        Aula::create($request->all());

        return redirect()->route('admin.aulas.index')
                         ->with('success', 'Aula creada exitosamente.');
    }

    /**
     * Muestra el formulario para editar un aula.
     */
    public function edit(Aula $aula)
    {
        return view('admin.aulas.edit', compact('aula'));
    }

    /**
     * Actualiza el aula en la base de datos.
     */
    public function update(Request $request, Aula $aula)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50|unique:aulas,codigo,' . $aula->id,
            'ubicacion' => 'nullable|string|max:255',
        ]);

        $aula->update($request->all());

        return redirect()->route('admin.aulas.index')
                         ->with('success', 'Aula actualizada exitosamente.');
    }

    /**
     * Elimina el aula de la base de datos.
     */
    public function destroy(Aula $aula)
    {
        $aula->delete();
        return redirect()->route('admin.aulas.index')
                         ->with('success', 'Aula eliminada exitosamente.');
    }
}