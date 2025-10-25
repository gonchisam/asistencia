<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Asistencia extends Model
{
    use HasFactory;
    protected $table = 'asistencias';

    protected $fillable = [
        'uid',
        'nombre',
        'accion',
        'modo',
        'fecha_hora',
        
        // --- NUEVOS CAMPOS ---
        'curso_id',
        'periodo_id',
        'estado_llegada', 
        // --- FIN NUEVOS CAMPOS ---
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    /**
     * Relación con el estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'uid', 'uid');
    }

    /**
     * Relación con el curso (¡NUEVO!)
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Relación con el periodo (¡NUEVO!)
     */
    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Obtiene la fecha y hora formateada.
     * @return string|null
     */
    public function getFechaHoraFormateadaAttribute()
    {
        return $this->fecha_hora ? $this->fecha_hora->format('d/m/Y H:i:s') : null;
    }

    /**
     * ¡ELIMINADO!
     * Ya no necesitamos getEstadoLlegadaAttribute
     * porque ahora se guarda directamente en la BD.
     */
}