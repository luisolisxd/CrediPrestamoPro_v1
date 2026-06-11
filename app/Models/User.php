<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
        'empresa_id',
        'cliente_id', // <-- Agregamos el nuevo campo aquí
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Obtener los atributos que deben ser casteados.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Conexión con la tabla de Roles
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    /**
     * Conexión con la tabla de Empresas
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Conexión con la tabla de Clientes
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}