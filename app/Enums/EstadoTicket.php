<?php

namespace App\Enums;

enum EstadoTicket: string
{
    case Abierto = 'abierto';
    case EnProgreso = 'en_progreso';
    case Resuelto = 'resuelto';
    case Cerrado = 'cerrado';

    /**
     * Reglas del flujo de trabajo:
     * - Un ticket cerrado ya no se puede mover.
     * - Solo se puede cerrar un ticket que antes se ha resuelto.
     */
    public function puedeCambiarA(self $nuevo): bool
    {
        if ($this === $nuevo) {
            return true;
        }

        return match ($this) {
            self::Cerrado => false,
            self::Abierto, self::EnProgreso => $nuevo !== self::Cerrado,
            self::Resuelto => true,
        };
    }

    public function etiqueta(): string
    {
        return match ($this) {
            self::Abierto => 'Abierto',
            self::EnProgreso => 'En progreso',
            self::Resuelto => 'Resuelto',
            self::Cerrado => 'Cerrado',
        };
    }
}
