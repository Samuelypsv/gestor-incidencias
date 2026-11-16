import { NavLink, Outlet } from 'react-router-dom';
import { useSesion } from '../api/auth';

const enlace = ({ isActive }: { isActive: boolean }) =>
    `rounded-lg px-3 py-2 text-sm font-medium ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}`;

export function Layout() {
    const { usuario, salir } = useSesion();

    return (
        <div className="min-h-screen">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-wrap items-center gap-2 px-4 py-3">
                    <span className="mr-4 font-semibold text-indigo-700">🛠️ Gestor de Incidencias</span>
                    <nav className="flex gap-1">
                        <NavLink to="/" end className={enlace}>Panel</NavLink>
                        <NavLink to="/tickets" className={enlace}>Tickets</NavLink>
                        <NavLink to="/clientes" className={enlace}>Clientes</NavLink>
                    </nav>
                    <div className="ml-auto flex items-center gap-3 text-sm">
                        <span className="text-slate-500">
                            {usuario?.nombre} · {usuario?.rol === 'admin' ? 'Administrador' : 'Técnico'}
                        </span>
                        <button onClick={salir} className="text-slate-600 underline hover:text-slate-900">Salir</button>
                    </div>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-4 py-6">
                <Outlet />
            </main>
        </div>
    );
}
