<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroAcopio extends Model
{
    protected $table = 'centro_acopio';

    protected $primaryKey = 'idCentroAcopio';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'capacidad',
        'tipoResiduo',
    ];
}
