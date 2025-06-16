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
        Schema::create('ordens', function (Blueprint $table) { //esta mal escrito orden, deberia ser orden pero lo hace laravel automatico xd
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('En espera'); // 'En espera', 'En viaje', 'En centro de distribución', 'Entregado', 'Cancelado'
            $table->float('valor_total')->default(0);
            $table->dateTime('ultima_fecha_actualizacion_estado')->default(now());
            $table->string('tracking_id')->unique();
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens');
    }
};
