<?php

namespace App\Imports;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Aula;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CursosCompletosImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private $failures = [];
    private $resumen = [
        'cursos_creados' => 0,
        'cursos_actualizados' => 0,
        'horarios_creados' => 0,
    ];

    /**
     * Mapeo de días de la semana (texto -> número)
     */
    private $diasSemana = [
        'lunes' => 1,
        'martes' => 2,
        'miercoles' => 3,
        'miércoles' => 3,
        'jueves' => 4,
        'viernes' => 5,
        'sabado' => 6,
        'sábado' => 6,
        'domingo' => 7,
    ];

    /**
     * Procesa cada fila del Excel
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $filaNumero = $index + 2; // +2 porque empezamos en fila 2 (después del header)
                
                // 1. BUSCAR O CREAR LA MATERIA
                $materia = Materia::where('nombre', strtoupper(trim($row['materia'])))
                                  ->where('carrera', trim($row['carrera']))
                                  ->where('ano_cursado', trim($row['ano_cursado']))
                                  ->first();

                if (!$materia) {
                    $this->failures[] = new Failure(
                        $filaNumero,
                        'materia',
                        ['No se encontró la materia "' . $row['materia'] . '" para ' . $row['carrera'] . ' - ' . $row['ano_cursado']],
                        $row->toArray()
                    );
                    continue;
                }

                // 2. BUSCAR EL DOCENTE POR CORREO
                $docente = null;
                if (!empty($row['docente_correo'])) {
                    $docente = User::where('email', trim($row['docente_correo']))
                                   ->where('role', 'docente')
                                   ->first();
                    
                    if (!$docente) {
                        $this->failures[] = new Failure(
                            $filaNumero,
                            'docente_correo',
                            ['No se encontró ningún docente con el correo "' . $row['docente_correo'] . '"'],
                            $row->toArray()
                        );
                        continue;
                    }
                }

                // 3. BUSCAR O CREAR EL CURSO
                $curso = Curso::firstOrCreate(
                    [
                        'materia_id' => $materia->id,
                        'paralelo' => strtoupper(trim($row['paralelo'])),
                        'gestion' => trim($row['gestion']),
                    ],
                    [
                        'docente_id' => $docente ? $docente->id : null,
                    ]
                );

                if ($curso->wasRecentlyCreated) {
                    $this->resumen['cursos_creados']++;
                    Log::info("Curso creado: {$materia->nombre} - Paralelo {$curso->paralelo}");
                } else {
                    // Si el curso ya existía, actualizamos el docente si se proporcionó uno nuevo
                    if ($docente && $curso->docente_id !== $docente->id) {
                        $curso->update(['docente_id' => $docente->id]);
                        $this->resumen['cursos_actualizados']++;
                        Log::info("Curso actualizado con nuevo docente: {$materia->nombre} - Paralelo {$curso->paralelo}");
                    }
                }

                // 4. PROCESAR DÍA DE LA SEMANA
                $diaNombre = strtolower(trim($row['dia']));
                $diaNumero = $this->diasSemana[$diaNombre] ?? null;

                if (!$diaNumero) {
                    $this->failures[] = new Failure(
                        $filaNumero,
                        'dia',
                        ['Día inválido: "' . $row['dia'] . '". Use: lunes, martes, miércoles, jueves, viernes, sábado o domingo'],
                        $row->toArray()
                    );
                    continue;
                }

                // 5. BUSCAR EL PERIODO
                $periodo = Periodo::where('nombre', trim($row['periodo']))->first();
                if (!$periodo) {
                    $this->failures[] = new Failure(
                        $filaNumero,
                        'periodo',
                        ['No se encontró el periodo "' . $row['periodo'] . '"'],
                        $row->toArray()
                    );
                    continue;
                }

                // 6. BUSCAR EL AULA
                $aula = Aula::where('nombre', trim($row['aula']))->first();
                if (!$aula) {
                    $this->failures[] = new Failure(
                        $filaNumero,
                        'aula',
                        ['No se encontró el aula "' . $row['aula'] . '"'],
                        $row->toArray()
                    );
                    continue;
                }

                // 7. CREAR EL HORARIO (Evitando duplicados)
                $horarioExiste = $curso->horarios()
                    ->where('dia_semana', $diaNumero)
                    ->where('periodo_id', $periodo->id)
                    ->where('aula_id', $aula->id)
                    ->exists();

                if (!$horarioExiste) {
                    $curso->horarios()->create([
                        'dia_semana' => $diaNumero,
                        'periodo_id' => $periodo->id,
                        'aula_id' => $aula->id,
                    ]);
                    $this->resumen['horarios_creados']++;
                    Log::info("Horario creado para curso {$curso->id}: {$diaNombre} - {$periodo->nombre} - {$aula->nombre}");
                }

            } catch (\Exception $e) {
                Log::error("Error procesando fila " . ($index + 2) . ": " . $e->getMessage());
                $this->failures[] = new Failure(
                    $index + 2,
                    'general',
                    ['Error inesperado: ' . $e->getMessage()],
                    $row->toArray()
                );
            }
        }
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            '*.dia' => 'required|string',
            '*.periodo' => 'required|string',
            '*.aula' => 'required|string',
            '*.materia' => 'required|string',
            '*.carrera' => 'required|string',
            '*.ano_cursado' => 'required|string',
            '*.paralelo' => 'required|string',
            '*.gestion' => 'required|integer|min:2000',
            '*.docente_correo' => 'nullable|email',
        ];
    }

    /**
     * Captura fallos de validación
     */
    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * Obtiene los errores acumulados
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Obtiene el resumen de la importación
     */
    public function getResumen()
    {
        return $this->resumen;
    }
}