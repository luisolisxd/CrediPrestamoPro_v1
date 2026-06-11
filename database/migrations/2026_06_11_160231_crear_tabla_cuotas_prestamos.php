<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas_prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prestamo_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('empresa_id');
            $table->integer('numero_cuota');
            $table->date('fecha_vencimiento');
            $table->decimal('capital',12,2);
            $table->decimal('interes',12,2);
            $table->decimal('total',12,2);
            $table->enum('estado', ['PENDIENTE','PAGADA','VENCIDA','BLOQUEADA'])->default('PENDIENTE');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto_pagado',12,2)->nullable();
            $table->unsignedBigInteger('forma_pago_id')->nullable();
            $table->string('numero_operacion')->nullable();
            $table->unsignedBigInteger('usuario_pago_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas_prestamos');
    }
};