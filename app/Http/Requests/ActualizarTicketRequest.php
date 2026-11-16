<?php

namespace App\Http\Requests;

use App\Enums\EstadoTicket;
use App\Enums\Prioridad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActualizarTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'string', 'min:5', 'max:150'],
            'descripcion' => ['sometimes', 'string', 'min:10'],
            'prioridad' => ['sometimes', Rule::enum(Prioridad::class)],
            'estado' => ['sometimes', Rule::enum(EstadoTicket::class)],
            'cliente_id' => ['sometimes', 'integer', 'exists:clientes,id'],
            'tecnico_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Reglas de negocio que dependen del estado actual del ticket.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $ticket = $this->route('ticket');

                if ($ticket->estado === EstadoTicket::Cerrado) {
                    $validator->errors()->add('estado', 'Un ticket cerrado no se puede modificar.');

                    return;
                }

                $nuevo = EstadoTicket::tryFrom((string) $this->input('estado'));

                if ($nuevo && ! $ticket->estado->puedeCambiarA($nuevo)) {
                    $validator->errors()->add(
                        'estado',
                        "No se puede pasar de «{$ticket->estado->etiqueta()}» a «{$nuevo->etiqueta()}»."
                    );
                }
            },
        ];
    }
}
