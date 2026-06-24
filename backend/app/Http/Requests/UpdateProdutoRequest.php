<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdutoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nome' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
                Rule::unique('produtos', 'nome')->ignore($this->route('produto')),
            ],
            'preco_venda' => ['sometimes', 'numeric', 'gt:0'],
        ];
    }
}
