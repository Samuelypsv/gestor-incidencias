<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoTicket;
use App\Enums\Prioridad;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class EstadisticasController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $porEstado = Ticket::query()->toBase()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $porPrioridad = Ticket::query()->toBase()
            ->whereIn('estado', [EstadoTicket::Abierto->value, EstadoTicket::EnProgreso->value])
            ->selectRaw('prioridad, count(*) as total')
            ->groupBy('prioridad')
            ->pluck('total', 'prioridad');

        // Tiempo medio de resolución (en horas) de los tickets resueltos o cerrados.
        // Se calcula en PHP para que funcione igual en SQLite, MySQL y PostgreSQL.
        $resueltos = Ticket::whereNotNull('resuelto_at')->get(['created_at', 'resuelto_at']);
        $horasMedias = $resueltos->isEmpty()
            ? null
            : round($resueltos->avg(fn (Ticket $t) => $t->created_at->diffInMinutes($t->resuelto_at)) / 60, 1);

        return response()->json([
            'por_estado' => collect(EstadoTicket::cases())
                ->mapWithKeys(fn (EstadoTicket $e) => [$e->value => (int) ($porEstado[$e->value] ?? 0)]),
            'pendientes_por_prioridad' => collect(Prioridad::cases())
                ->mapWithKeys(fn (Prioridad $p) => [$p->value => (int) ($porPrioridad[$p->value] ?? 0)]),
            'sin_asignar' => Ticket::whereNull('tecnico_id')
                ->whereIn('estado', [EstadoTicket::Abierto, EstadoTicket::EnProgreso])
                ->count(),
            'horas_medias_resolucion' => $horasMedias,
            'total' => Ticket::count(),
        ]);
    }
}
