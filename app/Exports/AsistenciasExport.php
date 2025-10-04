<?php

namespace App\Exports;

use App\Models\Asistencia;
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
        $query = Asistencia::query()->with('estudiante');

        // Filtros de la tabla Asistencia
        if (isset($this->filters['fecha_inicio']) && $this->filters['fecha_inicio']) {
            $query->where('created_at', '>=', $this->filters['fecha_inicio'] . ' 00:00:00');
        }
        if (isset($this->filters['fecha_fin']) && $this->filters['fecha_fin']) {
            $query->where('created_at', '<=', $this->filters['fecha_fin'] . ' 23:59:59');
        }
        if (isset($this->filters['accion']) && $this->filters['accion']) {
            $query->where('accion', $this->filters['accion']);
        }
        if (isset($this->filters['modo']) && $this->filters['modo']) {
            $query->where('modo', $this->filters['modo']);
        }

        // Aplica los filtros a la relación 'estudiante' usando whereHas
        $query->whereHas('estudiante', function ($q) {
            if (isset($this->filters['estudiante_id']) && $this->filters['estudiante_id']) {
                $q->where('id', $this->filters['estudiante_id']);
            }
            if (isset($this->filters['carrera']) && $this->filters['carrera']) {
                $q->where('carrera', $this->filters['carrera']);
            }
            // ¡CORRECCIÓN! Cambiar 'anio_estudio' a 'año'
            if (isset($this->filters['anio_estudio']) && $this->filters['anio_estudio']) {
                $q->where('año', $this->filters['anio_estudio']);
            }
            if (isset($this->filters['ci']) && $this->filters['ci']) {
                $q->where('ci', 'like', '%' . $this->filters['ci'] . '%');
            }
        });

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Nombre del Estudiante',
            'CI',
            'Carrera',
            'Año de Estudio',
            'UID',
            'Acción',
            'Modo',
            'Fecha y Hora',
        ];
    }

    public function map($asistencia): array
    {
        return [
            $asistencia->estudiante->nombre,
            $asistencia->estudiante->ci,
            $asistencia->estudiante->carrera,
            $asistencia->estudiante->año, // ¡CORRECCIÓN! Usar 'año' en el mapeo también
            $asistencia->estudiante->uid,
            $asistencia->accion,
            $asistencia->modo,
            $asistencia->created_at->format('d/m/Y H:i:s'),
        ];
    }
}