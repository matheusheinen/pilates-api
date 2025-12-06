<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Aula;

class UpdateAulaRequest extends FormRequest
{

    public function authorize(): bool
    {
        $aula = $this->route('aula');

        $usuario = $this->user();

        return $usuario->tipo === 'admin' || $usuario->id === $aula->usuario_id;
    }

    public function rules(): array
    {
        return [
            'data_aula' => 'sometimes|date|after_or_equal:today',
            'horario' => 'sometimes|date_format:H:i',
            'horario_fim' => 'sometimes|date_format:H:i|after:horario',
            'status' => ['sometimes', 'string', Rule::in(['agendada', 'realizada', 'cancelada', 'reagendada'])],

            'observacoes' => 'nullable|string',
        ];
    }
}
