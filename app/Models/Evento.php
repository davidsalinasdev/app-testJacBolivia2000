<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'eventos';

    // relacion de muchos a uno inversa(muchos a uno)
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id'); // Recibe a Users
    }

    // Se dirige hacia punto
    public function punto()
    {
        return $this->hasMany('App\Models\Punto'); // se dirige hacia Puntos
    }

    // Se dirige hacia agenda
    public function agenda()
    {
        return $this->hasMany('App\Models\Agenda'); // se dirige hacia Agenda
    }
}
