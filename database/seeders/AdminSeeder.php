<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário 1: Só cria se não achar este email
        Usuario::firstOrCreate(
            ['email' => 'matheus@pilates.com'], // Condição de busca
            [
                'nome' => 'Matheus Heinen',
                'senha' => Hash::make('password'),
                'tipo' => 'aluno',
            ]
        );

        // Usuário 2: Só cria se não achar este email
        Usuario::firstOrCreate(
            ['email' => 'admin@pilates.com'], // Condição de busca
            [
                'nome' => 'Administrador Geral',
                'senha' => Hash::make('12345678'),
                'tipo' => 'admin',
            ]
        );
    }
}
