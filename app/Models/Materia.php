<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'carrera',
        'ano_cursado',
    ];

    /**
     * Obtiene los cursos (grupos/paralelos) de esta materia.
     */
    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }
}