<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // <--- ¡¡ESTA LÍNEA ES LA SOLUCIÓN!!

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::all();
        return view('admin.periodos.index', compact('periodos'));
    }

    public function create()
    {
        // (Solución para el error '$periodo')
        $periodo = new Periodo(['tolerancia_ingreso_minutos' => 15]); // Valor por defecto
        
        return view('admin.periodos.create', compact('periodo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'tolerancia_ingreso_minutos' => 'required|integer|min:0',
        ]);

        Periodo::create($request->all());

        return redirect()->route('admin.periodos.index')
                         ->with('success', 'Periodo creado exitosamente.');
    }

    public function edit(Periodo $periodo)
    {
        return view('admin.periodos.edit', compact('periodo'));
    }

    public function update(Request $request, Periodo $periodo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'tolerancia_ingreso_minutos' => 'required|integer|min:0',
        ]);

        $periodo->update($request->all());

        return redirect()->route('admin.periodos.index')
                         ->with('success', 'Periodo actualizado exitosamente.');
    }

    public function destroy(Periodo $periodo)
    {
        try {
            $periodo->delete();
            return redirect()->route('admin.periodos.index')
                             ->with('success', 'Periodo eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors('No se puede eliminar el periodo porque está siendo usado en un horario.');
        }
    }
}