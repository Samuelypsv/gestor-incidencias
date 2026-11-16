import type { ReactNode } from 'react';

interface Props {
    etiqueta: string;
    error?: string[];
    children: ReactNode;
}

/** Envuelve un input con su etiqueta y el error de validación que devuelve Laravel. */
export function Campo({ etiqueta, error, children }: Props) {
    return (
        <label className="block">
            <span className="mb-1 block text-sm font-medium text-slate-700">{etiqueta}</span>
            {children}
            {error && <span className="mt-1 block text-sm text-red-600">{error[0]}</span>}
        </label>
    );
}

export const claseInput =
    'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200';

export const claseBoton =
    'inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50';
