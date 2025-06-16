<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->nullable()->constrained('ordens')->onDelete('set null');
            $table->string('asunto');
            $table->string('estado')->default('pendiente');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('agente_id')->nullable()->constrained('users'); // Relación con agente de soporte
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
