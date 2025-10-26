<?php

namespace App\Imports;

use App\Models\Materia;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class MateriasImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private $failures = [];

    // Opciones válidas (para robustecer la validación)
    private $carreras = ['Sistemas', 'Contabilidad', 'Secretariado', 'Mercadotecnia'];
    private $anos = ['Primer Año', 'Segundo Año', 'Tercer Año'];

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $nombre = strtoupper($row['nombre']);
        $carrera = $row['carrera'];
        $ano_cursado = $row['ano_cursado'] ?? $row['año_cursado']; // Acepta ambas cabeceras

        // Usamos firstOrCreate para evitar duplicados exactos
        // Si ya existe una materia con el mismo nombre, carrera y año, no la creará.
        return Materia::firstOrCreate(
            [
                'nombre' => $nombre,
                'carrera' => $carrera,
                'ano_cursado' => $ano_cursado,
            ]
        );
    }

    /**
     * Define las reglas de validación para cada fila.
     */
    public function rules(): array
    {
        return [
            '*.nombre' => 'required|string|max:255',
            
            '*.carrera' => ['required', Rule::in($this->carreras)],
            
            // Validamos 'ano_cursado' o 'año_cursado'
            '*.ano_cursado' => ['nullable', Rule::in($this->anos)],
            '*.año_cursado' => ['nullable', Rule::in($this->anos)],
        ];
    }

    /**
     * Captura todos los fallos de validación.
     */
    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * Un método público para que el controlador pueda obtener los fallos.
     */
    public function getFailures()
    {
        return $this->failures;
    }
}