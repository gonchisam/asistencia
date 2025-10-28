<?php
// app/Http/Controllers/HorariosPdfController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class HorariosPdfController extends Controller
{
    /**
     * Muestra el formulario para filtrar los horarios
     */
    public function formulario()
    {
        // Obtener carreras únicas
        $carreras = DB::table('materias')
            ->select('carrera')
            ->distinct()
            ->orderBy('carrera')
            ->pluck('carrera');

        // Años de estudio
        $anos = ['Primer Año', 'Segundo Año', 'Tercer Año'];

        // Obtener paralelos únicos de cursos
        $paralelos = Curso::select('paralelo')
            ->distinct()
            ->orderBy('paralelo')
            ->pluck('paralelo');

        return view('admin.horarios.formulario', compact('carreras', 'anos', 'paralelos'));
    }

    /**
     * Genera el PDF del horario según los filtros
     */
    public function generarPdf(Request $request)
    {
        $request->validate([
            'carrera' => 'required|string',
            'ano_cursado' => 'required|string',
            'paralelo' => 'required|string',
            'gestion' => 'required|string',
        ]);

        // Obtener los cursos que coincidan con los filtros
        $cursos = Curso::with(['materia', 'horarios.periodo', 'horarios.aula', 'docente'])
            ->whereHas('materia', function ($query) use ($request) {
                $query->where('carrera', $request->carrera)
                      ->where('ano_cursado', $request->ano_cursado);
            })
            ->where('paralelo', $request->paralelo)
            ->where('gestion', $request->gestion)
            ->get();

        if ($cursos->isEmpty()) {
            return back()->with('error', 'No se encontraron cursos con los criterios especificados.');
        }

        // Organizar horarios por día y periodo
        $horarioPorDiaPeriodo = $this->organizarHorarios($cursos);

        // Obtener todos los periodos para las columnas
        $periodos = DB::table('periodos')->orderBy('hora_inicio')->get();

        // Días de la semana
        $diasSemana = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIÉRCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES'
        ];

        // Datos para el PDF
        $datos = [
            'carrera' => $request->carrera,
            'ano_cursado' => $request->ano_cursado,
            'paralelo' => $request->paralelo,
            'gestion' => $request->gestion,
            'horarioPorDiaPeriodo' => $horarioPorDiaPeriodo,
            'periodos' => $periodos,
            'diasSemana' => $diasSemana,
        ];

        // Generar PDF en orientación horizontal
        $pdf = Pdf::loadView('admin.horarios.pdf', $datos)
                  ->setPaper('a4', 'landscape');

        return $pdf->stream('horario-' . strtolower($request->carrera) . '-' . strtolower(str_replace(' ', '-', $request->ano_cursado)) . '-' . strtolower($request->paralelo) . '.pdf');
    }

    /**
     * Organiza los horarios en una matriz [dia][periodo_id] = datos
     */
    private function organizarHorarios($cursos)
    {
        $horarioPorDiaPeriodo = [];

        foreach ($cursos as $curso) {
            foreach ($curso->horarios as $horario) {
                $dia = $horario->dia_semana;
                $periodoId = $horario->periodo_id;

                $horarioPorDiaPeriodo[$dia][$periodoId] = [
                    'materia' => $curso->materia->nombre,
                    'aula' => $horario->aula->nombre,
                    'docente' => $curso->docente ? $curso->docente->name : 'Sin asignar',
                ];
            }
        }

        return $horarioPorDiaPeriodo;
    }
}