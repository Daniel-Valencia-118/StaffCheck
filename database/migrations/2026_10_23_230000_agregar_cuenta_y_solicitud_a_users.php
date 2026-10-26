<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('pendiente')->default(false)->after('estado');
            $table->unsignedInteger('id_persona')->nullable()->unique()->after('pendiente');

            $table->foreign('id_persona')->references('id')->on('personas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_persona']);
            $table->dropUnique(['id_persona']);
            $table->dropColumn(['pendiente', 'id_persona']);
        });
    }
};
