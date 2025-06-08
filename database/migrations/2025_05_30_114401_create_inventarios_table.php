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
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->foreignId('compra_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('venta_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('cantidad_entrada')->default(0);
            $table->integer('cantidad_salida')->default(0);
            $table->string('tipo_movimiento'); // 'entrada', 'salida', 'ajuste'
            $table->dateTime('fecha_actualizacion');
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
