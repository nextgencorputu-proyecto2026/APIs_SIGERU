<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credenciales extends Model
{
    protected $table = 'credenciales';

    protected $primaryKey = 'idUsu';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'idUsu',
        'mail',
        'contrasena',
    ];
}
