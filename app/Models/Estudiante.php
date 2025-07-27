<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'nombre',
        'uid',
        'estado', // Se mantiene 'estado' aquí
        'created_by',
        'updated_by',
    ];

    /**
     * Método boot() para auto-rellenar campos de auditoría al crear/actualizar.
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear un nuevo estudiante
        static::creating(function ($estudiante) {
            $estudiante->created_by = Auth::id() ?? 1; // Usamos 1 como ID de usuario por defecto
            $estudiante->updated_by = Auth::id() ?? 1;
        });

        // Al actualizar un estudiante
        static::updating(function ($estudiante) {
            $estudiante->updated_by = Auth::id() ?? 1; // Usamos 1 como ID de usuario por defecto
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
}