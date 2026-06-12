<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoPrestamo extends Model
{
    protected $table = 'movimientos_prestamos';

    protected $fillable = [
        'prestamo_id',
        'cliente_id',
        'empresa_id',
        'fecha',
        'tipo',
        'capital_antes',
        'monto',
        'forma_pago_id',
        'cuenta_destino',
        'numero_operacion',
        'porcentaje_interes',
        'interes_cobrado',
        'mora_cobrada',
        'capital_cobrado',
        'capital_final',
        'usuario_id',
    ];
}