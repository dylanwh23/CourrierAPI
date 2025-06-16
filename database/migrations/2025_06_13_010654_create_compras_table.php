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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->float('valor_declarado');
            $table->string('estado')->default('En espera');
            $table->string('descripcion');
            $table->foreignId('orden_id')->constrained('ordens')->onDelete('cascade');
            $table->string('imagen_factura');
            $table->string('proveedor')->nullable(); // Proveedor de la compra, puede ser nulo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
