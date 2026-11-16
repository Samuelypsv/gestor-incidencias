<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClienteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $clientes = Cliente::query()
            ->buscar($request->string('buscar')->toString() ?: null)
            ->withCount([
                'tickets',
                'tickets as tickets_abiertos_count' => fn (Builder $q) => $q
                    ->whereIn('estado', [EstadoTicket::Abierto, EstadoTicket::EnProgreso]),
            ])
            ->orderBy('nombre')
            ->get();

        return ClienteResource::collection($clientes);
    }

    public function store(GuardarClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated());

        return (new ClienteResource($cliente))->response()->setStatusCode(201);
    }

    public function show(Cliente $cliente): ClienteResource
    {
        return new ClienteResource($cliente->loadCount('tickets'));
    }

    public function update(GuardarClienteRequest $request, Cliente $cliente): ClienteResource
    {
        $cliente->update($request->validated());

        return new ClienteResource($cliente);
    }

    public function destroy(Request $request, Cliente $cliente): JsonResponse
    {
        abort_unless($request->user()->esAdmin(), 403, 'Solo un administrador puede borrar clientes.');

        if ($cliente->tickets()->exists()) {
            return response()->json([
                'message' => 'No se puede borrar un cliente que tiene tickets.',
            ], 409);
        }

        $cliente->delete();

        return response()->json(status: 204);
    }
}
