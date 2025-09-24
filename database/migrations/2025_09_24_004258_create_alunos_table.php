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
        Schema::create('alunos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
        $table->string('genero')->nullable();
        $table->date('data_nascimento')->nullable();
        $table->string('profissao')->nullable();
        $table->string('celular');
        $table->decimal('altura', 3, 2)->nullable();
        $table->decimal('peso', 5, 2)->nullable();
        $table->text('queixa_principal')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
