<?php

namespace App\Exports;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AsistenciasExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Database\Query\Builder
    */
    public function query()
    {
        $query = Asistencia::query();

        if (isset($this->filters['fecha_inicio']) && $this->filters['fecha_inicio']) {
            $query->where('fecha_hora', '>=', $this->filters['fecha_inicio'] . ' 00:00:00');
        }
        if (isset($this->filters['fecha_fin']) && $this->filters['fecha_fin']) {
            $query->where('fecha_hora', '<=', $this->filters['fecha_fin'] . ' 23:59:59');
        }
        if (isset($this->filters['estudiante_id']) && $this->filters['estudiante_id']) {
            $estudiante = Estudiante::find($this->filters['estudiante_id']);
            if ($estudiante) {
                $query->where('uid', $estudiante->uid);
            }
        }
        if (isset($this->filters['accion']) && $this->filters['accion']) {
            $query->where('accion', $this->filters['accion']);
        }
        if (isset($this->filters['modo']) && $this->filters['modo']) {
            $query->where('modo', $this->filters['modo']);
        }

        return $query->orderBy('fecha_hora', 'desc');
    }

    /**
     * Define los encabezados de las columnas en el archivo Excel.
     */
    public function headings(): array
    {
        return [
            'UID',
            'Nombre',
            'Acción',
            'Modo',
            'Fecha y Hora',
        ];
    }

    /**
     * Mapea cada fila de datos a un array para la exportación.
     */
    public function map($asistencia): array
    {
        return [
            $asistencia->uid,
            $asistencia->nombre,
            $asistencia->accion,
            $asistencia->modo,
            $asistencia->fecha_hora->format('d/m/Y H:i:s'),
        ];
    }
}