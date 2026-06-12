<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_prestamos', function (Blueprint $blueprint) {
            // Cambiamos el ENUM o VARCHAR corto a un VARCHAR de 50 caracteres
            $blueprint->string('tipo', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_prestamos', function (Blueprint $blueprint) {
            // En caso de revertir, regresa a un estado básico (opcional)
            $blueprint->string('tipo', 20)->change();
        });
    }
};