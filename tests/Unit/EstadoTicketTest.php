<?php

namespace Tests\Unit;

use App\Enums\EstadoTicket;
use PHPUnit\Framework\TestCase;

class EstadoTicketTest extends TestCase
{
    public function test_un_ticket_cerrado_no_puede_cambiar_de_estado(): void
    {
        foreach (EstadoTicket::cases() as $estado) {
            if ($estado !== EstadoTicket::Cerrado) {
                $this->assertFalse(EstadoTicket::Cerrado->puedeCambiarA($estado));
            }
        }
    }

    public function test_solo_se_cierra_desde_resuelto(): void
    {
        $this->assertTrue(EstadoTicket::Resuelto->puedeCambiarA(EstadoTicket::Cerrado));
        $this->assertFalse(EstadoTicket::Abierto->puedeCambiarA(EstadoTicket::Cerrado));
        $this->assertFalse(EstadoTicket::EnProgreso->puedeCambiarA(EstadoTicket::Cerrado));
    }

    public function test_un_ticket_resuelto_se_puede_reabrir(): void
    {
        $this->assertTrue(EstadoTicket::Resuelto->puedeCambiarA(EstadoTicket::Abierto));
    }
}
