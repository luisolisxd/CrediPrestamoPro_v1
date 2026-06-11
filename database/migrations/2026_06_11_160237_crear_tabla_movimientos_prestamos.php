<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('empresa_id');
            $table->date('fecha');
            $table->enum('tipo', ['DEPÓSITO','PAGO','AJUSTE','CIERRE']);
            $table->decimal('capital_antes',12,2)->default(0);
            $table->decimal('monto',12,2);
            $table->unsignedBigInteger('forma_pago_id')->nullable();
            $table->string('cuenta_destino')->nullable();
            $table->string('numero_operacion')->nullable();
            $table->decimal('porcentaje_interes',5,2)->nullable();
            $table->decimal('interes_cobrado',12,2)->default(0);
            $table->decimal('capital_cobrado',12,2)->default(0);
            $table->decimal('capital_final',12,2)->default(0);
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_prestamos');
    }
};