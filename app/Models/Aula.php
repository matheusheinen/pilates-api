<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aula extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inscricao_id',

        // --- CHAVES ADICIONADAS ---
        'usuario_id',          // Atalho para o aluno (Performance)
        'horario_aluno_id',    // O vínculo fixo (Vaga) que gerou esta aula
        'horario_agenda_id',   // O slot fixo (Segunda 10h) que gerou esta aula
        // --------------------------

        'data_hora_inicio',
        'duracao_minutos',
        'status',
        'observacoes',
    ];

    /**
     * Define os tipos de dados para os atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_hora_inicio' => 'datetime',
    ];


    // --- RELACIONAMENTOS ---

    /**
     * O slot fixo da Agenda (Ex: A aula pertence ao horário Terça 10h da escola).
     */
    public function horarioAgenda(): BelongsTo
    {
        return $this->belongsTo(HorarioAgenda::class, 'horario_agenda_id');
    }

    /**
     * O vínculo do aluno com o horário fixo (Ex: A aula nasceu do contrato de vaga do aluno A).
     */
    public function horarioAluno(): BelongsTo
    {
        return $this->belongsTo(HorarioAluno::class, 'horario_aluno_id');
    }

    /**
     * O contrato principal (Inscrição).
     */
    public function inscricao(): BelongsTo
    {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

    /**
     * O Aluno (Atalho para performance, evita um JOIN extra).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
