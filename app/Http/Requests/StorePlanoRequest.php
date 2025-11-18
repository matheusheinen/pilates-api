<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Defina como true se qualquer usuário logado (ou admin) puder criar planos
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255|unique:planos,nome',
            'numero_aulas' => 'required|integer|min:1',
            'preco' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'nome.unique' => 'Já existe um plano com este nome.',
            'numero_aulas.integer' => 'O número de aulas deve ser um número inteiro.',
        ];
    }
}
