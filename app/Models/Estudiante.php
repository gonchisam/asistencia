<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // Importa la clase Str para generar el UUID
use Laravel\Sanctum\HasApiTokens;

class Estudiante extends Model
{
    use HasApiTokens, HasFactory;
    
    // La tabla es 'students'
    protected $table = 'students';

    protected $fillable = [
        'uid',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'ci',
        'fecha_nacimiento',
        'carrera',
        'año',
        'sexo',
        'celular',
        'correo',
        'last_action',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    /**
     * El método "booted" del modelo.
     * Sobreescribe para asignar automáticamente el usuario que crea/actualiza
     * y para generar un UUID para el campo 'uid' antes de crear el registro.
     */
    protected static function booted()
    {
        static::creating(function ($estudiante) {
            // Se ha comentado esta línea para que se use el UID del formulario
            // en lugar de generar uno nuevo.
            // $estudiante->uid = (string) Str::uuid();
            
            // Asigna el usuario creador y actualizador
            $estudiante->created_by = Auth::id() ?? 1;
            $estudiante->updated_by = Auth::id() ?? 1;
        });

        static::updating(function ($estudiante) {
            // Asigna el usuario que actualiza el registro
            $estudiante->updated_by = Auth::id() ?? 1;
        });
    }

    /**
     * Relación con el usuario que creó el registro.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con el usuario que actualizó el registro por última vez.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relación para obtener las asistencias de un estudiante.
     * Conecta el campo 'uid' de ambas tablas.
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'uid', 'uid');
    }

    /**
     * Scope para obtener solo estudiantes activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    /**
     * Accessor para obtener el nombre completo.
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->primer_apellido . ' ' . $this->segundo_apellido);
    }
}