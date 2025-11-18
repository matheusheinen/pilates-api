<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id();
            // Garante que se o usuário for deletado, a inscrição também seja
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('plano_id')->constrained('planos')->onDelete('cascade');

            $table->date('data_inicio');

            // Mudamos de boolean('ativo') para string('status') para suportar 'trancada', 'cancelada', etc.
            $table->string('status')->default('ativa');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricoes');
    }
};
