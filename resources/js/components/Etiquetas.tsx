import { ESTADOS, PRIORIDADES, type Estado, type Prioridad } from '../api/types';

const coloresEstado: Record<Estado, string> = {
    abierto: 'bg-sky-100 text-sky-800',
    en_progreso: 'bg-amber-100 text-amber-800',
    resuelto: 'bg-emerald-100 text-emerald-800',
    cerrado: 'bg-slate-200 text-slate-600',
};

const coloresPrioridad: Record<Prioridad, string> = {
    baja: 'bg-slate-100 text-slate-600',
    media: 'bg-indigo-100 text-indigo-700',
    alta: 'bg-orange-100 text-orange-800',
    urgente: 'bg-red-600 text-white',
};

const base = 'inline-block rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap';

export const EtiquetaEstado = ({ estado }: { estado: Estado }) => (
    <span className={`${base} ${coloresEstado[estado]}`}>{ESTADOS[estado]}</span>
);

export const EtiquetaPrioridad = ({ prioridad }: { prioridad: Prioridad }) => (
    <span className={`${base} ${coloresPrioridad[prioridad]}`}>{PRIORIDADES[prioridad]}</span>
);
