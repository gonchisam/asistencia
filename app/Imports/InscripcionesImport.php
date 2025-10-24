<?php

// app/Imports/InscripcionesImport.php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Para leer la cabecera (ej: 'ci_estudiante')
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Curso;

class InscripcionesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // 1. Buscar Estudiante
            $estudiante = Estudiante::where('ci', $row['ci_estudiante'])->first();
            if (!$estudiante) continue; // Si no existe, saltar esta fila

            // 2. Buscar Materia
            $materia = Materia::where('nombre', $row['nombre_materia'])
                              ->where('carrera', $row['carrera'])
                              ->where('ano_cursado', $row['ano_cursado'])
                              ->first();
            if (!$materia) continue;

            // 3. Buscar Curso
            $curso = Curso::where('materia_id', $materia->id)
                          ->where('paralelo', $row['paralelo'])
                          ->where('gestion', $row['gestion'])
                          ->first();
            if (!$curso) continue; // El admin debe crear el curso primero

            // 4. Inscribir (Evitando duplicados)
            $curso->estudiantes()->syncWithoutDetaching($estudiante->id);
        }
    }
}