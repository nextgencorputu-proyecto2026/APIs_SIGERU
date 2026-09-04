<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camion extends Model
{
    protected $table = 'camion';

    protected $primaryKey = 'idVehiculo';

    public $timestamps = false;

    protected $fillable = [
        'idVehiculo',
        'tipo',
        'capacidad',
        'idCentroAcopio',
        'idVertedero',
    ];
}

