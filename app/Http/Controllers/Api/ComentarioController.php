<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarComentarioRequest;
use App\Http\Resources\ComentarioResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class ComentarioController extends Controller
{
    public function store(GuardarComentarioRequest $request, Ticket $ticket): JsonResponse
    {
        $comentario = $ticket->comentarios()->create([
            'texto' => $request->validated('texto'),
            'autor_id' => $request->user()->id,
        ]);

        // Al comentar un ticket abierto, pasa a "en progreso" automáticamente
        if ($ticket->estado === EstadoTicket::Abierto) {
            $ticket->cambiarEstado(EstadoTicket::EnProgreso);
            $ticket->save();
        }

        return (new ComentarioResource($comentario->load('autor')))
            ->response()
            ->setStatusCode(201);
    }
}
