<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    // 1.- indicamos la tabla que va a utilizar de la base de datos
    protected $table = 'agendas';

    // relacion de muchos a uno inversa(muchos a uno)
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id'); // Recibe a Users
    }

    // Recibe a evento
    public function evento()
    {
        return $this->belongsTo('App\Models\Evento', 'eventos_id'); // Recibe a Evento
    }
}
