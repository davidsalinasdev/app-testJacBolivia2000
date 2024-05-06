<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prueba extends Model
{
    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'pruebas';

    // relacion de muchos a uno inversa(muchos a uno)
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id'); // Recibe a Users
    }
}
