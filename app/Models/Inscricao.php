<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    use HasFactory;

    protected $table = 'inscricoes';

    protected $fillable = [
        'usuario_id',
        'plano_id',
        'data_inicio',
        'ativo' // ou 'status'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    /**
     * Relacionamento Muitos-para-Muitos com HorarioAgenda.
     */
    public function horarios()
    {
        return $this->belongsToMany(HorarioAgenda::class, 'horario_inscricao', 'inscricao_id', 'horario_agenda_id')
                    ->withTimestamps();
    }
}
