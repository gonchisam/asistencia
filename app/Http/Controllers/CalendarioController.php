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

        // Preparamos los eventos para el calendario (FullCalendar)
        foreach ($dias as $dia) {
            $eventos[] = [
                'title' => $dia->descripcion,
                'start' => $dia->fecha->format('Y-m-d'),
                'backgroundColor' => $dia->color, 
                'borderColor' => $dia->color,
                'allDay' => true,
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
}