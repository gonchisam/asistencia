<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos'; // Definir el mombre de la tabla
    protected $fillable = ['nombre', 'precio', 'stock','imagen'];
    // Campos permitidos para inserción masiva

    // Mutador para convertir 'nombre' a mayúsculas antes de guardar
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }
}
