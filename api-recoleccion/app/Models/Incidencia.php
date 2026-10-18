<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Incidencia extends Model
{
    public const TIPOS_VEHICULO = ['Vehículo averiado', 'Sistema informático de vehículo defectuoso', 'Sistema de recolección averiado'];

    public const TIPOS_CONTENEDOR = ['Residuos fuera de contenedor', 'Incendiado', 'Roto', 'Ruta a contenedor obstruida'];

    protected $table = 'incidencia';

    protected $primaryKey = 'idIncidencia';

    public $timestamps = false;

    protected $fillable = ['tipo', 'prioridad', 'estado', 'fechaCreacion', 'fechaResolucion'];

    protected function casts(): array
    {
        return ['fechaCreacion' => 'datetime', 'fechaResolucion' => 'datetime'];
    }

    public function vehiculos(): BelongsToMany
    {
        return $this->belongsToMany(Vehiculo::class, 'vehiculo_presenta', 'idIncidencia', 'idVehiculo');
    }

    public function contenedores(): BelongsToMany
    {
        return $this->belongsToMany(Contenedor::class, 'contenedor_presenta', 'idIncidencia', 'idContenedor');
    }

    public function reportantes(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'reporta', 'idIncidencia', 'idUsu');
    }

    public function controladores(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'controla', 'idIncidencia', 'idUsu');
    }

    public function cuadrillas(): BelongsToMany
    {
        return $this->belongsToMany(Cuadrilla::class, 'atiende', 'idIncidencia', 'idCuadrilla')
            ->withPivot('idAtencion', 'idVehiculo', 'fechaHoraIni', 'fechaHoraFin');
    }
}
