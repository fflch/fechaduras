<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios_bloqueados', function (Blueprint $table) {
            $table->id();
            $table->integer('codpes');
            $table->foreignId('fechadura_id')->constrained()->onDelete('cascade');
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unique(['codpes', 'fechadura_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_bloqueados');
    }
};
