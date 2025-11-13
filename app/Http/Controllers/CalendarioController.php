<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiaNoLaborable;
use App\Models\GestionAcademica;

class CalendarioController extends Controller
{
    public function index()
    {
        $gestion = GestionAcademica::activa()->first();
        $dias = DiaNoLaborable::all();
        $eventos = [];

        foreach ($dias as $dia) {
            $eventos[] = [
                'id' => $dia->id, // <--- ¡CRUCIAL! Pasar el ID
                'title' => $dia->descripcion,
                'start' => $dia->fecha->format('Y-m-d'),
                'backgroundColor' => $dia->color, 
                'borderColor' => $dia->color,
                'allDay' => true,
                // Pasamos datos extra para rellenar el modal al editar
                'extendedProps' => [
                    'tipo' => $dia->tipo,
                    'descripcion' => $dia->descripcion
                ]
            ];
        }

        $limites = null;
        if ($gestion) {
            $limites = [
                'inicio' => $gestion->fecha_inicio->format('Y-m-d'),
                'fin' => $gestion->fecha_fin->format('Y-m-d'),
            ];
        }

        return view('admin.calendario.index', compact('eventos', 'limites', 'gestion'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|in:FERIADO,VACACION,EMERGENCIA,TOLERANCIA',
            'descripcion' => 'required|string|max:255',
        ]);

        DiaNoLaborable::create([
            'fecha' => $request->fecha,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'recurrente' => false,
        ]);

        return redirect()->route('admin.calendario.index')
            ->with('success', 'Evento registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $dia = DiaNoLaborable::findOrFail($id);
        
        $request->validate([
            'tipo' => 'required|in:FERIADO,VACACION,EMERGENCIA,TOLERANCIA',
            'descripcion' => 'required|string|max:255',
        ]);

        $dia->update([
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('admin.calendario.index')->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $dia = DiaNoLaborable::findOrFail($id);
        $dia->delete();

        return redirect()->route('admin.calendario.index')->with('success', 'Evento eliminado y día restablecido.');
    }
}