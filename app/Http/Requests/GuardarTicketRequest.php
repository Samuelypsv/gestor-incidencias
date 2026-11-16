<?php

namespace App\Http\Requests;

use App\Enums\Prioridad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:5', 'max:150'],
            'descripcion' => ['required', 'string', 'min:10'],
            'prioridad' => ['required', Rule::enum(Prioridad::class)],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tecnico_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
