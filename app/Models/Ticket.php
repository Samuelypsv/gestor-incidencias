<?php

namespace App\Models;

use App\Enums\EstadoTicket;
use App\Enums\Prioridad;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['titulo', 'descripcion', 'estado', 'prioridad', 'cliente_id', 'tecnico_id', 'autor_id', 'resuelto_at'])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $attributes = [
        'estado' => 'abierto',
        'prioridad' => 'media',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoTicket::class,
            'prioridad' => Prioridad::class,
            'resuelto_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class)->oldest();
    }

    /**
     * Aplica los filtros que llegan por query string: ?estado=&prioridad=&cliente_id=&tecnico_id=&buscar=
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['estado'] ?? null, fn (Builder $q, $v) => $q->where('estado', $v))
            ->when($filtros['prioridad'] ?? null, fn (Builder $q, $v) => $q->where('prioridad', $v))
            ->when($filtros['cliente_id'] ?? null, fn (Builder $q, $v) => $q->where('cliente_id', $v))
            ->when($filtros['tecnico_id'] ?? null, fn (Builder $q, $v) => $v === 'sin_asignar'
                ? $q->whereNull('tecnico_id')
                : $q->where('tecnico_id', $v))
            ->when($filtros['buscar'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $w) => $w
                ->where('titulo', 'like', "%{$v}%")
                ->orWhere('descripcion', 'like', "%{$v}%")));
    }

    /**
     * Cambia el estado respetando el flujo y mantiene la fecha de resolución al día.
     */
    public function cambiarEstado(EstadoTicket $nuevo): void
    {
        if ($nuevo === EstadoTicket::Resuelto && $this->estado !== EstadoTicket::Resuelto) {
            $this->resuelto_at = now();
        }

        if (in_array($nuevo, [EstadoTicket::Abierto, EstadoTicket::EnProgreso], true)) {
            $this->resuelto_at = null;
        }

        $this->estado = $nuevo;
    }
}
