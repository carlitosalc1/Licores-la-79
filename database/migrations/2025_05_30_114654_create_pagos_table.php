<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('cascade');
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->decimal('monto_recibido', 10, 2);
            $table->decimal('cambio', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta_credito','tarjeta_debito']);
            $table->timestamp('fecha_pago')->useCurrent();
            $table->string('referencia_pago')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
