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
        $table->time('horario_fim');
        $table->foreignId('inscricao_id')->nullable()->constrained('inscricoes')->onDelete('set null');

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
