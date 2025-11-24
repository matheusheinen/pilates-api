<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensalidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscricao_id')->constrained('inscricoes')->onDelete('cascade');
            $table->date('data_vencimento');
            $table->decimal('valor', 10, 2);

            // Status: pendente, em_analise, paga, atrasada, cancelada
            $table->string('status')->default('pendente');

            $table->timestamps();
        });

        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensalidade_id')->constrained('mensalidades')->onDelete('cascade');

            $table->date('data_pagamento');
            $table->decimal('valor_pago', 10, 2);
            $table->string('metodo_pagamento');

            // NOVO: Caminho do arquivo do comprovante
            $table->string('comprovante_path')->nullable();

            // Status do pagamento em si (opcional, mas bom para histórico)
            $table->string('status')->default('aprovado'); // aprovado, em_analise, rejeitado

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
        Schema::dropIfExists('mensalidades');
    }
};
