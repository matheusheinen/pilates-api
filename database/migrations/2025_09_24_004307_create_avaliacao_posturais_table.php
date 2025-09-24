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
        Schema::create('avaliacoes_posturais', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
        $table->date('data_avaliacao');
        
        // --- VISTA ANTERIOR ---
        $table->string('anterior_cabeca')->nullable();
        $table->string('anterior_ombros_altura')->nullable();
        $table->string('anterior_maos_altura')->nullable();
        $table->string('anterior_tronco_rotacao')->nullable();
        $table->string('anterior_angulo_tales')->nullable();
        $table->string('anterior_cicatriz_umbilical')->nullable();
        $table->string('anterior_iliacas_altura')->nullable();
        $table->string('anterior_joelhos')->nullable();
        $table->string('anterior_tornozelos')->nullable();
        $table->string('anterior_pes')->nullable();

        // --- VISTA POSTERIOR ---
        $table->string('posterior_escapulas_altura')->nullable();
        $table->string('posterior_escapula_alada')->nullable();
        $table->string('posterior_gibosidade_toracica')->nullable();
        $table->string('posterior_pregas_gluteas')->nullable();
        $table->string('posterior_pregas_popliteas')->nullable();
        $table->string('posterior_lombar_concavidade')->nullable();
        $table->string('posterior_toracica_concavidade')->nullable();
        $table->string('posterior_cervical_concavidade')->nullable();

        // --- VISTA LATERAL ---
        $table->string('lateral_cabeca')->nullable();
        $table->string('lateral_cervical')->nullable();
        $table->string('lateral_ombro')->nullable();
        $table->string('lateral_membro_superior')->nullable();
        $table->string('lateral_toracica')->nullable();
        $table->string('lateral_tronco_rotacao')->nullable();
        $table->string('lateral_abdomen')->nullable();
        $table->string('lateral_lombar')->nullable();
        $table->string('lateral_pelve')->nullable();
        $table->string('lateral_quadril')->nullable();
        $table->string('lateral_joelho')->nullable();
        
        $table->json('anexos')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_posturais');
    }
};
