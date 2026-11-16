# 🛠️ Gestor de Incidencias

Aplicación web para que un equipo de soporte técnico gestione las incidencias (tickets) de sus clientes: alta de tickets, asignación a técnicos, cambios de estado con reglas, comentarios y un panel con estadísticas.

- **Backend:** API REST en **Laravel 13** (PHP 8.3)
- **Frontend:** panel en **React 19 + TypeScript** con Vite y Tailwind CSS
- **Despliegue:** **Docker** + Docker Compose (Apache + MySQL)

Proyecto personal de 2.º de DAM.

## Funcionalidades

- **Inicio de sesión** con token (Laravel Sanctum) y dos roles: administrador y técnico.
- **Tickets:** listado paginado con búsqueda y filtros por estado, prioridad y técnico (incluido "sin asignar"). Los filtros se guardan en la URL.
- **Flujo de estados con reglas de negocio:**
  - `abierto → en progreso → resuelto → cerrado`
  - solo se puede cerrar un ticket que ya está resuelto;
  - un ticket cerrado no se puede modificar ni comentar;
  - al resolverlo se guarda la fecha de resolución y, si se reabre, se borra.
- **Comentarios:** al comentar un ticket abierto pasa solo a "en progreso".
- **Clientes:** alta con validación de CIF (y CIF único), búsqueda y número de tickets pendientes.
- **Panel:** tickets por estado, pendientes por prioridad, tickets sin asignar y tiempo medio de resolución.
- Solo los administradores pueden borrar tickets y clientes, y no se puede borrar un cliente que tiene tickets.

## Arquitectura

```
React (resources/js)  ──fetch + token──▶  routes/api.php
                                              │
                         Form Requests (validación y reglas de negocio)
                                              │
                         Controladores  ──▶  Modelos Eloquent  ──▶  MySQL / SQLite
                                              │
                         API Resources (JSON de salida)
```

- **Enums de PHP** (`EstadoTicket`, `Prioridad`, `Rol`) con *casts* en los modelos. El flujo de estados está en `EstadoTicket::puedeCambiarA()`.
- **Form Requests** con validación y reglas que dependen del estado del ticket (método `after()`).
- **API Resources** para que los modelos no salgan tal cual: se elige qué campos y relaciones se devuelven.
- **Scopes de Eloquent** (`Ticket::filtrar()`, `Cliente::buscar()`) para los filtros.
- Las estadísticas se calculan para que funcionen igual en SQLite, MySQL y PostgreSQL.
- En el frontend, un cliente HTTP pequeño (`api/client.ts`) que añade el token y convierte los errores 422 de Laravel en errores por campo.

## Cómo ejecutarlo

### Con Docker (recomendado)

```bash
docker compose up --build
```

Abre http://localhost:8000. La primera vez se crean las tablas y datos de prueba:

| Usuario | Contraseña | Rol |
|---|---|---|
| admin@demo.test | password | Administrador |
| laura@demo.test | password | Técnico |

### En local (PHP 8.3, Composer y Node 22)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build        # o "npm run dev" mientras desarrollas
php artisan serve
```

## Tests

```bash
php artisan test
```

Tests de funcionalidad (PHPUnit) para la autenticación, los tickets y sus reglas de estado, los clientes y las estadísticas, y tests unitarios del flujo de estados. Se ejecutan con SQLite en memoria y en GitHub Actions en cada push, junto con el build del frontend y de la imagen Docker.

## Endpoints principales

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/login` | Inicia sesión y devuelve un token |
| GET | `/api/yo` | Usuario actual |
| GET | `/api/estadisticas` | Datos del panel |
| GET | `/api/tickets?estado=&prioridad=&tecnico_id=&cliente_id=&buscar=&page=` | Listado paginado |
| POST | `/api/tickets` | Crear ticket |
| GET | `/api/tickets/{id}` | Detalle con comentarios |
| PATCH | `/api/tickets/{id}` | Cambiar estado, prioridad, técnico… |
| DELETE | `/api/tickets/{id}` | Borrar (solo admin) |
| POST | `/api/tickets/{id}/comentarios` | Añadir comentario |
| GET/POST/PATCH/DELETE | `/api/clientes` | CRUD de clientes |
| GET | `/api/tecnicos` | Usuarios asignables |

## Estructura

```
app/
├── Enums/                 EstadoTicket, Prioridad, Rol
├── Http/Controllers/Api/  controladores de la API
├── Http/Requests/         validación y reglas de negocio
├── Http/Resources/        formato JSON de salida
└── Models/                Ticket, Cliente, Comentario, User
database/                  migraciones, factories y seeder con datos de demo
resources/js/              frontend React + TypeScript
├── api/                   cliente HTTP, tipos y sesión
├── components/            layout, etiquetas, campos de formulario
└── pages/                 panel, tickets, detalle, clientes, login
tests/                     tests de PHPUnit
docker/ · Dockerfile · docker-compose.yml
```
