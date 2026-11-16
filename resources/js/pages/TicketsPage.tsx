import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { api } from '../api/client';
import { ESTADOS, PRIORIDADES, type Paginado, type Ticket, type Usuario } from '../api/types';
import { claseBoton, claseInput } from '../components/Campo';
import { EtiquetaEstado, EtiquetaPrioridad } from '../components/Etiquetas';

const fecha = (iso: string) => new Date(iso).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });

export function TicketsPage() {
    // Los filtros viven en la URL, así se pueden compartir o recargar sin perderlos
    const [params, setParams] = useSearchParams();
    const [pagina, setPagina] = useState<Paginado<Ticket> | null>(null);
    const [tecnicos, setTecnicos] = useState<Usuario[]>([]);
    const [buscar, setBuscar] = useState(params.get('buscar') ?? '');

    useEffect(() => {
        api<{ data: Usuario[] }>('/tecnicos').then((r) => setTecnicos(r.data));
    }, []);

    useEffect(() => {
        api<Paginado<Ticket>>(`/tickets?${params.toString()}`).then(setPagina);
    }, [params]);

    // Búsqueda con un pequeño retardo para no lanzar una petición por cada tecla
    useEffect(() => {
        const id = setTimeout(() => cambiar('buscar', buscar), 300);
        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [buscar]);

    function cambiar(clave: string, valor: string) {
        setParams((anterior) => {
            const nuevos = new URLSearchParams(anterior);
            if (valor) nuevos.set(clave, valor);
            else nuevos.delete(clave);
            if (clave !== 'page') nuevos.delete('page');
            return nuevos;
        });
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Tickets</h1>
                <Link to="/tickets/nuevo" className={claseBoton}>+ Nuevo ticket</Link>
            </div>

            <div className="grid gap-2 md:grid-cols-4">
                <input className={claseInput} placeholder="Buscar por título o descripción…" value={buscar} onChange={(e) => setBuscar(e.target.value)} />
                <select className={claseInput} value={params.get('estado') ?? ''} onChange={(e) => cambiar('estado', e.target.value)}>
                    <option value="">Todos los estados</option>
                    {Object.entries(ESTADOS).map(([v, t]) => <option key={v} value={v}>{t}</option>)}
                </select>
                <select className={claseInput} value={params.get('prioridad') ?? ''} onChange={(e) => cambiar('prioridad', e.target.value)}>
                    <option value="">Todas las prioridades</option>
                    {Object.entries(PRIORIDADES).map(([v, t]) => <option key={v} value={v}>{t}</option>)}
                </select>
                <select className={claseInput} value={params.get('tecnico_id') ?? ''} onChange={(e) => cambiar('tecnico_id', e.target.value)}>
                    <option value="">Todos los técnicos</option>
                    <option value="sin_asignar">Sin asignar</option>
                    {tecnicos.map((t) => <option key={t.id} value={t.id}>{t.nombre}</option>)}
                </select>
            </div>

            <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500">
                        <tr>
                            <th className="px-4 py-3">#</th>
                            <th className="px-4 py-3">Título</th>
                            <th className="px-4 py-3">Cliente</th>
                            <th className="px-4 py-3">Prioridad</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Técnico</th>
                            <th className="px-4 py-3">Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        {pagina?.data.map((t) => (
                            <tr key={t.id} className="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                                <td className="px-4 py-3 text-slate-400">{t.id}</td>
                                <td className="px-4 py-3">
                                    <Link to={`/tickets/${t.id}`} className="font-medium text-indigo-700 hover:underline">{t.titulo}</Link>
                                    {!!t.comentarios_total && <span className="ml-2 text-xs text-slate-400">💬 {t.comentarios_total}</span>}
                                </td>
                                <td className="px-4 py-3">{t.cliente?.nombre}</td>
                                <td className="px-4 py-3"><EtiquetaPrioridad prioridad={t.prioridad} /></td>
                                <td className="px-4 py-3"><EtiquetaEstado estado={t.estado} /></td>
                                <td className="px-4 py-3">{t.tecnico?.nombre ?? <span className="text-slate-400">Sin asignar</span>}</td>
                                <td className="px-4 py-3 text-slate-500">{fecha(t.creado)}</td>
                            </tr>
                        ))}
                        {pagina?.data.length === 0 && (
                            <tr><td colSpan={7} className="px-4 py-8 text-center text-slate-500">No hay tickets con estos filtros.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            {pagina && pagina.meta.last_page > 1 && (
                <div className="flex items-center justify-center gap-3 text-sm">
                    <button className="rounded border px-3 py-1 disabled:opacity-40" disabled={pagina.meta.current_page === 1}
                        onClick={() => cambiar('page', String(pagina.meta.current_page - 1))}>← Anterior</button>
                    <span>Página {pagina.meta.current_page} de {pagina.meta.last_page} · {pagina.meta.total} tickets</span>
                    <button className="rounded border px-3 py-1 disabled:opacity-40" disabled={pagina.meta.current_page === pagina.meta.last_page}
                        onClick={() => cambiar('page', String(pagina.meta.current_page + 1))}>Siguiente →</button>
                </div>
            )}
        </div>
    );
}
