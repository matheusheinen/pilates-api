<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'numero_aulas',
        'preco',
    ];

    public function inscricoes()
    {
        return $this->hasMany(Inscricao::class);
    }
}