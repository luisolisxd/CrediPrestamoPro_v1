<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Saltamos cuotas_prestamos porque el error nos confirmó que ya tiene interes_pagado y capital_pagado

        // 2. Añadimos el acumulador global de moras a la cabecera del préstamo
        if (!Schema::hasColumn('prestamos', 'mora_pagada')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->decimal('mora_pagada', 10, 2)->default(0)->after('total_cobrado');
            });
        }

        // 3. Añadimos la columna para auditar la mora en cada movimiento de caja
        if (!Schema::hasColumn('movimientos_prestamos', 'mora_cobrada')) {
            Schema::table('movimientos_prestamos', function (Blueprint $table) {
                $table->decimal('mora_cobrada', 10, 2)->default(0)->after('interes_cobrado');
            });
        }
    }

    public function down()
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn('mora_pagada');
        });

        Schema::table('movimientos_prestamos', function (Blueprint $table) {
            $table->dropColumn('mora_cobrada');
        });
    }
};