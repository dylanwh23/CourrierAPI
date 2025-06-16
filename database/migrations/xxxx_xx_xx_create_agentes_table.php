<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_soportes', function (Blueprint $table) {
            
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('estado')->default('desconectado'); // activo o desconectado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }

};
