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

        $clientes = collect([
            ['Talleres Tormes SL', 'B37100001'],
            ['Panadería La Espiga SL', 'B37100002'],
            ['Clínica Dental Plaza Mayor', 'B37100003'],
            ['Transportes Charros SA', 'A37100004'],
            ['Librería Unamuno', 'B37100005'],
            ['Ferretería El Puente SL', 'B37100006'],
        ])->map(fn (array $c, int $i) => Cliente::create([
            'nombre' => $c[0],
            'cif' => $c[1],
            'email' => 'contacto'.($i + 1).'@cliente.test',
            'telefono' => '92300000'.($i + 1),
        ]));

        // Tickets principales: [título, prioridad, descripción, comentarios]
        $ejemplos = [
            ['No se puede facturar desde el TPV', Prioridad::Urgente,
                'Desde esta mañana el TPV muestra "Error de conexión" al cerrar una venta y no emite la factura. Tienen clientes esperando en caja.',
                []],
            ['Error al exportar el informe mensual a Excel', Prioridad::Alta,
                'Al pulsar "Exportar" en el informe de ventas de octubre se descarga un archivo vacío. Con los meses anteriores funciona bien.',
                ['He reproducido el fallo: ocurre cuando hay ventas con descuento del 100 %.']],
            ['Copia de seguridad nocturna fallida', Prioridad::Urgente,
                'El cliente ha recibido el aviso de que la copia de seguridad de anoche no se completó. Piden confirmar que los datos están a salvo.',
                ['El disco de copias estaba al 98 %. Libero espacio y relanzo la copia a mano.',
                 'Copia completada y verificada. Dejo programada una alerta cuando el disco pase del 80 %.']],
            ['Alta de un nuevo usuario en el ERP', Prioridad::Baja,
                'Se incorpora una persona nueva a administración el lunes y necesita acceso al módulo de contabilidad.',
                ['Usuario creado y credenciales enviadas al responsable.']],
            ['La aplicación móvil no sincroniza pedidos', Prioridad::Alta,
                'Los comerciales crean pedidos en la app pero no aparecen en la central hasta el día siguiente.',
                []],
            ['Lentitud al abrir el módulo de almacén', Prioridad::Media,
                'El listado de artículos tarda más de 20 segundos en cargar desde la última actualización.',
                ['Faltaba un índice en la tabla de movimientos. Pendiente de aplicarlo fuera de horario.']],
            ['Cambiar el logotipo de las facturas', Prioridad::Baja,
                'Han renovado la imagen de la empresa y quieren el logotipo nuevo en facturas y albaranes.',
                ['Logotipo actualizado. El cliente ha dado el visto bueno.']],
            ['Duda sobre el cierre contable de fin de año', Prioridad::Media,
                'Quieren saber qué pasos seguir en el programa para hacer el cierre del ejercicio sin bloquear la facturación de enero.',
                ['Les he enviado la guía paso a paso y quedamos en revisarlo juntos por videollamada.']],
        ];

        $estados = EstadoTicket::cases();

        foreach ($ejemplos as $i => [$titulo, $prioridad, $descripcion, $comentarios]) {
            // Los tickets sin comentarios se quedan abiertos y sin asignar
            $estado = $comentarios === [] ? EstadoTicket::Abierto : $estados[1 + $i % 3];
            $creado = now()->subDays(8 - $i)->subHours($i * 3);

            $ticket = Ticket::factory()->enEstado($estado)->create([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'prioridad' => $prioridad,
                'cliente_id' => $clientes[$i % $clientes->count()]->id,
                'autor_id' => $admin->id,
                'tecnico_id' => $estado === EstadoTicket::Abierto ? null : $tecnicos[$i % 2]->id,
                'created_at' => $creado,
                'resuelto_at' => in_array($estado, [EstadoTicket::Resuelto, EstadoTicket::Cerrado], true)
                    ? $creado->copy()->addHours(5 + $i * 2)
                    : null,
            ]);

            foreach ($comentarios as $n => $texto) {
                Comentario::create([
                    'ticket_id' => $ticket->id,
                    'autor_id' => $tecnicos[$i % 2]->id,
                    'texto' => $texto,
                    'created_at' => $creado->copy()->addHours($n + 1),
                ]);
            }
        }

        // Tickets antiguos para que haya varias páginas en el listado
        $antiguos = [
            'La impresora de tickets imprime en blanco', 'Restablecer contraseña de un usuario', 'Error al enviar facturas por email',
            'Actualizar el tipo de IVA de varios artículos', 'El lector de códigos de barras no responde', 'Añadir un nuevo almacén',
            'Descuadre en el arqueo de caja', 'Instalar el programa en un equipo nuevo', 'Duplicados en la ficha de clientes',
            'El informe de stock no coincide con el inventario', 'Configurar la firma en los presupuestos', 'Problema con el certificado digital',
            'Importar tarifas desde una hoja de cálculo', 'La web no muestra los precios actualizados',
        ];

        foreach ($antiguos as $i => $titulo) {
            $estado = $i % 4 === 0 ? EstadoTicket::Resuelto : EstadoTicket::Cerrado;
            $creado = now()->subDays(15 + $i * 2);

            Ticket::factory()->enEstado($estado)->create([
                'titulo' => $titulo,
                'descripcion' => 'Incidencia comunicada por teléfono por el cliente. Se resolvió en remoto.',
                'prioridad' => Prioridad::cases()[$i % 3],
                'cliente_id' => $clientes[$i % $clientes->count()]->id,
                'autor_id' => $admin->id,
                'tecnico_id' => $tecnicos[$i % 2]->id,
                'created_at' => $creado,
                'resuelto_at' => $creado->copy()->addHours(3 + $i),
            ]);
        }
    }
}
