<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado->value,
            'prioridad' => $this->prioridad->value,
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'tecnico' => new UsuarioResource($this->whenLoaded('tecnico')),
            'autor' => new UsuarioResource($this->whenLoaded('autor')),
            'comentarios' => ComentarioResource::collection($this->whenLoaded('comentarios')),
            'comentarios_total' => $this->whenCounted('comentarios'),
            'creado' => $this->created_at?->toIso8601String(),
            'actualizado' => $this->updated_at?->toIso8601String(),
            'resuelto' => $this->resuelto_at?->toIso8601String(),
        ];
    }
}
