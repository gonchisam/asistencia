<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstadisticasExport implements WithMultipleSheets
{
    use Exportable;

    protected $asistenciaDiaria;
    protected $horasPico;
    protected $asistenciaPorCarrera;

    public function __construct(array $asistenciaDiaria, array $horasPico, array $asistenciaPorCarrera)
    {
        $this->asistenciaDiaria = $asistenciaDiaria;
        $this->horasPico = $horasPico;
        $this->asistenciaPorCarrera = $asistenciaPorCarrera;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new EstadisticasSheet('Asistencia Diaria', ['Fecha', 'Total Asistencias'], $this->asistenciaDiaria),
            new EstadisticasSheet('Horas Pico', ['Hora', 'Total Asistencias'], $this->horasPico),
            new EstadisticasSheet('Asistencia por Carrera', ['Carrera - Año', 'Total Asistencias'], $this->asistenciaPorCarrera),
        ];

        return $sheets;
    }
}