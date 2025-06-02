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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Usuario que generó el reporte
            $table->string('tipo')->index(); // Tipo de reporte: ventas, compras, pedidos, inventario
            $table->date('fecha_inicio')->nullable(); // Rango de fechas del reporte
            $table->date('fecha_fin')->nullable();
            $table->json('filtros')->nullable(); // Filtros aplicados (cliente, proveedor, categoría, etc.)
            $table->text('descripcion')->nullable(); // Descripción opcional del reporte
            $table->string('estado')->default('generado'); // Estado: generado, archivado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
