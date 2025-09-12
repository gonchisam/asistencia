<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'students'; // Asegura que apunta a la tabla correcta

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
        'estado',
        'created_by', // Si estás usando esto
        'updated_by', // Si estás usando esto
    ];

    /**
     * The "booted" method of the model.
     * Sobreescribe para asignar automáticamente el usuario que crea/actualiza.
     */
    protected static function booted()
    {
        static::creating(function ($estudiante) {
            $estudiante->created_by = Auth::id() ?? 1; // Asigna el ID del usuario actual o 1 si no hay
            $estudiante->updated_by = Auth::id() ?? 1;
        });

        static::updating(function ($estudiante) {
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

    // Si quieres obtener las asistencias de un estudiante
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'uid', 'uid');
    }
}