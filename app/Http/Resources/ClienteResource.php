<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'cif' => $this->cif,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'tickets_abiertos' => $this->whenHas('tickets_abiertos_count', fn () => $this->tickets_abiertos_count),
            'tickets_total' => $this->whenCounted('tickets'),
        ];
    }
}
