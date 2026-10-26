<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'aprobado', 'observado', 'rechazado'])
                ->nullable()->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        DB::table('personas')->where('estado', 'pendiente')->update(['estado' => 'observado']);

        Schema::table('personas', function (Blueprint $table) {
            $table->enum('estado', ['aprobado', 'observado', 'rechazado'])
                ->nullable()->default('observado')->change();
        });
    }
};
