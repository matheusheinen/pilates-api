<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plano;

class PlanoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plano::firstOrCreate(
            ['nome' => 'Plano 1x na semana'], // Verifica se já existe um plano com este nome
            [
                'preco' => 120.00,
                'numero_aulas' => 1,
            ]
        );

        Plano::firstOrCreate(
            ['nome' => 'Plano 2x na semana'], // Verifica se já existe um plano com este nome
            [
                'preco' => 200.00,
                'numero_aulas' => 2,
            ]
        );
    }
}
