<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestamo extends Model
{
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

    public function cuotas()
    {
        return $this->hasMany(CuotaPrestamo::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoPrestamo::class);
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }
}