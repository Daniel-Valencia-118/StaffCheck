<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enlaces_correccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_persona');
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expira_at');
            $table->timestamp('usado_at')->nullable();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_persona')->references('id')->on('personas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enlaces_correccion');
    }
};
