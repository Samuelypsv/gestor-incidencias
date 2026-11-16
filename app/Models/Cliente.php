<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'cif', 'email', 'telefono'])]
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        return $query->when($texto, fn (Builder $q) => $q
            ->where('nombre', 'like', "%{$texto}%")
            ->orWhere('cif', 'like', "%{$texto}%"));
    }
}
