<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Credenciales extends Authenticatable implements JWTSubject
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

    protected $hidden = [
        'contrasena',
    ];

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}