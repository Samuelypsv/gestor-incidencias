import { useEffect, useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { api, enviar, ErrorApi } from '../api/client';
import type { Cliente } from '../api/types';
import { Campo, claseBoton, claseInput } from '../components/Campo';

const vacio = { nombre: '', cif: '', email: '', telefono: '' };

export function ClientesPage() {
    const [clientes, setClientes] = useState<Cliente[]>([]);
    const [buscar, setBuscar] = useState('');
    const [form, setForm] = useState(vacio);
    const [errores, setErrores] = useState<Record<string, string[]>>({});

    const cargar = () => api<{ data: Cliente[] }>(`/clientes?buscar=${encodeURIComponent(buscar)}`).then((r) => setClientes(r.data));

    useEffect(() => {
        const id = setTimeout(cargar, 300);
        return () => clearTimeout(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [buscar]);

    async function guardar(e: FormEvent) {
        e.preventDefault();
        try {
            await enviar('/clientes', 'POST', form);
            setForm(vacio);
            setErrores({});
            cargar();
        } catch (err) {
            if (err instanceof ErrorApi) setErrores(err.errores);
        }
    }

    return (
        <div className="grid gap-6 lg:grid-cols-3">
            <div className="space-y-4 lg:col-span-2">
                <h1 className="text-2xl font-semibold">Clientes</h1>
                <input className={claseInput} placeholder="Buscar por nombre o CIF…" value={buscar} onChange={(e) => setBuscar(e.target.value)} />
                <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b border-slate-200 bg-slate-50 text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Nombre</th>
                                <th className="px-4 py-3">CIF</th>
                                <th className="px-4 py-3">Contacto</th>
                                <th className="px-4 py-3 text-right">Pendientes</th>
                                <th className="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {clientes.map((c) => (
                                <tr key={c.id} className="border-b border-slate-100 last:border-0">
                                    <td className="px-4 py-3 font-medium">
                                        <Link to={`/tickets?cliente_id=${c.id}`} className="text-indigo-700 hover:underline">{c.nombre}</Link>
                                    </td>
                                    <td className="px-4 py-3 font-mono text-xs">{c.cif}</td>
                                    <td className="px-4 py-3 text-slate-500">{c.email}<br />{c.telefono}</td>
                                    <td className="px-4 py-3 text-right">{c.tickets_abiertos}</td>
                                    <td className="px-4 py-3 text-right">{c.tickets_total}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <form onSubmit={guardar} className="h-fit space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:mt-12">
                <h2 className="font-medium">Nuevo cliente</h2>
                <Campo etiqueta="Nombre" error={errores.nombre}>
                    <input className={claseInput} value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} />
                </Campo>
                <Campo etiqueta="CIF" error={errores.cif}>
                    <input className={claseInput} value={form.cif} placeholder="B37123456" onChange={(e) => setForm({ ...form, cif: e.target.value })} />
                </Campo>
                <Campo etiqueta="Email" error={errores.email}>
                    <input className={claseInput} type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                </Campo>
                <Campo etiqueta="Teléfono" error={errores.telefono}>
                    <input className={claseInput} value={form.telefono} onChange={(e) => setForm({ ...form, telefono: e.target.value })} />
                </Campo>
                <button className={`${claseBoton} w-full`}>Añadir cliente</button>
            </form>
        </div>
    );
}
