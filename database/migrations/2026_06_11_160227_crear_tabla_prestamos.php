<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('cliente_id');
            $table->enum('tipo_prestamo', ['DINAMICO','CUOTA_FIJA','CRONOGRAMA']);
            $table->decimal('capital_prestado', 12, 2);
            $table->decimal('capital_pendiente', 12, 2)->default(0);
            $table->integer('numero_cuotas')->nullable();
            $table->string('frecuencia_pago')->nullable(); // Mensual / Quincenal / Dias
            $table->decimal('tasa_interes', 5, 2)->nullable();
            $table->integer('dia_pago')->nullable();
            $table->json('cuotas_dobles_adicionales')->nullable();
            $table->enum('estado', ['ACTIVO','CERRADO'])->default('ACTIVO');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('interes_generado', 12, 2)->default(0);
            $table->decimal('total_cobrado', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};