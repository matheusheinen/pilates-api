<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscricaoRequest extends FormRequest
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
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'plano_id' => 'required|integer|exists:planos,id',
            'data_inicio' => 'required|date',
            // Regras para os horários fixos
            'horarios' => 'required|array',
            'horarios.*.dia_da_semana' => 'required|integer|between:1,7', // Garante que é um dia da semana válido (1-7)
            'horarios.*.horario' => 'required|date_format:H:i', // Garante o formato HH:MM
            'horarios.*.horario_fim' => 'required|date_format:H:i|after:horarios.*.horario', // Garante que o horário final é depois do inicial
        ];
    }
}
