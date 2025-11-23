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
        // Apenas os campos que podem ser editados no frontend, removendo data_inicio
        return [
            'usuario_id' => ['required', 'exists:usuarios,id'], // Mantido como required, mas o frontend o envia.
            'plano_id' => ['required', 'exists:planos,id'],
            'status' => ['required', 'in:ativa,inativa,trancada,cancelada'],
            'horarios_agenda_ids' => ['required', 'array', 'min:1'],
            'horarios_agenda_ids.*' => ['exists:horarios_agenda,id'],
        ];
    }
}
