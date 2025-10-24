<?php

namespace App\Http\Controllers;

use App\Services\EstadisticasService;
use Illuminate\Http\Request; // Asegúrate que Request esté importado

class EstadisticasController extends Controller
{
    protected $estadisticasService;

    public function __construct(EstadisticasService $estadisticasService)
    {
        $this->estadisticasService = $estadisticasService;
    }

    // app/Http/Controllers/EstadisticasController.php

public function index(Request $request) // Asegúrate de que 'Request $request' esté aquí
{
    // Obtener fechas del request
    $fechaInicio = $request->input('fecha_inicio');
    $fechaFin = $request->input('fecha_fin');

    // === INICIO DE LA VERIFICACIÓN ===
    //
    // Asegúrate de que ($fechaInicio, $fechaFin) se pasen a LAS TRES llamadas:

    $asistenciaDiaria = $this->estadisticasService->getAsistenciaDiariaSemanalMensual($fechaInicio, $fechaFin);
    
    $horasPico = $this->estadisticasService->getDistribucionHorasPico($fechaInicio, $fechaFin);
    
    $asistenciaPorCarrera = $this->estadisticasService->getAsistenciaPorCarreraYAnio($fechaInicio, $fechaFin);
    
    // (Esta también, aunque es para el Punto 4)
    $estudiantesEnRiesgo = $this->estadisticasService->getEstudiantesEnRiesgo($fechaInicio, $fechaFin);

    // === FIN DE LA VERIFICACIÓN ===


    // Convertir los datos a arrays para la exportación y la vista
    $asistenciaDiaria = json_decode(json_encode($asistenciaDiaria), true);
    $horasPico = json_decode(json_encode($horasPico), true);
    $asistenciaPorCarrera = json_decode(json_encode($asistenciaPorCarrera), true);

    // Devolver las fechas a la vista para que los inputs las recuerden
    return view('estadisticas.index', compact(
        'asistenciaDiaria', 
        'horasPico', 
        'asistenciaPorCarrera', 
        'estudiantesEnRiesgo',
        'fechaInicio',
        'fechaFin'
    ));
}
}