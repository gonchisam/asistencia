<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class EstadisticasSheet implements FromCollection, WithHeadings, WithTitle, WithDrawings, WithEvents, WithCustomStartCell, ShouldAutoSize
{
    private $title;
    private $headings;
    private $data;

    public function __construct(string $title, array $headings, array $data)
    {
        $this->title = $title;
        $this->headings = $headings;
        
        $this->data = collect($data)->map(function ($item) {
            return (array) $item;
        });
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function startCell(): string
    {
        return 'A10'; // Dejamos 9 filas para el encabezado
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo del Instituto');
        $drawing->setPath(public_path('img/logo.jpg')); // Ruta al logo
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ---- Nuevos Títulos del Encabezado ----
                $sheet->mergeCells('B2:G2');
                $sheet->setCellValue('B2', 'INSTITUTO TECNICO NACIONAL DE COMERCIO');
                
                $sheet->mergeCells('B3:G3');
                $sheet->setCellValue('B3', 'FEDERICO ALVAREZ PLATA NOCTURNO');

                $sheet->mergeCells('B5:G5');
                $sheet->setCellValue('B5', 'SISTEMA AUTOMATIZADO PARA EL CONTROL DE ASISTENCIA S.A.C.A.');
                
                $sheet->mergeCells('B7:G7');
                $sheet->setCellValue('B7', 'REPORTE DE ESTADÍSTICAS - ' . strtoupper($this->title()));

                // Fecha de generación
                $sheet->mergeCells('B8:G8');
                $sheet->setCellValue('B8', 'Fecha: ' . Carbon::now()->format('d/m/Y'));

                // ---- Estilos ----
                $sheet->getStyle('B2:B3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('B5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('B7')->getFont()->setBold(true);
                $sheet->getStyle('B2:G8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A10:' . $sheet->getHighestColumn() . '10')->getFont()->setBold(true);
            },
        ];
    }
}