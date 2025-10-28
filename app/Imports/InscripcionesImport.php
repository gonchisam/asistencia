<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Para leer la cabecera
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Curso;

class InscripcionesImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // 1. Buscar Estudiante (Igual que antes)
            $estudiante = Estudiante::where('ci', $row['ci_estudiante'])->first();
            
            // Si el estudiante no existe, saltamos esta fila
            if (!$estudiante) {
                continue; 
            }

            // 2. Buscar TODAS las materias para ese año y carrera
            // ¡ESTE ES EL CAMBIO DE LÓGICA!
            $materiaIds = Materia::where('carrera', $row['carrera'])
                                 ->where('ano_cursado', $row['ano_cursado'])
                                 ->pluck('id'); // Obtenemos solo los IDs: [10, 11, 12, 13]

            // Si no se encontraron materias, saltamos esta fila
            if ($materiaIds->isEmpty()) {
                continue;
            }

            // 3. Buscar TODOS los cursos que coincidan
            // ¡ESTE ES EL SEGUNDO CAMBIO DE LÓGICA!
            $cursos = Curso::whereIn('materia_id', $materiaIds) // Busca en la lista de IDs
                           ->where('paralelo', $row['paralelo'])
                           ->where('gestion', $row['gestion'])
                           ->get(); // Obtenemos la colección de Cursos

            // Si no se encontraron cursos (ej: el admin no creó el paralelo "B" para esa gestión)
            if ($cursos->isEmpty()) {
                continue;
            }

            // 4. Inscribir al estudiante en TODOS esos cursos
            // syncWithoutDetaching es perfecto para esto.
            // Tomará los IDs de todos los cursos encontrados [201, 202, 203, 204]
            // y los asociará con el estudiante_id (99).
            $estudiante->cursos()->syncWithoutDetaching($cursos->pluck('id'));
        }
    }
}