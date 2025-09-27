<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioFixo extends Model
{
    use HasFactory;

    // Especifica o nome da tabela, já que o nome do modelo não segue a convenção exata
    protected $table = 'horarios_fixos';

    protected $fillable = [
        'inscricao_id',
        'dia_da_semana',
        'horario',
        'horario_fim',
    ];

    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class);
    }
}