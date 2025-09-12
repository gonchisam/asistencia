<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Asistencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'nombre', // Este campo es redundante si tienes una relación directa a estudiantes, pero puede ser útil para el historial
        'accion',
        'modo',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    // Relación con el estudiante (asumiendo que 'uid' en asistencias se relaciona con 'uid' en students)
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'uid', 'uid');
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
     * Obtiene el estado de llegada.
     * Si la acción es 'ENTRADA', siempre retorna 'a_tiempo' (sin restricciones de horario).
     * Para otras acciones, retorna 'N/A'.
     * @return string
     */
    public function getEstadoLlegadaAttribute(): string
    {
        if ($this->accion === 'ENTRADA') {
            return 'a_tiempo'; // Todas las entradas se consideran 'a_tiempo'
        } else {
            return 'N/A'; // No aplicable para SALIDA u otras acciones
        }
    }
}