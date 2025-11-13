<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaNoLaborable extends Model
{
    use HasFactory;

    protected $table = 'dias_no_laborables';

    protected $fillable = [
        'fecha',
        'tipo',
        'descripcion',
        'recurrente',
        'gestion_id'
    ];

    protected $casts = [
        'fecha'      => 'date',
        'recurrente' => 'boolean',
    ];

    /**
     * Retorna el color sugerido para el calendario según el tipo.
     */
    public function getColorAttribute()
    {
        return match($this->tipo) {
            'FERIADO'    => '#dc3545', // Rojo
            'VACACION'   => '#ffc107', // Amarillo
            'EMERGENCIA' => '#343a40', // Oscuro (Bloqueos)
            'TOLERANCIA' => '#17a2b8', // Azulito
            default      => '#6c757d',
        };
    }
}