<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import correto

class UpdateUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'nullable|string|max:255', // Pode ser 'required' se o nome for obrigatório na edição
            'email' => [
                'string',
                'email',
                'max:255',
                // Garante que o email é único, exceto para o próprio usuário que está sendo editado
                Rule::unique('usuarios')->ignore($this->route('usuario')),
            ],
            // CORREÇÃO 2: Adicione a regra da senha (opcional, mas se vier, min 8)
            'senha' => ['nullable', 'string', 'min:8'],

            'genero' => ['nullable', 'string', Rule::in(['masculino', 'feminino', 'outro'])],
            'data_nascimento' => 'nullable|date',
            'profissao' => 'nullable|string|max:255',
            'celular' => 'required|string|min:10|max:20',
            'lateralidade' => ['nullable', 'string', Rule::in(['destro', 'canhoto'])],
        ];
    }
}
