<?php

namespace App\Http\Controllers;

use App\Services\EstadisticasService;
use App\Models\GestionAcademica; // <--- IMPORTANTE: Importar el modelo
use Illuminate\Http\Request;

class EstadisticasController extends Controller
{
    protected $estadisticasService;

    public function __construct(EstadisticasService $estadisticasService)
    {
        $this->estadisticasService = $estadisticasService;
    }

    public function index(Request $request)
    {
        // 1. Obtener todas las gestiones para el filtro (Select)
        $gestiones = GestionAcademica::orderBy('fecha_inicio', 'desc')->get();

        // 2. Lógica de Fechas Inteligente
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $gestionId = $request->input('gestion_id');

        // Si el usuario seleccionó una gestión específica, sobrescribimos las fechas
        if ($gestionId) {
            $gestionSeleccionada = $gestiones->find($gestionId);
            if ($gestionSeleccionada) {
                $fechaInicio = $gestionSeleccionada->fecha_inicio->format('Y-m-d');
                $fechaFin = $gestionSeleccionada->fecha_fin->format('Y-m-d');
            }
        } 
        // Si no hay fechas ni gestión seleccionada, usar la Gestión ACTIVA por defecto
        elseif (!$fechaInicio && !$fechaFin) {
            $gestionActiva = $gestiones->where('actual', true)->first();
            if ($gestionActiva) {
                $fechaInicio = $gestionActiva->fecha_inicio->format('Y-m-d');
                $fechaFin = $gestionActiva->fecha_fin->format('Y-m-d');
                $gestionId = $gestionActiva->id; // Para que el select aparezca marcado
            }
        }

        // 3. Generar Estadísticas con las fechas finales
        $asistenciaDiaria = $this->estadisticasService->getAsistenciaDiariaSemanalMensual($fechaInicio, $fechaFin);
        $horasPico = $this->estadisticasService->getDistribucionHorasPico($fechaInicio, $fechaFin);
        $asistenciaPorCarrera = $this->estadisticasService->getAsistenciaPorCarreraYAnio($fechaInicio, $fechaFin);
        $estudiantesEnRiesgo = $this->estadisticasService->getEstudiantesEnRiesgo($fechaInicio, $fechaFin);

        // 4. Retornar a la vista con todo
        return view('estadisticas.index', compact(
            'asistenciaDiaria', 
            'horasPico', 
            'asistenciaPorCarrera', 
            'estudiantesEnRiesgo',
            'fechaInicio',
            'fechaFin',
            'gestiones', // <--- Enviamos la lista
            'gestionId'  // <--- Enviamos la selección actual
        ));
    }
}