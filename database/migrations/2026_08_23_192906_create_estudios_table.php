<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudios', function (Blueprint $table) {
            $table->increments('id_estudio');
            $table->string('tipo', 50);
            $table->string('titulo_obtenido', 200);
            $table->string('institucion', 200);
            $table->year('anio');
            $table->integer('id_persona')->unsigned();
            $table->timestamps();

            $table->foreign('id_persona')
                  ->references('id')->on('personas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudios');
    }
};