<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Horários (Template da Agenda)
        Schema::create('horarios_agenda', function (Blueprint $table) {
            $table->id();
            $table->integer('dia_semana'); // 1 = Segunda, 2 = Terça...
            $table->time('horario_inicio');
            $table->integer('duracao_minutos')->default(50);

            // Limite de alunos neste horário (Ex: 3 vagas)
            $table->integer('vagas_totais')->default(3);

            $table->string('status')->default('ativo'); // ativo/inativo
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('horario_inscricao');
        Schema::dropIfExists('horarios_agenda');
    }
};
