# SIGO-NEXO

**Sistema Integrado de Gestión Operativa** — Auditoría de tickets, control de SLA y gestión de cuadrillas técnicas en campo.

Landing pública en `/` · Aplicación privada en `/dashboard`.

---

## Tabla de contenidos

- [Stack tecnológico](#stack-tecnológico)
- [Requisitos](#requisitos)
- [Instalación local](#instalación-local)
- [Base de datos PostgreSQL + PostGIS](#base-de-datos-postgresql--postgis)
- [Variables de entorno](#variables-de-entorno)
- [Ejecutar el proyecto](#ejecutar-el-proyecto)
- [Pruebas](#pruebas)
- [Roles y permisos](#roles-y-permisos)
- [Estructura funcional](#estructura-funcional)
- [Google Drive (opcional)](#google-drive-opcional)
- [Despliegue](#despliegue)
- [Convenciones de commits y ramas](#convenciones-de-commits-y-ramas)

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Vistas | Blade + Livewire 4 + Alpine.js |
| Estilos | Tailwind CSS 4 + Vite 7 |
| Base de datos | PostgreSQL 18 + PostGIS 3.6 (obligatorio) |
| Tiempo real | Laravel Reverb + Laravel Echo |
| Gráficos | Chart.js 4 |
| Mapas | Leaflet + OpenStreetMap |
| Archivos | Google Drive API |
| PWA | Service Worker + IndexedDB + Background Sync |
| Gestor de paquetes | pnpm (compatible con npm) |

> ⚠️ **No se usa SQLite ni MySQL.** Las migraciones requieren PostGIS (tipo `geometry`).

---

## Requisitos

- PHP 8.3+ con extensiones `pdo_pgsql`, `pgsql`, `gd`
- Composer 2
- Node.js 20+ y pnpm
- PostgreSQL 18 con PostGIS 3.6

---

## Instalación local

```powershell
cd C:\xampp\htdocs\auditoria-nexo-tickets

composer install
cp .env.example .env
php artisan key:generate

pnpm install
pnpm run build
```

---

## Base de datos PostgreSQL + PostGIS

### 1. Crear usuario y base de datos

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres
```

Dentro de psql:

```sql
CREATE USER laravel WITH PASSWORD 'laravel123';
CREATE DATABASE auditoria_tickets OWNER laravel;
CREATE DATABASE auditoria_tickets_test OWNER laravel;
\q
```

### 2. Habilitar PostGIS en ambas bases

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d auditoria_tickets -c "CREATE EXTENSION IF NOT EXISTS postgis;"
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d auditoria_tickets -c "GRANT ALL ON SCHEMA public TO laravel;"

& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d auditoria_tickets_test -c "CREATE EXTENSION IF NOT EXISTS postgis;"
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d auditoria_tickets_test -c "GRANT ALL ON SCHEMA public TO laravel;"
```

### 3. Migraciones y datos iniciales

En PowerShell, forzando PostgreSQL en cada comando:

```powershell
$env:DB_CONNECTION='pgsql'; $env:DB_HOST='127.0.0.1'; $env:DB_PORT='5432'; $env:DB_DATABASE='auditoria_tickets'; $env:DB_USERNAME='laravel'; $env:DB_PASSWORD='laravel123'; php artisan migrate --force

$env:DB_CONNECTION='pgsql'; $env:DB_HOST='127.0.0.1'; $env:DB_PORT='5432'; $env:DB_DATABASE='auditoria_tickets'; $env:DB_USERNAME='laravel'; $env:DB_PASSWORD='laravel123'; php artisan db:seed --force
```

> En Linux/macOS, reemplazá el prefijo `$env:VAR='...';` por `VAR=...` delante del comando.

---

## Variables de entorno

Configurá estas secciones en `.env`:

```dotenv
APP_NAME=SIGO-NEXO
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=auditoria_tickets
DB_USERNAME=laravel
DB_PASSWORD=laravel123

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
CACHE_STORE=database

REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=${REVERB_PORT}
VITE_REVERB_SCHEME=${REVERB_SCHEME}

# Opcional: Google Drive
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REFRESH_TOKEN=
GOOGLE_PARENT_FOLDER_ID=
```

Después de cambiar `.env`:

```powershell
php artisan config:clear
```

---

## Ejecutar el proyecto

Terminal 1 — servidor web:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2 — WebSockets (mapa en vivo):

```powershell
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Terminal 3 — cola de trabajos:

```powershell
php artisan queue:work
```

Terminal 4 — Vite (solo en desarrollo):

```powershell
pnpm run dev
```

### Usuarios de prueba (seeder)

Todos con contraseña `password`:

| Rol | Email |
|-----|-------|
| Administrador | `admin@nexo.com` |
| Supervisor | `supervisor@nexo.com` |
| Técnico mant. | `abel.garcia@nexo.com` |
| Técnico mant. | `lucas.barreto@nexo.com` |
| Técnico limp. | `santiago.garcia@nexo.com` |
| Técnico limp. | `maria.ruiz@nexo.com` |

> Las cuentas nuevas las crea el administrador con contraseña aleatoria; el usuario debe cambiarla al primer ingreso.

---

## Pruebas

```powershell
# Suite SQLite (ExampleTest)
php vendor/bin/phpunit

# Suite PostgreSQL + PostGIS (flujo completo)
$env:DB_CONNECTION='pgsql'; $env:DB_HOST='127.0.0.1'; $env:DB_PORT='5432'; $env:DB_DATABASE='auditoria_tickets_test'; $env:DB_USERNAME='laravel'; $env:DB_PASSWORD='laravel123'; php vendor/bin/phpunit --configuration phpunit.pgsql.xml
```

Resultado esperado: `OK (2 tests, 3 assertions)` y `OK (8 tests, 18 assertions)`.

---

## Roles y permisos

| Rol | Permisos |
|-----|----------|
| **Admin** | Todo: crea usuarios, zonas, contratos, cierra/reabre tickets, panel de control y gasto |
| **Supervisor** | Crea tickets, asigna sus técnicos, ve solo sus zonas, resuelve, importa Excel |
| **Técnico** | Ve solo sus tickets asignados, sube fotos con GPS, check-in/out |

Relaciones:
- Cada supervisor tiene **técnicos propios** (`supervisor_tecnicos`).
- Cada supervisor gestiona **zonas específicas** (`supervisor_zonas`).
- Cada sitio pertenece a una zona; el SLA se calcula según el contrato del sitio.

---

## Estructura funcional

| Ruta | Descripción |
|------|-------------|
| `/` | Landing pública SIGO-NEXO (simulador Alpine, temas) |
| `/login` | Ingreso |
| `/dashboard` | Panel por rol |
| `/tickets` | Listado filtrado por rol |
| `/tickets/crear` | Alta manual (solo supervisores) |
| `/importar` | Importación Excel con validación de hash y cambios |
| `/sitios` | Sitios, zonas, técnicos fijos y contrato SLA |
| `/mapa` | Mapa en vivo + GPS |
| `/control` | Panel de control del admin (solo lectura) |
| `/gastos` | Gasto por mes, gráficos torta/barras/lineales |
| `/alertas` | Alertas SLA |
| `/perfil` | Datos personales y token de Google Drive |

---

## Google Drive (opcional)

1. Habilitá **Google Drive API** en Google Cloud.
2. Credenciales OAuth → **Aplicación de escritorio** → copiá client ID y secret al `.env`.
3. Obtené el refresh token:

```powershell
php artisan google:auth
```

4. Pegalo en **Mi perfil → Google Drive → Guardar token**.

Sin Google configurado el sistema funciona igual: las fotos se guardan localmente.

---

## Despliegue

```powershell
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
pnpm install --frozen-lockfile
pnpm run build
```

Servicios a configurar en producción: PostgreSQL + PostGIS, Reverb, cola de trabajos y scheduler:

```powershell
php artisan schedule:work
```

---

## Convenciones de commits y ramas

Ramas técnicas:

| Rama | Contenido |
|------|-----------|
| `database/esquemas-y-migraciones` | Migraciones |
| `backend/logica-principal` | Modelos, controladores, Livewire, rutas, tests |
| `frontend/landing-e-interfaz` | Vistas, CSS, JS, SVG |

Commits en español (prefijo estándar en inglés, descripción en español):

```powershell
git commit -m "feat(backend): agregar filtro de tickets por zona"
git commit -m "fix(frontend): corregir subtitulo del logo"
git commit -m "refactor(db): unificar migraciones de zonas"
git commit -m "chore: actualizar dependencias de pnpm"
```

Orden de merge de PRs: **base de datos → backend → frontend**.

---

## Licencia

Uso interno · Nexo Obras y Servicios.
