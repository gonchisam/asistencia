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
        'docente_id', // <-- NUEVO CAMPO
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
        return $this->belongsToMany(Estudiante::class, 'curso_estudiante');
    }

    /**
     * ¡NUEVA RELACIÓN! Obtiene el docente asignado al curso.
     */
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}