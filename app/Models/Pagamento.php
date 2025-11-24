<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $fillable = [
        'mensalidade_id',
        'data_pagamento',
        'valor_pago',
        'metodo_pagamento',
        'comprovante_path', // <--- Adicionado
        'status'
    ];

    public function mensalidade(): BelongsTo
    {
        return $this->belongsTo(Mensalidade::class);
    }
}
