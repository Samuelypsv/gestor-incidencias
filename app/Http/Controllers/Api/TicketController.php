<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarTicketRequest;
use App\Http\Requests\GuardarTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $porPagina = min((int) $request->integer('por_pagina', 10), 50) ?: 10;

        $tickets = Ticket::query()
            ->with(['cliente', 'tecnico'])
            ->withCount('comentarios')
            ->filtrar($request->only(['estado', 'prioridad', 'cliente_id', 'tecnico_id', 'buscar']))
            ->latest()
            ->paginate($porPagina)
            ->withQueryString();

        return TicketResource::collection($tickets);
    }

    public function store(GuardarTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::create([
            ...$request->validated(),
            'estado' => EstadoTicket::Abierto,
            'autor_id' => $request->user()->id,
        ]);

        return (new TicketResource($ticket->load(['cliente', 'tecnico', 'autor'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket->load(['cliente', 'tecnico', 'autor', 'comentarios.autor']));
    }

    public function update(ActualizarTicketRequest $request, Ticket $ticket): TicketResource
    {
        $datos = $request->validated();

        if (isset($datos['estado'])) {
            $ticket->cambiarEstado(EstadoTicket::from($datos['estado']));
            unset($datos['estado']);
        }

        $ticket->fill($datos)->save();

        return new TicketResource($ticket->load(['cliente', 'tecnico', 'autor', 'comentarios.autor']));
    }

    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->esAdmin(), 403, 'Solo un administrador puede borrar tickets.');

        $ticket->delete();

        return response()->json(status: 204);
    }
}
