<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Aula;

class UpdateAulaRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        // Pega a aula que está a ser pedida a partir da rota (ex: /api/aulas/15)
        $aula = $this->route('aula');

        // Pega o usuário que está logado
        $usuario = $this->user();

        // Permite a ação se o usuário for um admin OU se for um aluno que é o "dono" da aula.
        return $usuario->tipo === 'admin' || $usuario->id === $aula->usuario_id;
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'sometimes' significa que o campo só é validado se for enviado no pedido.
            // Regras para reagendamento
            'data_aula' => 'sometimes|date|after_or_equal:today',
            'horario' => 'sometimes|date_format:H:i',
            'horario_fim' => 'sometimes|date_format:H:i|after:horario',

            // Regra para a professora atualizar o status da aula
            'status' => ['sometimes', 'string', Rule::in(['agendada', 'realizada', 'cancelada', 'reagendada'])],
            
            'observacoes' => 'nullable|string',
        ];
    }
}