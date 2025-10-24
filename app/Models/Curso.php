<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'materia_id',
        'paralelo',
        'gestion',
    ];

    /**
     * Obtiene la materia a la que pertenece este curso.
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Obtiene los horarios (dia, periodo, aula) de este curso.
     */
    public function horarios()
    {
        return $this->hasMany(CursoHorario::class);
    }

    /**
     * Obtiene los estudiantes inscritos en este curso.
     */
    public function estudiantes()
    {
        // Relación Muchos-a-Muchos con la tabla pivote 'curso_estudiante'
        // y asumiendo que tu modelo de estudiante es 'Estudiante'
        return $this->belongsToMany(Estudiante::class, 'curso_estudiante');
    }
}