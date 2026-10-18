<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cuadrilla extends Model
{
    protected $table = 'cuadrilla';

    protected $primaryKey = 'idCuadrilla';

    public $timestamps = false;

    public function integrantes(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'integra_cuadrilla', 'idCuadrilla', 'idUsu')->withPivot('esResponsable');
    }

    public function vehiculos(): BelongsToMany
    {
        return $this->belongsToMany(Vehiculo::class, 'cuadrilla_vehiculo', 'idCuadrilla', 'idVehiculo');
    }

    public function rutas(): BelongsToMany
    {
        return $this->belongsToMany(Ruta::class, 'asigna_ruta', 'idCuadrilla', 'idRuta')->withPivot('idUsu');
    }
}
