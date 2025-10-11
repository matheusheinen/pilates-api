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
        'data_hora_inicio',
        'duracao_minutos',
        'status',
        'observacoes',
    ];

    /**
     * Define os tipos de dados para os atributos, garantindo que 'data_hora_inicio'
     * seja sempre um objeto Carbon para facilitar manipulações.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_hora_inicio' => 'datetime',
    ];

    /**
     * Define a relação: uma Aula pertence a uma Inscrição.
     */
    public function inscricao(): BelongsTo
    {
        return $this->belongsTo(Inscricao::class);
    }
}
