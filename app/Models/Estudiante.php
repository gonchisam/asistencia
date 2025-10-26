<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Estudiante extends Authenticatable
{
    use HasApiTokens, HasFactory;

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
        'device_id',
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

    protected $appends = ['nombre_completo'];

    /**
     * Mutators para convertir a mayúsculas al guardar
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => mb_strtoupper(trim($value), 'UTF-8'),
        );
    }

    protected function primerApellido(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => mb_strtoupper(trim($value), 'UTF-8'),
        );
    }

    protected function segundoApellido(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? mb_strtoupper(trim($value), 'UTF-8') : null,
        );
    }

    /**
     * El método "booted" del modelo.
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

    /**
     * Relación para obtener las asistencias de un estudiante.
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

    /**
     * Obtiene los cursos en los que está inscrito el estudiante.
     */
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_estudiante');
    }
}