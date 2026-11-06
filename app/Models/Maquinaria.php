<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maquinaria extends Model
{
    protected $table = 'maquinaria';

    protected $primaryKey = 'idMaquinaria';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'idCentro',
    ];
}