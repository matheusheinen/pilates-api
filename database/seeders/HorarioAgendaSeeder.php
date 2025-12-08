<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HorarioAgenda;
use Carbon\Carbon;

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

        $diasSemana = [
            'segunda-feira',
            'terça-feira',
            'quarta-feira',
            'quinta-feira',
            'sexta-feira',
        ];

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
