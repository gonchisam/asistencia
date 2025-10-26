<?php

namespace App\Imports;

use App\Models\Estudiante;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel; // Cambiamos de ToCollection a ToModel
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation; // Para validar datos
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // Para saltar filas con error
use Maatwebsite\Excel\Validators\Failure; // Para capturar los fallos
use Illuminate\Validation\Rule;

class EstudiantesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    // Almacenará los errores de validación
    private $failures = [];

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $userId = Auth::id() ?? 1; // Obtener el ID del admin logueado (o un fallback)

        return new Estudiante([
            'ci' => $row['ci'],
            'nombre' => strtoupper($row['nombre']),
            'primer_apellido' => strtoupper($row['primer_apellido']),
            'segundo_apellido' => isset($row['segundo_apellido']) ? strtoupper($row['segundo_apellido']) : null,
            
            // Asegúrate que el Excel tenga formato YYYY-MM-DD para la fecha
            'fecha_nacimiento' => $row['fecha_nacimiento'], 
            
            'carrera' => $row['carrera'],
            'año' => $row['ano'] ?? $row['año'], // Acepta 'ano' o 'año' como cabecera
            'sexo' => strtoupper($row['sexo']),
            'correo' => $row['correo'],
            'celular' => $row['celular'] ?? null,
            
            'uid' => null, // El UID se deja nulo
            
            'estado' => true,
            'last_action' => null,
            'device_id' => null,
            
            'created_by' => $userId, // Auditoría
        ]);
    }

    public function prepareForValidation($data, $index)
    {
        // Forzamos que el CI siempre sea un string
        // Así, 7878 (número) se convierte en "7878" (string)
        if (isset($data['ci'])) {
            $data['ci'] = (string) $data['ci'];
        }

        return $data;
    }

    /**
     * Define las reglas de validación para cada fila.
     */
    public function rules(): array
    {
        return [
            // El '*' indica que se aplica a cada fila
            
            // MODIFICADO: Quitamos 'string' y añadimos 'max' y 'regex'
            '*.ci' => [
                'required',
                'distinct',
                'unique:students,ci',
                'max:255',
                'regex:/^[a-zA-Z0-9\-]+$/' // Acepta números, letras y guiones
            ],
            
            '*.correo' => ['required', 'email', 'distinct', 'unique:students,correo'],
            
            '*.nombre' => 'required|string|max:255',
            '*.primer_apellido' => 'required|string|max:255',
            
            // Valida que el formato de fecha sea YYYY-MM-DD
            '*.fecha_nacimiento' => 'required|date_format:Y-m-d', 
            
            '*.carrera' => 'required|in:Contabilidad,Secretariado,Mercadotecnia,Sistemas',
            
            // Valida 'ano' o 'año'
            '*.ano' => 'nullable|in:Primer Año,Segundo Año,Tercer Año',
            '*.año' => 'nullable|in:Primer Año,Segundo Año,Tercer Año',
            
            '*.sexo' => 'required|in:MASCULINO,FEMENINO',
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