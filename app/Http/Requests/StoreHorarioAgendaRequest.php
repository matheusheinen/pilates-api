<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHorarioAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuste conforme sua auth (ex: apenas admins)
        return true;
    }

    public function rules(): array
    {
        return [
            'dia_semana'      => 'required|integer|between:1,7',
            'horario_inicio'  => 'required|date_format:H:i',
            'duracao_minutos' => 'required|integer|min:10',
            'vagas_totais'    => 'required|integer|min:1',
        ];
    }
}
