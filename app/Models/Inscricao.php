<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'plano_id',
        'data_inicio',
        'ativo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function horariosFixos()
    {
        return $this->hasMany(HorarioFixo::class);
    }
}