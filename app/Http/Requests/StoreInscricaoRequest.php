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
            // Requisito 1: Aluno
            'usuario_id' => 'required|exists:usuarios,id',
            // Requisito 2: Plano
            'plano_id' => 'required|exists:planos,id',
            // Requisito 3: Horários (deve ser um array com no mínimo 1 ID)
            'horarios_agenda_ids' => 'required|array|min:1',
            // Garante que cada ID no array existe na tabela horarios_agenda
            'horarios_agenda_ids.*' => 'exists:horarios_agenda,id',
            // Requisito 4: Data
            'data_inicio' => 'required|date'
        ];
    }
}
