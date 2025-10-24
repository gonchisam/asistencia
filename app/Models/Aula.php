<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'ubicacion',
    ];

    /**
     * Obtiene los horarios programados en esta aula.
     */
    public function cursoHorarios()
    {
        return $this->hasMany(CursoHorario::class);
    }
}