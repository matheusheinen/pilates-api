<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvaliacaoPosturalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'altura' => 'nullable|numeric',
            'peso' => 'nullable|numeric',
            'queixa_principal' => 'nullable|string',
            'diagnostico_clinico' => 'nullable|string',
            'data_avaliacao' => 'required|date',

            'anterior_cabeca' => 'required|string',
            'anterior_ombros_altura' => 'required|string',
            'anterior_maos_altura' => 'required|string',
            'anterior_tronco_rotacao' => 'required|string',
            'anterior_angulo_tales' => 'required|string',
            'anterior_cicatriz_umbilical' => 'required|string',
            'anterior_iliacas_altura' => 'required|string',
            'anterior_joelhos' => 'required|string',
            'anterior_tornozelos' => 'required|string',
            'anterior_pes' => 'required|string',

            'posterior_escapulas_altura' => 'required|string',
            'posterior_escapula_alada' => 'required|string',
            'posterior_gibosidade_toracica' => 'required|string',
            'posterior_pregas_gluteas' => 'required|string',
            'posterior_pregas_popliteas' => 'required|string',
            'posterior_lombar_concavidade' => 'required|string',
            'posterior_toracica_concavidade' => 'required|string',
            'posterior_cervical_concavidade' => 'required|string',

            'lateral_cabeca' => 'required|string',
            'lateral_cervical' => 'required|string',
            'lateral_ombro' => 'required|string',
            'lateral_membro_superior' => 'required|string',
            'lateral_toracica' => 'required|string',
            'lateral_tronco_rotacao' => 'required|string',
            'lateral_abdomen' => 'required|string',
            'lateral_lombar' => 'required|string',
            'lateral_pelve' => 'required|string',
            'lateral_quadril' => 'required|string',
            'lateral_joelho' => 'required|string',
            'anexo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'caminho_anexo' => 'nullable|string',

            'observacoes' => 'nullable|string',
        ];
    }
}
