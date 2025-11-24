<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin 1
        Usuario::create([
            'nome' => 'Matheus Heinen',
            'email' => 'matheus@pilates.com',
            'senha' => Hash::make('password'), // Senha padrão 'password'
            'tipo' => 'aluno',
            // Adicione outros campos obrigatórios se houver (cpf, telefone, etc.)
        ]);

        // Admin 2
        Usuario::create([
            'nome' => 'Administrador Geral',
            'email' => 'admin@pilates.com',
            'senha' => Hash::make('12345678'),
            'tipo' => 'admin',
        ]);
    }
}
