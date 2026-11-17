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
        Schema::create('fechadura_acessos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fechadura_id')->constrained('fechaduras')->cascadeOnDelete();
            $table->unsignedBigInteger('codpes');
            // Espelho do que existe na fechadura (preenchido pela sincronização noturna)
            $table->boolean('tem_foto')->default(false);
            $table->boolean('tem_senha')->default(false);
            // Pedidos feitos em "Meu perfil", aguardando a sincronização noturna
            $table->boolean('foto_pendente')->default(false);
            $table->text('senha_pendente')->nullable(); 
            $table->unique(['fechadura_id', 'codpes']);
            $table->index('codpes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechadura_acessos');
    }
};
