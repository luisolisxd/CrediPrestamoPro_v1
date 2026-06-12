<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestamo extends Model
{
    /**
     * Forzamos a Laravel a que siempre cargue al cliente de forma automática
     */
    protected $with = ['cliente'];

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'tipo_prestamo',
        'capital_prestado',
        'capital_pendiente',
        'numero_cuotas',
        'frecuencia_pago',
        'tasa_interes',
        'dia_pago',
        'cuotas_dobles_adicionales',
        'estado',
        'fecha_inicio',
        'fecha_vencimiento',
        'interes_generado',
        'total_cobrado',
    ];

    /**
     * Relación con el Cliente (CORREGIDA)
     * Cambiamos 'id_cliente' por 'cliente_id' para que coincida exactamente con tu base de datos
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaPrestamo::class, 'prestamo_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoPrestamo::class, 'prestamo_id');
    }
}