<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'data_aula',
        'horario',
        'horario_fim',
        'status',
        'tipo',
        'observacoes',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}