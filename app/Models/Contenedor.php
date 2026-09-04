<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contenedor extends Model
{
    protected $table = 'contenedor';

    protected $primaryKey = 'idContenedor';

    public $timestamps = false;

    protected $fillable = [
        'UbicacionX',
        'UbicacionY',
        'Estado',
        'nivelLlenado',
        'tipoResiduo',
        'idRuta',
    ];
}
