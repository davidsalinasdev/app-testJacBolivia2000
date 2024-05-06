<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Punto extends Model
{
    use HasFactory;

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'puntos';

    // relacion de muchos a uno inversa(muchos a uno)
    public function evento()
    {
        return $this->belongsTo('App\Models\Evento', 'eventos_id'); // Recibe a eventos
    }

    // relacion de muchos a uno inversa(muchos a uno)
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id'); // Recibe a usuarios
    }
}
