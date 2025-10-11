<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aula;
use Carbon\Carbon;

class CancelarAulasVagas extends Command
{
    /**
     * A assinatura do comando do console.
     */
    protected $signature = 'app:cancelar-aulas-vagas';

    /**
     * A descrição do comando do console.
     */
    protected $description = 'Cancela automaticamente as aulas que não foram preenchidas com 24h de antecedência.';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de aulas vagas para cancelamento...');

        // Define o limite de tempo: 24 horas a partir de agora.
        $limiteDeTempo = Carbon::now()->addHours(24);

        // A LÓGICA PRINCIPAL:
        // 1. Encontra todas as aulas que:
        //    - Têm o status 'disponivel'.
        //    - A data de início é ANTES do nosso limite de 24 horas.
        //    - A data de início é DEPOIS de agora (para não cancelar aulas que já passaram).
        $aulasParaCancelar = Aula::where('status', 'disponivel')
                                 ->where('data_hora_inicio', '<', $limiteDeTempo)
                                 ->where('data_hora_inicio', '>', Carbon::now());

        // Obtém o número de aulas antes de as atualizar
        $count = $aulasParaCancelar->count();

        if ($count > 0) {
            // 2. Atualiza o status de todas as aulas encontradas para 'cancelada'.
            $aulasParaCancelar->update(['status' => 'cancelada']);
            $this->info("{$count} aulas vagas foram canceladas.");
        } else {
            $this->info('Nenhuma aula vaga encontrada para cancelar.');
        }

        return 0;
    }
}
