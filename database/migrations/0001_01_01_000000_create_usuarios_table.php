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

        $table->string('cpf')->nullable()->unique();
        $table->string('genero')->nullable();
        $table->date('data_nascimento')->nullable();
        $table->string('profissao')->nullable();
        $table->string('celular')->nullable();
        $table->string('lateralidade')->nullable();

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
