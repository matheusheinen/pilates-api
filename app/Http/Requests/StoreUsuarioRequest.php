<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'senha' => 'required|string|min:8',
            'celular' => 'required|string|min:10|max:20',
            'cpf' => ['required', 'string', 'digits:11', 'unique:usuarios'],
            'data_nascimento' => 'nullable|date',
            'profissao' => 'nullable|string|max:255',
            'lateralidade' => ['nullable', 'string', Rule::in(['destro', 'canhoto', 'ambidestro'])],
            'genero' => ['nullable', 'string', Rule::in(['feminino', 'masculino', 'outro'])],
        ];
    }

    /**
     * Opcional: Mensagens personalizadas para ficar mais amigável
     */
    public function messages(): array
    {
        return [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            'cpf.digits' => 'O CPF deve conter apenas 11 números.',
            'email.unique' => 'Este e-mail já está em uso.',
            'lateralidade.in' => 'Selecione uma lateralidade válida.',
        ];
    }
}
