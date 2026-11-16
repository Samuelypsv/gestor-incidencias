import { useCallback, useEffect, useState, type FormEvent } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useSesion } from '../api/auth';
import { api, enviar, ErrorApi } from '../api/client';
import { ESTADOS, PRIORIDADES, type Estado, type Ticket, type Usuario } from '../api/types';
import { claseBoton, claseInput } from '../components/Campo';
import { EtiquetaEstado, EtiquetaPrioridad } from '../components/Etiquetas';

const fechaHora = (iso: string) =>
    new Date(iso).toLocaleString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });

// Mismas reglas que EstadoTicket::puedeCambiarA() en el backend, para no ofrecer opciones imposibles
const siguientes: Record<Estado, Estado[]> = {
    abierto: ['abierto', 'en_progreso', 'resuelto'],
    en_progreso: ['abierto', 'en_progreso', 'resuelto'],
    resuelto: ['abierto', 'en_progreso', 'resuelto', 'cerrado'],
    cerrado: ['cerrado'],
};

export function TicketPage() {
    const { id } = useParams();
    const navegar = useNavigate();
    const { usuario } = useSesion();
    const [ticket, setTicket] = useState<Ticket | null>(null);
    const [tecnicos, setTecnicos] = useState<Usuario[]>([]);
    const [comentario, setComentario] = useState('');
    const [error, setError] = useState<string | null>(null);

    const cargar = useCallback(() => api<{ data: Ticket }>(`/tickets/${id}`).then((r) => setTicket(r.data)), [id]);

    useEffect(() => {
        cargar();
        api<{ data: Usuario[] }>('/tecnicos').then((r) => setTecnicos(r.data));
    }, [cargar]);

    async function actualizar(cambios: Record<string, unknown>) {
        setError(null);
        try {
            const r = await enviar<{ data: Ticket }>(`/tickets/${id}`, 'PATCH', cambios);
            setTicket(r.data);
        } catch (err) {
            if (err instanceof ErrorApi) setError(Object.values(err.errores)[0]?.[0] ?? err.message);
        }
    }

    async function comentar(e: FormEvent) {
        e.preventDefault();
        if (!comentario.trim()) return;
        try {
            await enviar(`/tickets/${id}/comentarios`, 'POST', { texto: comentario });
            setComentario('');
            cargar();
        } catch (err) {
            if (err instanceof ErrorApi) setError(err.errores.texto?.[0] ?? err.message);
        }
    }

    async function borrar() {
        if (!window.confirm('¿Seguro que quieres borrar este ticket?')) return;
        await enviar(`/tickets/${id}`, 'DELETE');
        navegar('/tickets');
    }

    if (!ticket) return <p className="text-slate-500">Cargando…</p>;

    const cerrado = ticket.estado === 'cerrado';

    return (
        <div className="grid gap-6 lg:grid-cols-3">
            <div className="space-y-4 lg:col-span-2">
                <Link to="/tickets" className="text-sm text-slate-500 hover:underline">← Volver a tickets</Link>
                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex flex-wrap items-center gap-2">
                        <span className="text-slate-400">#{ticket.id}</span>
                        <EtiquetaEstado estado={ticket.estado} />
                        <EtiquetaPrioridad prioridad={ticket.prioridad} />
                    </div>
                    <h1 className="text-xl font-semibold">{ticket.titulo}</h1>
                    <p className="mt-3 whitespace-pre-line text-slate-700">{ticket.descripcion}</p>
                    <p className="mt-4 text-xs text-slate-400">
                        Creado por {ticket.autor?.nombre} el {fechaHora(ticket.creado)}
                        {ticket.resuelto && ` · Resuelto el ${fechaHora(ticket.resuelto)}`}
                    </p>
                </div>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="mb-4 font-medium">Comentarios ({ticket.comentarios?.length ?? 0})</h2>
                    <ul className="space-y-4">
                        {ticket.comentarios?.map((c) => (
                            <li key={c.id} className="border-l-2 border-indigo-200 pl-3">
                                <p className="text-sm text-slate-500"><strong className="text-slate-700">{c.autor.nombre}</strong> · {fechaHora(c.creado)}</p>
                                <p className="mt-1 whitespace-pre-line">{c.texto}</p>
                            </li>
                        ))}
                    </ul>
                    {!cerrado && (
                        <form onSubmit={comentar} className="mt-4 space-y-2">
                            <textarea className={claseInput} rows={3} placeholder="Escribe un comentario…" value={comentario} onChange={(e) => setComentario(e.target.value)} />
                            <button className={claseBoton}>Comentar</button>
                        </form>
                    )}
                </section>
            </div>

            <aside className="space-y-4">
                <div className="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:mt-9">
                    <p className="text-sm"><span className="text-slate-500">Cliente:</span> <strong>{ticket.cliente?.nombre}</strong></p>
                    <label className="block text-sm">
                        <span className="text-slate-500">Estado</span>
                        <select className={claseInput} value={ticket.estado} disabled={cerrado} onChange={(e) => actualizar({ estado: e.target.value })}>
                            {siguientes[ticket.estado].map((e) => <option key={e} value={e}>{ESTADOS[e]}</option>)}
                        </select>
                    </label>
                    <label className="block text-sm">
                        <span className="text-slate-500">Prioridad</span>
                        <select className={claseInput} value={ticket.prioridad} disabled={cerrado} onChange={(e) => actualizar({ prioridad: e.target.value })}>
                            {Object.entries(PRIORIDADES).map(([v, t]) => <option key={v} value={v}>{t}</option>)}
                        </select>
                    </label>
                    <label className="block text-sm">
                        <span className="text-slate-500">Técnico asignado</span>
                        <select className={claseInput} value={ticket.tecnico?.id ?? ''} disabled={cerrado}
                            onChange={(e) => actualizar({ tecnico_id: e.target.value ? Number(e.target.value) : null })}>
                            <option value="">Sin asignar</option>
                            {tecnicos.map((t) => <option key={t.id} value={t.id}>{t.nombre}</option>)}
                        </select>
                    </label>
                    {error && <p className="text-sm text-red-600">{error}</p>}
                    {usuario?.rol === 'admin' && (
                        <button onClick={borrar} className="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">Borrar ticket</button>
                    )}
                </div>
            </aside>
        </div>
    );
}
