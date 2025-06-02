<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade'); // Si el producto se elimina, sus registros de inventario también
            $table->integer('cantidad_inicial');
            $table->integer('cantidad_entrada')->default(0);
            $table->integer('cantidad_salida')->default(0);
            $table->integer('cantidad_actual');
            $table->timestamp('fecha_actualizacion')->useCurrent(); // Fecha del movimiento
            $table->string('tipo_movimiento'); // 'entrada', 'salida', 'ajuste'
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
