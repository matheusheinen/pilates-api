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
            // Ajuste: Garante min:8 no cadastro também
            'senha' => 'required|string|min:8',
            'tipo' => ['nullable', Rule::in(['aluno', 'admin'])],
            'genero' => ['nullable', 'string', Rule::in(['masculino', 'feminino', 'outro'])],
            'data_nascimento' => 'nullable|date',
            'profissao' => 'nullable|string|max:255',
            'cpf' => ['nullable', 'string', 'digits:11'], // Confirma que recebe apenas números
            'celular' => 'nullable|string|max:20',
            'lateralidade' => ['nullable', 'string', Rule::in(['destro', 'canhoto'])],
        ];
    }
}
