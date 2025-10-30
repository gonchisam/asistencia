<?php

namespace App\Exports;

use App\Models\Asistencia;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class AsistenciasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        $query = Asistencia::query()->with('estudiante', 'curso.materia'); 

        // Filtros directos en 'asistencias'
        if (!empty($this->filters['fecha_desde'])) {
            try {
                $query->where('fecha_hora', '>=', Carbon::parse($this->filters['fecha_desde'])->startOfDay());
            } catch (\Exception $e) {}
        }
        if (!empty($this->filters['fecha_fin'])) {
            try {
                $query->where('fecha_hora', '<=', Carbon::parse($this->filters['fecha_fin'])->endOfDay());
            } catch (\Exception $e) {}
        }
        if (!empty($this->filters['modo'])) {
            $query->where('modo', $this->filters['modo']);
        }
        if (!empty($this->filters['estado_llegada'])) {
            $query->where('estado_llegada', $this->filters['estado_llegada']);
        }

        // Filtros en 'estudiante'
        $query->whereHas('estudiante', function ($q) {
            if (!empty($this->filters['carrera'])) {
                $q->where('carrera', $this->filters['carrera']);
            }
            if (!empty($this->filters['año'])) { // Usar 'año'
                $q->where('año', $this->filters['año']);
            }
            if (!empty($this->filters['ci'])) {
                $q->where('ci', 'like', '%' . $this->filters['ci'] . '%');
            }
        });

        // Filtros en 'curso'
        $query->whereHas('curso', function ($q) {
             if (!empty($this->filters['materia_id'])) {
                 $q->where('materia_id', $this->filters['materia_id']);
             }
             if (!empty($this->filters['paralelo'])) {
                 $q->where('paralelo', $this->filters['paralelo']);
             }
        });

        return $query->latest('fecha_hora');
    }

    /**
    * Define los encabezados de las columnas en el Excel.
    */
    public function headings(): array
    {
        // Coincidir con las 9 columnas de la vista PDF
        return [
            'Fecha y Hora',
            'CI',
            'Nombre Completo',
            'Carrera',
            'Año Cursado',
            'Paralelo', // <-- NUEVA COLUMNA
            'Materia',
            'Modo',
            'Estado Llegada',
        ];
    }

    /**
    * Mapea los datos de cada fila de la consulta a las columnas del Excel.
    * @param mixed $asistencia Un modelo Asistencia con relaciones cargadas.
    * @return array
    */
    public function map($asistencia): array
    {
        // Devolver los datos en el orden de los encabezados
        return [
            $asistencia->fecha_hora->format('d/m/Y H:i:s'),
            $asistencia->estudiante->ci ?? 'N/A',
            $asistencia->nombre,
            $asistencia->estudiante->carrera ?? 'N/A',
            $asistencia->estudiante->año ?? 'N/A',
            $asistencia->curso->paralelo ?? 'N/A', // <-- NUEVA CELDA
            $asistencia->curso->materia->nombre ?? 'N/A',
            $asistencia->modo,
            $asistencia->estado_llegada ? str_replace('_', ' ', $asistencia->estado_llegada) : 'N/A',
        ];
    }
}