<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // No update, ignoramos o ID atual na verificação de unique para não dar erro se o nome não mudar
            'nome' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('planos')->ignore($this->plano)
            ],
            'numero_aulas' => 'sometimes|integer|min:1',
            'preco' => 'sometimes|numeric|min:0',
        ];
    }
}
