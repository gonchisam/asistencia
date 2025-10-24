<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoHorario extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'curso_id',
        'periodo_id',
        'dia_semana',
        'aula_id',
    ];

    /**
     * Obtiene el curso al que pertenece este horario.
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Obtiene el periodo (hora) de este horario.
     */
    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Obtiene el aula de este horario.
     */
    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
}