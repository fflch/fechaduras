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
        Schema::create('fechadura_setor', function (Blueprint $table) {
            $table->foreignId('fechadura_id')->constrained()->onDelete('cascade');
            $table->foreignId('setor_id')->constrained('setores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechadura_setor');
    }
};
