<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInscricaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->tipo === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plano_id' => 'sometimes|integer|exists:planos,id',
            'data_inicio' => 'sometimes|date',
            'ativo' => 'sometimes|boolean',

            // Validação para a atualização dos horários
            'horarios' => 'sometimes|array',
            'horarios.*.dia_da_semana' => 'required|integer|between:1,7',
            'horarios.*.horario' => 'required|date_format:H:i',
            'horarios.*.horario_fim' => 'required|date_format:H:i|after:horarios.*.horario',
        ];
    }
}
