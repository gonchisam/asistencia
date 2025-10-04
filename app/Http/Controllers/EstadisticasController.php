<?php

namespace App\Http\Controllers;

use App\Services\EstadisticasService;
use Illuminate\Http\Request;

class EstadisticasController extends Controller
{
    protected $estadisticasService;

    public function __construct(EstadisticasService $estadisticasService)
    {
        $this->estadisticasService = $estadisticasService;
    }

    public function index()
    {
        $asistenciaDiaria = $this->estadisticasService->getAsistenciaDiariaSemanalMensual();
        $horasPico = $this->estadisticasService->getDistribucionHorasPico();
        $asistenciaPorCarrera = $this->estadisticasService->getAsistenciaPorCarreraYAnio();
        $estudiantesEnRiesgo = $this->estadisticasService->getEstudiantesEnRiesgo();

        return view('estadisticas.index', compact('asistenciaDiaria', 'horasPico', 'asistenciaPorCarrera', 'estudiantesEnRiesgo'));
    }
}