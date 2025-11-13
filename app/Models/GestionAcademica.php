<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GestionAcademica extends Model
{
    use HasFactory;

    protected $table = 'gestiones_academicas';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'actual'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'actual'       => 'boolean',
    ];
    
    // Scope para obtener la gestión activa fácilmente: GestionAcademica::activa()->first()
    public function scopeActiva($query)
    {
        return $query->where('actual', true);
    }
}