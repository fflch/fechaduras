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
        // Remove a constraint unique do codpes
        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['codpes']);
        });

        // Adiciona chave única composta (codpes + fechadura_id)
        Schema::table('admins', function (Blueprint $table) {
            $table->unique(['codpes', 'fechadura_id'], 'admin_codpes_fechadura_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
