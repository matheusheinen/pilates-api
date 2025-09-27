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
        Schema::create('usuarios', function (Blueprint $table) {
        $table->id();
        $table->string('nome');
        $table->string('email')->unique();
        $table->timestamp('email_verificado')->nullable();
        $table->string('senha');
        $table->string('tipo')->default('aluno'); // 'aluno' ou 'admin'
        $table->rememberToken();
        $table->timestamps();

        $table->string('genero')->nullable();
        $table->date('data_nascimento')->nullable();
        $table->string('profissao')->nullable();
        $table->string('celular')->nullable();
        $table->decimal('altura', 3, 2)->nullable();
        $table->decimal('peso', 5, 2)->nullable();
        $table->text('queixa_principal')->nullable();
        $table->string('lateralidade')->nullable();
        $table->text('diagnostico_clinico')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
