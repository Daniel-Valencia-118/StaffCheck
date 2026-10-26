<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisiones_fichas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_persona');
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->text('motivo')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_persona')->references('id')->on('personas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisiones_fichas');
    }
};
