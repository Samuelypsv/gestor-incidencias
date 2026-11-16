<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComentarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'texto' => $this->texto,
            'autor' => new UsuarioResource($this->whenLoaded('autor')),
            'creado' => $this->created_at?->toIso8601String(),
        ];
    }
}
