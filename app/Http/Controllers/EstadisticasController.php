<?php

namespace App\Http\Controllers;

use App\Services\EstadisticasService;
use App\Exports\EstadisticasExport; // <-- AÑADIR ESTE USE
use Maatwebsite\Excel\Facades\Excel; // <-- AÑADIR ESTE USE
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

        // Convertir los datos a arrays para la exportación y la vista
        $asistenciaDiaria = json_decode(json_encode($asistenciaDiaria), true);
        $horasPico = json_decode(json_encode($horasPico), true);
        $asistenciaPorCarrera = json_decode(json_encode($asistenciaPorCarrera), true);

        return view('estadisticas.index', compact('asistenciaDiaria', 'horasPico', 'asistenciaPorCarrera', 'estudiantesEnRiesgo'));
    }

    /**
     * Exporta los datos de estadísticas a un archivo Excel.
     */
    public function exportarExcel()
    {
        $asistenciaDiaria = (array) $this->estadisticasService->getAsistenciaDiariaSemanalMensual();
        $horasPico = (array) $this->estadisticasService->getDistribucionHorasPico();
        $asistenciaPorCarrera = (array) $this->estadisticasService->getAsistenciaPorCarreraYAnio();

        return Excel::download(new EstadisticasExport($asistenciaDiaria, $horasPico, $asistenciaPorCarrera), 'estadisticas.xlsx');
    }
}