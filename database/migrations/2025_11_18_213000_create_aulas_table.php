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
        $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
        $table->foreignId('horario_aluno_id')->nullable()->constrained('horarios_aluno')->onDelete('set null');
        $table->foreignId('horario_agenda_id')->nullable()->constrained('horarios_agenda')->onDelete('set null');
        $table->dateTime('data_hora_inicio');
        $table->integer('duracao_minutos')->default(50);
        $table->string('status')->default('agendada'); // Alterado de 'disponivel'
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
