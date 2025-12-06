<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvaliacaoPostural extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes_posturais';

    protected $fillable = [
        'usuario_id',
        'altura',
        'peso',
        'queixa_principal',
        'diagnostico_clinico',
        'data_avaliacao',
        'anterior_cabeca', 'anterior_ombros_altura', 'anterior_maos_altura',
        'anterior_tronco_rotacao', 'anterior_angulo_tales', 'anterior_cicatriz_umbilical',
        'anterior_iliacas_altura', 'anterior_joelhos', 'anterior_tornozelos', 'anterior_pes',
        'posterior_escapulas_altura', 'posterior_escapula_alada', 'posterior_gibosidade_toracica',
        'posterior_pregas_gluteas', 'posterior_pregas_popliteas', 'posterior_lombar_concavidade',
        'posterior_toracica_concavidade', 'posterior_cervical_concavidade',
        'lateral_cabeca', 'lateral_cervical', 'lateral_ombro', 'lateral_membro_superior',
        'lateral_toracica', 'lateral_tronco_rotacao', 'lateral_abdomen', 'lateral_lombar',
        'lateral_pelve', 'lateral_quadril', 'lateral_joelho',
        'caminho_anexo',
        'observacoes'
    ];


    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
