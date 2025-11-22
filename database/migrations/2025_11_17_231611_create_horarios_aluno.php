<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('horarios_aluno', function (Blueprint $table) {
            $table->id();

            // Liga à Inscrição (Contrato)
            $table->foreignId('inscricao_id')->constrained('inscricoes')->onDelete('cascade');

            // Liga ao Slot Fixo da Escola
            $table->foreignId('horario_agenda_id')->constrained('horarios_agenda');

            $table->string('status')->default('ativo');

            $table->timestamps();

            // Restrição de unicidade
            $table->unique(['inscricao_id', 'horario_agenda_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_aluno');
    }
};
