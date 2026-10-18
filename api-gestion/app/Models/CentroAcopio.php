<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroAcopio extends Model
{
    protected $table = 'centro';

    protected $primaryKey = 'idCentro';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'capacidad',
        'tipo',
        'ubicacionX',
        'ubicacionY',
    ];
}