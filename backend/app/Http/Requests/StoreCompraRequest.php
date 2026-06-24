<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fornecedor' => ['required', 'string', 'max:255'],
            'produtos' => ['required', 'array', 'min:1'],
            'produtos.*.id' => ['required', 'integer', 'exists:produtos,id'],
            'produtos.*.quantidade' => ['required', 'integer', 'min:1'],
            'produtos.*.preco_unitario' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function () use ($validator): void {
            $ids = array_column($this->input('produtos', []), 'id');

            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('produtos', 'Nao e permitido informar o mesmo produto mais de uma vez.');
            }
        });
    }
}
