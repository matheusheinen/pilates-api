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
        Plano::create([
            'nome' => '1x por semana',
            'numero_aulas' => 1,
            'preco' => 120.00,
        ]);

        Plano::create([
            'nome' => '2x por semana',
            'numero_aulas' => 2,
            'preco' => 200.00,
        ]);
    }
}