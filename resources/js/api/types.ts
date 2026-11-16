export type Estado = 'abierto' | 'en_progreso' | 'resuelto' | 'cerrado';
export type Prioridad = 'baja' | 'media' | 'alta' | 'urgente';

export interface Usuario {
    id: number;
    nombre: string;
    email: string;
    rol: 'admin' | 'tecnico';
}

export interface Cliente {
    id: number;
    nombre: string;
    cif: string;
    email: string | null;
    telefono: string | null;
    tickets_abiertos?: number;
    tickets_total?: number;
}

export interface Comentario {
    id: number;
    texto: string;
    autor: Usuario;
    creado: string;
}

export interface Ticket {
    id: number;
    titulo: string;
    descripcion: string;
    estado: Estado;
    prioridad: Prioridad;
    cliente?: Cliente;
    tecnico?: Usuario | null;
    autor?: Usuario;
    comentarios?: Comentario[];
    comentarios_total?: number;
    creado: string;
    actualizado: string;
    resuelto: string | null;
}

export interface Paginado<T> {
    data: T[];
    meta: { current_page: number; last_page: number; total: number; per_page: number };
}

export interface Estadisticas {
    por_estado: Record<Estado, number>;
    pendientes_por_prioridad: Record<Prioridad, number>;
    sin_asignar: number;
    horas_medias_resolucion: number | null;
    total: number;
}

export const ESTADOS: Record<Estado, string> = {
    abierto: 'Abierto',
    en_progreso: 'En progreso',
    resuelto: 'Resuelto',
    cerrado: 'Cerrado',
};

export const PRIORIDADES: Record<Prioridad, string> = {
    baja: 'Baja',
    media: 'Media',
    alta: 'Alta',
    urgente: 'Urgente',
};
