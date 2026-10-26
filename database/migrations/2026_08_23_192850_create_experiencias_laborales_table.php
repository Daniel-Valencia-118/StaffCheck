<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiencias_laborales', function (Blueprint $table) {
            $table->increments('id_exp_lab');
            $table->string('institucion', 200);
            $table->string('cargo', 150);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->integer('id_persona')->unsigned();
            $table->timestamps();

            $table->index(['id_persona', 'fecha_inicio']);
            $table->foreign('id_persona')
                  ->references('id')->on('personas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiencias_laborales');
    }
};