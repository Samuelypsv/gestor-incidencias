<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarClienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('cif')) {
            $this->merge(['cif' => strtoupper(trim((string) $this->input('cif')))]);
        }
    }

    public function rules(): array
    {
        $cliente = $this->route('cliente');
        $requerido = $cliente ? 'sometimes' : 'required';

        return [
            'nombre' => [$requerido, 'string', 'max:120'],
            'cif' => [$requerido, 'string', 'regex:/^[A-Z][0-9]{7}[0-9A-Z]$/', Rule::unique('clientes', 'cif')->ignore($cliente)],
            'email' => ['nullable', 'email', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'cif.regex' => 'El CIF debe tener una letra, 7 números y un dígito o letra de control (ej. B37123456).',
        ];
    }
}
