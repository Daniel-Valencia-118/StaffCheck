<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->increments('id_familiar');
            $table->string('nombre', 100);
            $table->string('paterno', 100);
            $table->string('materno', 100);
            $table->string('parentesco_relacion', 50);
            $table->integer('edad')->unsigned()->nullable();
            $table->string('telefono_celular', 20);
            $table->boolean('es_familiar')->default(true);
            $table->integer('id_persona')->unsigned();
            $table->timestamps();

            $table->index(['id_persona', 'es_familiar']);
            $table->foreign('id_persona')
                  ->references('id')->on('personas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};