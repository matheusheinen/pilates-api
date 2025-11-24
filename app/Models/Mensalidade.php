<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mensalidade extends Model
{
    protected $fillable = [
        'inscricao_id',
        'data_vencimento',
        'valor',
        'status'
    ];

    // Relacionamento com a Inscrição (e através dela, com o Usuário/Aluno)
    public function inscricao(): BelongsTo
    {
        return $this->belongsTo(Inscricao::class);
    }

    // Uma mensalidade pode ter um pagamento registrado
    public function pagamento(): HasOne
    {
        return $this->hasOne(Pagamento::class);
    }
}
