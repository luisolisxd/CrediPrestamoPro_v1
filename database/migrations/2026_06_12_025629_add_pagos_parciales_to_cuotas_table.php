<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('cuotas_prestamos', function (Blueprint $table) {
        $table->decimal('interes_pagado', 10, 2)->default(0)->after('monto_pagado');
        $table->decimal('capital_pagado', 10, 2)->default(0)->after('interes_pagado');
    });
}

public function down()
{
    Schema::table('cuotas_prestamos', function (Blueprint $table) {
        $table->dropColumn(['interes_pagado', 'capital_pagado']);
    });
}
};
