<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('paterno', 100);
            $table->string('materno', 100);
            $table->string('nombres', 150);
            $table->string('ci', 15)->unique();
            $table->string('ci_expedicion', 10);
            $table->string('sexo', 15);
            $table->date('fecha_nacimiento');
            $table->string('lugar_nacimiento_provincia', 100)->nullable();
            $table->string('lugar_nacimiento_ciudad', 100)->nullable();
            $table->string('estado_civil', 20)->nullable();
            $table->integer('numero_hijos')->unsigned()->default(0);
            $table->string('email', 150)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('direccion_actual', 255)->nullable();
            $table->date('fecha_ingreso_fundacion')->nullable();
            $table->string('cargo_actual', 100)->nullable();
            $table->string('oficina_actual', 100)->nullable();
            $table->double('longitude', 10, 6)->nullable();
            $table->double('latitude', 10, 6)->nullable();
            $table->string('url_croquis', 255)->nullable();
            $table->string('url_foto', 255)->nullable();
            $table->enum('estado', ['aprobado', 'observado', 'rechazado'])->nullable()->default('observado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};