<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $primaryKey = 'idUsu';

    public $timestamps = false;

    protected $fillable = [
        'ci',
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'fec_nac',
        'tipo',
        'idCentro',
    ];
}