<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id'  => 'required|integer|exists:usuarios,id',
            'plano_id'    => 'required|integer|exists:planos,id',
            'data_inicio' => 'required|date',

            // Recebemos um array de IDs (ex: [1, 5])
            'horarios_agenda_ids'   => 'required|array',
            'horarios_agenda_ids.*' => 'integer|exists:horarios_agenda,id',
        ];
    }
}
