<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Estudiante extends Model
{
    use HasFactory;

    // Asegura que el modelo apunte a la tabla correcta 'students'.
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
        'last_action', // Me faltó este campo en la respuesta anterior, lo he añadido.
        'estado',
        'created_by',
        'updated_by',
    ];

    /**
     * The "booted" method of the model.
     * Sobreescribe para asignar automáticamente el usuario que crea/actualiza.
     */
    protected static function booted()
    {
        static::creating(function ($estudiante) {
            $estudiante->created_by = Auth::id() ?? 1;
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

    // Relación para obtener las asistencias de un estudiante
    public function asistencias()
    {
        // Esta relación es crucial para que DB::table('asistencias') funcione
        // correctamente y sepa a qué UID pertenece cada estudiante.
        return $this->hasMany(Asistencia::class, 'uid', 'uid');
    }
}