<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvaliacaoPosturalRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
        'altura' => 'nullable|numeric',
        'peso' => 'nullable|numeric',
        'queixa_principal' => 'nullable|string',
        'diagnostico_clinico' => 'nullable|string',
        'data_avaliacao' => 'nullable|date',
        'anterior_cabeca' => 'nullable|string',
        'anterior_ombros_altura' => 'nullable|string',
        'anterior_maos_altura' => 'nullable|string',
        'anterior_tronco_rotacao' => 'nullable|string',
        'anterior_angulo_tales' => 'nullable|string',
        'anterior_cicatriz_umbilical' => 'nullable|string',
        'anterior_iliacas_altura' => 'nullable|string',
        'anterior_joelhos' => 'nullable|string',
        'anterior_tornozelos' => 'nullable|string',
        'anterior_pes' => 'nullable|string',
        'posterior_escapulas_altura' => 'nullable|string',
        'posterior_escapula_alada' => 'nullable|string',
        'posterior_gibosidade_toracica' => 'nullable|string',
        'posterior_pregas_gluteas' => 'nullable|string',
        'posterior_pregas_popliteas' => 'nullable|string',
        'posterior_lombar_concavidade' => 'nullable|string',
        'posterior_toracica_concavidade' => 'nullable|string',
        'posterior_cervical_concavidade' => 'nullable|string',
        'lateral_cabeca' => 'nullable|string',
        'lateral_cervical' => 'nullable|string',
        'lateral_ombro' => 'nullable|string',
        'lateral_membro_superior' => 'nullable|string',
        'lateral_toracica' => 'nullable|string',
        'lateral_tronco_rotacao' => 'nullable|string',
        'lateral_abdomen' => 'nullable|string',
        'lateral_lombar' => 'nullable|string',
        'lateral_pelve' => 'nullable|string',
        'lateral_quadril' => 'nullable|string',
        'lateral_joelho' => 'nullable|string',
        'anexo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        'caminho_anexo' => 'nullable|string',
        'observacoes' => 'nullable|string',
    ];
    }
}
