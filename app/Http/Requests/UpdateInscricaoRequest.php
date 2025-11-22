<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class UpdateInscricaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Acesso restrito a administradores
        return $this->user() && $this->user()->tipo === 'admin';
    }

    public function rules(): array
    {
        return [
            // Campos que podem ser atualizados
            'plano_id' => 'sometimes|integer|exists:planos,id',

            // CAMPO CHAVE: Status do Contrato
            'status' => ['required', 'string', Rule::in(['ativa', 'inativa', 'trancada', 'cancelada'])], //

            // ADICIONADO: Suporte para reescrever os horários fixos na edição
            'horarios_agenda_ids' => 'sometimes|array',
            'horarios_agenda_ids.*' => 'exists:horarios_agenda,id',

            // Não deve vir horários aqui, a menos que você queira reescrever a vaga

        ];
    }
}
