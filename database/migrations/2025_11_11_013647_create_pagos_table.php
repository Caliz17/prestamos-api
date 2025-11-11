<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('prestamo_id')->constrained('prestamos');
            $table->timestamp('fecha_pago')->useCurrent();
            $table->decimal('monto_pagado', 12, 2);
            $table->string('metodo_pago')->default('EFECTIVO');
            $table->string('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
