<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paquetes', function (Blueprint $table) {
            $table->id(); // id del paquete (clave primaria)

            $table->unsignedBigInteger('user_id'); // clave foránea al usuario

            $table->decimal('peso', 8, 2); // peso del paquete
            $table->string('direccion_actual');
            $table->string('direccion_origen'); // dirección de origen
            $table->string('direccion_destino'); // dirección de destino
            $table->string('estado')->default('pendiente'); // estado del envío
            $table->timestamps();

            // clave foránea que conecta con la tabla users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquetes');
    }
};
