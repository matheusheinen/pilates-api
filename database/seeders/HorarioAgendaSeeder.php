<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HorarioAgenda;

class HorarioAgendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $horarioAbertura = 8;
        $horarioFechamento = 20;
        $duracaoAulaMinutos = 60;
        $vagasPorHorario = 3;

        // Mapeamento: 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta
        // O banco espera INTEIROS, não strings.
        $diasSemana = [1, 2, 3, 4, 5];

        foreach ($diasSemana as $dia) {
            for ($hora = $horarioAbertura; $hora < $horarioFechamento; $hora++) {

                $horaFormatada = sprintf('%02d:00:00', $hora);

                HorarioAgenda::firstOrCreate(
                    [
                        'dia_semana' => $dia,
                        'horario_inicio' => $horaFormatada,
                    ],
                    [
                        'duracao_minutos' => $duracaoAulaMinutos,
                        'vagas_totais' => $vagasPorHorario,
                        'status' => 'ativo',
                    ]
                );
            }
        }
    }
}
