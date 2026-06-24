<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProdutoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:255', Rule::unique('produtos', 'nome')],
            'preco_venda' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
