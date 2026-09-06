<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camion extends Model
{
    protected $table = 'vehiculos';

    protected $primaryKey = 'idVehiculo';

    public $timestamps = false;

    protected $fillable = [
        'matricula',
        'marca',
        'tipo',
        'estado',
        'capacidad',
    ];
}