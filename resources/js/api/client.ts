const CLAVE_TOKEN = 'gestor-incidencias.token';

export class ErrorApi extends Error {
    constructor(
        message: string,
        public status: number,
        public errores: Record<string, string[]> = {},
    ) {
        super(message);
    }
}

export const token = {
    obtener: () => localStorage.getItem(CLAVE_TOKEN),
    guardar: (valor: string) => localStorage.setItem(CLAVE_TOKEN, valor),
    borrar: () => localStorage.removeItem(CLAVE_TOKEN),
};

/**
 * Pequeño cliente HTTP sobre fetch: añade el token, convierte la respuesta a JSON
 * y transforma los errores de Laravel (422, 401...) en un ErrorApi tipado.
 */
export async function api<T>(ruta: string, opciones: RequestInit = {}): Promise<T> {
    const cabeceras: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };
    const t = token.obtener();
    if (t) cabeceras.Authorization = `Bearer ${t}`;

    const respuesta = await fetch(`/api${ruta}`, { ...opciones, headers: { ...cabeceras, ...opciones.headers } });

    if (respuesta.status === 401) {
        token.borrar();
        window.dispatchEvent(new Event('sesion-caducada'));
    }

    if (respuesta.status === 204) return undefined as T;

    const cuerpo = await respuesta.json().catch(() => ({}));

    if (!respuesta.ok) {
        throw new ErrorApi(cuerpo.message ?? 'Error inesperado', respuesta.status, cuerpo.errors ?? {});
    }

    return cuerpo as T;
}

export const enviar = <T>(ruta: string, metodo: 'POST' | 'PATCH' | 'DELETE', datos?: unknown) =>
    api<T>(ruta, { method: metodo, body: datos === undefined ? undefined : JSON.stringify(datos) });
