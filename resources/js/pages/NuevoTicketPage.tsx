import { useEffect, useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { api, enviar, ErrorApi } from '../api/client';
import { PRIORIDADES, type Cliente, type Ticket, type Usuario } from '../api/types';
import { Campo, claseBoton, claseInput } from '../components/Campo';

export function NuevoTicketPage() {
    const navegar = useNavigate();
    const [clientes, setClientes] = useState<Cliente[]>([]);
    const [tecnicos, setTecnicos] = useState<Usuario[]>([]);
    const [errores, setErrores] = useState<Record<string, string[]>>({});
    const [form, setForm] = useState({ titulo: '', descripcion: '', prioridad: 'media', cliente_id: '', tecnico_id: '' });

    useEffect(() => {
        api<{ data: Cliente[] }>('/clientes').then((r) => setClientes(r.data));
        api<{ data: Usuario[] }>('/tecnicos').then((r) => setTecnicos(r.data));
    }, []);

    const actualizar = (campo: keyof typeof form) => (e: { target: { value: string } }) =>
        setForm({ ...form, [campo]: e.target.value });

    async function guardar(e: FormEvent) {
        e.preventDefault();
        try {
            const r = await enviar<{ data: Ticket }>('/tickets', 'POST', {
                ...form,
                cliente_id: form.cliente_id ? Number(form.cliente_id) : null,
                tecnico_id: form.tecnico_id ? Number(form.tecnico_id) : null,
            });
            navegar(`/tickets/${r.data.id}`);
        } catch (err) {
            if (err instanceof ErrorApi) setErrores(err.errores);
        }
    }

    return (
        <form onSubmit={guardar} className="mx-auto max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 className="text-xl font-semibold">Nuevo ticket</h1>
            <Campo etiqueta="Título" error={errores.titulo}>
                <input className={claseInput} value={form.titulo} onChange={actualizar('titulo')} />
            </Campo>
            <Campo etiqueta="Descripción" error={errores.descripcion}>
                <textarea className={claseInput} rows={5} value={form.descripcion} onChange={actualizar('descripcion')} />
            </Campo>
            <div className="grid gap-4 md:grid-cols-3">
                <Campo etiqueta="Cliente" error={errores.cliente_id}>
                    <select className={claseInput} value={form.cliente_id} onChange={actualizar('cliente_id')}>
                        <option value="">Selecciona…</option>
                        {clientes.map((c) => <option key={c.id} value={c.id}>{c.nombre}</option>)}
                    </select>
                </Campo>
                <Campo etiqueta="Prioridad" error={errores.prioridad}>
                    <select className={claseInput} value={form.prioridad} onChange={actualizar('prioridad')}>
                        {Object.entries(PRIORIDADES).map(([v, t]) => <option key={v} value={v}>{t}</option>)}
                    </select>
                </Campo>
                <Campo etiqueta="Asignar a" error={errores.tecnico_id}>
                    <select className={claseInput} value={form.tecnico_id} onChange={actualizar('tecnico_id')}>
                        <option value="">Sin asignar</option>
                        {tecnicos.map((t) => <option key={t.id} value={t.id}>{t.nombre}</option>)}
                    </select>
                </Campo>
            </div>
            <div className="flex justify-end gap-2">
                <button type="button" onClick={() => navegar(-1)} className="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button className={claseBoton}>Crear ticket</button>
            </div>
        </form>
    );
}
