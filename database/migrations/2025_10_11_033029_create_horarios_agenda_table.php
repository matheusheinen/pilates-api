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
    Schema::create('horarios_agenda', function (Blueprint $table) {
        $table->id();
        $table->integer('dia_semana'); // 1=Seg, 2=Ter, ...
        $table->time('horario_inicio');
        $table->integer('duracao_minutos')->default(50);
        $table->foreignId('inscricao_id')->nullable()->constrained('inscricoes')->onDelete('set null');
        $table->string('status')->default('ativo')->after('inscricao_id');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_agenda');
    }
};
