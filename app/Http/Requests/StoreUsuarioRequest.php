<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Adicione este import

class StoreUsuarioRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'senha' => 'required|string|min:8',
            // Regras do perfil do aluno (opcionais)
            'tipo' => ['nullable', Rule::in(['aluno', 'admin'])],
            'genero' => ['nullable', 'string', Rule::in(['masculino', 'feminino', 'outro'])],
            'data_nascimento' => 'nullable|date',
            'profissao' => 'nullable|string|max:255',
            'cpf' => ['nullable', 'string', 'digits:11'],
            'celular' => 'nullable|string|max:20',
            'altura' => 'nullable|numeric',
            'peso' => 'nullable|numeric',
            'queixa_principal' => 'nullable|string',
            'lateralidade' => ['nullable', 'string', Rule::in(['destro', 'canhoto'])],
        ];
    }
}
