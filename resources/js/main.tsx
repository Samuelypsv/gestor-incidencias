import { StrictMode, type ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { ProveedorSesion, useSesion } from './api/auth';
import { Layout } from './components/Layout';
import { ClientesPage } from './pages/ClientesPage';
import { LoginPage } from './pages/LoginPage';
import { NuevoTicketPage } from './pages/NuevoTicketPage';
import { PanelPage } from './pages/PanelPage';
import { TicketPage } from './pages/TicketPage';
import { TicketsPage } from './pages/TicketsPage';

function RutaPrivada({ children }: { children: ReactNode }) {
    const { usuario, cargando } = useSesion();
    if (cargando) return null;
    return usuario ? children : <Navigate to="/login" replace />;
}

createRoot(document.getElementById('root')!).render(
    <StrictMode>
        <ProveedorSesion>
            <BrowserRouter>
                <Routes>
                    <Route path="/login" element={<LoginPage />} />
                    <Route element={<RutaPrivada><Layout /></RutaPrivada>}>
                        <Route index element={<PanelPage />} />
                        <Route path="tickets" element={<TicketsPage />} />
                        <Route path="tickets/nuevo" element={<NuevoTicketPage />} />
                        <Route path="tickets/:id" element={<TicketPage />} />
                        <Route path="clientes" element={<ClientesPage />} />
                    </Route>
                    <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
            </BrowserRouter>
        </ProveedorSesion>
    </StrictMode>,
);
