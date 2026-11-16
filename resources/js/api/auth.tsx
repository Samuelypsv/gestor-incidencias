import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react';
import { api, enviar, token } from './client';
import type { Usuario } from './types';

interface Sesion {
    usuario: Usuario | null;
    cargando: boolean;
    entrar: (email: string, password: string) => Promise<void>;
    salir: () => Promise<void>;
}

const ContextoSesion = createContext<Sesion | null>(null);

export function ProveedorSesion({ children }: { children: ReactNode }) {
    const [usuario, setUsuario] = useState<Usuario | null>(null);
    const [cargando, setCargando] = useState(true);

    useEffect(() => {
        const alCaducar = () => setUsuario(null);
        window.addEventListener('sesion-caducada', alCaducar);

        if (token.obtener()) {
            api<{ data: Usuario }>('/yo')
                .then((r) => setUsuario(r.data))
                .catch(() => token.borrar())
                .finally(() => setCargando(false));
        } else {
            setCargando(false);
        }

        return () => window.removeEventListener('sesion-caducada', alCaducar);
    }, []);

    const entrar = useCallback(async (email: string, password: string) => {
        const r = await enviar<{ token: string; usuario: Usuario }>('/login', 'POST', { email, password });
        token.guardar(r.token);
        setUsuario(r.usuario);
    }, []);

    const salir = useCallback(async () => {
        await enviar('/logout', 'POST').catch(() => undefined);
        token.borrar();
        setUsuario(null);
    }, []);

    return <ContextoSesion.Provider value={{ usuario, cargando, entrar, salir }}>{children}</ContextoSesion.Provider>;
}

export function useSesion(): Sesion {
    const ctx = useContext(ContextoSesion);
    if (!ctx) throw new Error('useSesion debe usarse dentro de <ProveedorSesion>');
    return ctx;
}
