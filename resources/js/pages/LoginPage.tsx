import { useState, type FormEvent } from 'react';
import { Navigate } from 'react-router-dom';
import { useSesion } from '../api/auth';
import { ErrorApi } from '../api/client';
import { Campo, claseBoton, claseInput } from '../components/Campo';

export function LoginPage() {
    const { usuario, entrar } = useSesion();
    const [email, setEmail] = useState('admin@demo.test');
    const [password, setPassword] = useState('password');
    const [error, setError] = useState<string | null>(null);
    const [enviando, setEnviando] = useState(false);

    if (usuario) return <Navigate to="/" replace />;

    async function alEnviar(e: FormEvent) {
        e.preventDefault();
        setEnviando(true);
        setError(null);
        try {
            await entrar(email, password);
        } catch (err) {
            setError(err instanceof ErrorApi ? (err.errores.email?.[0] ?? err.message) : 'No se pudo conectar');
        } finally {
            setEnviando(false);
        }
    }

    return (
        <div className="flex min-h-screen items-center justify-center p-4">
            <form onSubmit={alEnviar} className="w-full max-w-sm space-y-4 rounded-2xl bg-white p-8 shadow-lg">
                <h1 className="text-xl font-semibold">🛠️ Gestor de Incidencias</h1>
                <p className="text-sm text-slate-500">Usuarios de prueba: admin@demo.test o laura@demo.test (contraseña: password)</p>
                <Campo etiqueta="Email">
                    <input className={claseInput} type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
                </Campo>
                <Campo etiqueta="Contraseña">
                    <input className={claseInput} type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
                </Campo>
                {error && <p className="text-sm text-red-600">{error}</p>}
                <button className={`${claseBoton} w-full`} disabled={enviando}>{enviando ? 'Entrando…' : 'Entrar'}</button>
            </form>
        </div>
    );
}
