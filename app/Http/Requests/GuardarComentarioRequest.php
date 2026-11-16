<?php

namespace App\Http\Requests;

use App\Enums\EstadoTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarComentarioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'texto' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->route('ticket')->estado === EstadoTicket::Cerrado) {
                    $validator->errors()->add('texto', 'No se pueden añadir comentarios a un ticket cerrado.');
                }
            },
        ];
    }
}
