<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestEstudiante extends Model
{
    use HasFactory;

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'test_estudiantes';

    // relacion de muchos a uno inversa(muchos a uno)
    public function prueba()
    {
        return $this->belongsTo('App\Models\Prueba', 'pruebas_id'); // Recibe a Pruebas
    }
}
