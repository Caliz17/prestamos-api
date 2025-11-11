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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes');
            $table->decimal('monto_aprobado', 12, 2);
            $table->timestamp('fecha_aprobacion')->useCurrent();
            $table->decimal('tasa_interes', 5, 2);
            $table->integer('plazo_meses');
            $table->decimal('saldo_actual', 12, 2);
            $table->string('estado')->default('ACTIVO'); // ACTIVO | PAGADO | MOROSO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
