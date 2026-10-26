<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->index(['deleted_at', 'created_at'], 'personas_papelera_creado_index');
            $table->index(['deleted_at', 'estado', 'created_at'], 'personas_papelera_estado_index');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('personas_papelera_creado_index');
            $table->dropIndex('personas_papelera_estado_index');
        });
    }
};
