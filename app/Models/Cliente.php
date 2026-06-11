<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = [
        'empresa_id',
        'nombre',
        'documento',
        'telefono',
        'direccion',
        'correo',
        'estado',
    ];

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'cliente_id');
    }
}