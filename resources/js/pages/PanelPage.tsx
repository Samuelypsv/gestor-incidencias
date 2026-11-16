import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';
import { ESTADOS, PRIORIDADES, type Estadisticas, type Estado, type Prioridad } from '../api/types';

const Tarjeta = ({ titulo, valor, to }: { titulo: string; valor: string | number; to?: string }) => {
    const contenido = (
        <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow">
            <p className="text-sm text-slate-500">{titulo}</p>
            <p className="mt-1 text-3xl font-semibold">{valor}</p>
        </div>
    );
    return to ? <Link to={to}>{contenido}</Link> : contenido;
};

export function PanelPage() {
    const [datos, setDatos] = useState<Estadisticas | null>(null);

    useEffect(() => {
        api<Estadisticas>('/estadisticas').then(setDatos);
    }, []);

    if (!datos) return <p className="text-slate-500">Cargando…</p>;

    const maxPrioridad = Math.max(1, ...Object.values(datos.pendientes_por_prioridad));

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-semibold">Panel</h1>

            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                {(Object.keys(ESTADOS) as Estado[]).map((e) => (
                    <Tarjeta key={e} titulo={ESTADOS[e]} valor={datos.por_estado[e]} to={`/tickets?estado=${e}`} />
                ))}
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <Tarjeta titulo="Pendientes sin asignar" valor={datos.sin_asignar} to="/tickets?tecnico_id=sin_asignar" />
                <Tarjeta
                    titulo="Tiempo medio de resolución"
                    valor={datos.horas_medias_resolucion === null ? '—' : `${datos.horas_medias_resolucion} h`}
                />
                <Tarjeta titulo="Total de tickets" valor={datos.total} to="/tickets" />
            </div>

            <section className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 className="mb-3 font-medium">Pendientes por prioridad</h2>
                <div className="space-y-2">
                    {(Object.keys(PRIORIDADES) as Prioridad[]).map((p) => (
                        <div key={p} className="flex items-center gap-3 text-sm">
                            <span className="w-20 text-slate-600">{PRIORIDADES[p]}</span>
                            <div className="h-3 flex-1 rounded bg-slate-100">
                                <div
                                    className={`h-3 rounded ${p === 'urgente' ? 'bg-red-500' : 'bg-indigo-500'}`}
                                    style={{ width: `${(datos.pendientes_por_prioridad[p] / maxPrioridad) * 100}%` }}
                                />
                            </div>
                            <span className="w-6 text-right font-medium">{datos.pendientes_por_prioridad[p]}</span>
                        </div>
                    ))}
                </div>
            </section>
        </div>
    );
}
