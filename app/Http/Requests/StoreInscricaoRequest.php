<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se a rota for protegida por sanctum (admin logado), TRUE é o padrão
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => 'required|exists:usuarios,id',
            'plano_id' => 'required|exists:planos,id',
            'horarios_agenda_ids' => 'required|array|min:1',
            'horarios_agenda_ids.*' => 'exists:horarios_agenda,id',
            'data_inicio' => 'required|date'
        ];
    }
}
