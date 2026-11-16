<?php

namespace Database\Seeders;

use App\Enums\EstadoTicket;
use App\Enums\Prioridad;
use App\Models\Cliente;
use App\Models\Comentario;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.test',
        ]);

        $tecnicos = collect([
            ['name' => 'Laura Martín', 'email' => 'laura@demo.test'],
            ['name' => 'Carlos Pérez', 'email' => 'carlos@demo.test'],
        ])->map(fn (array $datos) => User::factory()->create($datos));

        $clientes = Cliente::factory(6)->create();

        $ejemplos = [
            ['No se puede facturar desde el TPV', Prioridad::Urgente],
            ['Error al exportar el informe mensual a Excel', Prioridad::Alta],
            ['Alta de un nuevo usuario en el ERP', Prioridad::Baja],
            ['La aplicación móvil no sincroniza pedidos', Prioridad::Alta],
            ['Lentitud al abrir el módulo de almacén', Prioridad::Media],
            ['Cambiar el logotipo de las facturas', Prioridad::Baja],
            ['Copia de seguridad nocturna fallida', Prioridad::Urgente],
            ['Duda sobre el cierre contable de fin de año', Prioridad::Media],
        ];

        $estados = EstadoTicket::cases();

        foreach ($ejemplos as $i => [$titulo, $prioridad]) {
            $estado = $estados[$i % count($estados)];

            $ticket = Ticket::factory()->enEstado($estado)->create([
                'titulo' => $titulo,
                'prioridad' => $prioridad,
                'cliente_id' => $clientes->random()->id,
                'autor_id' => $admin->id,
                'tecnico_id' => $estado === EstadoTicket::Abierto ? null : $tecnicos->random()->id,
                'created_at' => now()->subDays(10 - $i)->subHours($i * 3),
            ]);

            Comentario::factory(rand(0, 3))->create([
                'ticket_id' => $ticket->id,
                'autor_id' => $tecnicos->random()->id,
            ]);
        }

        Ticket::factory(20)->recycle($clientes)->create([
            'autor_id' => $admin->id,
            'tecnico_id' => fn () => rand(0, 1) ? $tecnicos->random()->id : null,
        ]);
    }
}
