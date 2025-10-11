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
        Schema::create('aulas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('inscricao_id')->nullable()->constrained('inscricoes')->onDelete('cascade');
        $table->dateTime('data_hora_inicio');
        $table->integer('duracao_minutos')->default(50);
        $table->string('status')->default('disponivel'); // 'disponivel', 'reservada', 'concluida', 'cancelada'
        $table->text('observacoes')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
