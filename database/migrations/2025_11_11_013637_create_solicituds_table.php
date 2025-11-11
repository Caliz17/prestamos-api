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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->decimal('monto_solicitado', 12, 2);
            $table->integer('plazo_meses');
            $table->decimal('tasa_interes', 5, 2);
            $table->string('estado')->default('EN PROCESO');
            $table->string('observaciones')->nullable();
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->string('usuario_crea')->nullable();
            $table->timestamp('fecha_crea')->nullable();
            $table->string('usuario_actualiza')->nullable();
            $table->timestamp('fecha_actualiza')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
