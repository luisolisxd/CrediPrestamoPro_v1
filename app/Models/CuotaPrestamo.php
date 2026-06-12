<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuotaPrestamo extends Model
{
    protected $table = 'cuotas_prestamos';

    protected $fillable = [
        'prestamo_id',
        'cliente_id',
        'empresa_id',
        'numero_cuota',
        'fecha_vencimiento',
        'capital',
        'interes',
        'total',
        'estado',
        'fecha_pago',
        'monto_pagado',

        // AGREGAR ESTOS
        'interes_pagado',
        'capital_pagado',

        'forma_pago_id',
        'numero_operacion',
        'usuario_pago_id',
    ];
}