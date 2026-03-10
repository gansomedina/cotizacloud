# CLAUDE.md — CotizaCloud

## Project Overview

CotizaCloud is a multi-tenant SaaS application for quote management, sales tracking, and conversion analytics. Built as a custom PHP microframework with MySQL, it provides businesses with tools to create quotes, track client interactions, manage sales/receipts, and predict quote conversion through a behavioral analytics engine (Radar).

**Live domain:** `cotiza.cloud` (subdomains per tenant, e.g. `empresa.cotiza.cloud`)

## Tech Stack

- **Backend:** PHP 7.4+ (no external frameworks or Composer dependencies)
- **Database:** MySQL 5.7+ via PDO
- **Frontend:** Tailwind CSS (CDN), Feather Icons, vanilla JS
- **Architecture:** Single entry point (`index.php`), module-based routing, multi-tenant via subdomains

## Project Structure

```
/
├── index.php              # Single entry point & router registration
├── config.php             # Environment config (gitignored — contains credentials)
├── core/                  # Framework classes
│   ├── Auth.php           # Authentication, sessions, permissions
│   ├── DB.php             # PDO database abstraction
│   ├── Router.php         # URL pattern routing
│   ├── Helpers.php        # Utility functions (slug, CSRF, formatting, etc.)
│   └── layout.php         # HTML shell template with sidebar nav
├── modules/               # Feature modules (each has action files)
│   ├── auth/              # Login, logout, registration
│   ├── cotizaciones/      # Quote CRUD (nueva, ver, editar, enviar, etc.)
│   ├── clientes/          # Client management
│   ├── ventas/            # Sales & receipts
│   ├── costos/            # Cost/expense tracking
│   ├── dashboard/         # Dashboard view
│   ├── config/            # Admin panel (empresa, articulos, cupones, usuarios)
│   ├── reportes/          # Reporting
│   └── radar/             # Analytics & scoring engine (Radar.php + index.php)
├── api/                   # JSON API endpoints
│   ├── track.php          # Quote interaction tracking
│   └── quote-action.php   # Accept/reject quotes
├── public/                # Public-facing pages (no auth required)
│   ├── cotizacion.php     # Public quote view
│   ├── venta.php          # Public sale view
│   └── recibo.php         # Public receipt view
└── error_log              # Application error log
```

## Core Classes & Patterns

### Database (`core/DB.php`)

```php
DB::query($sql, $params)   // Fetch multiple rows
DB::row($sql, $params)     // Fetch single row
DB::val($sql, $params)     // Fetch single value
DB::execute($sql, $params) // INSERT/UPDATE/DELETE
DB::insert($sql, $params)  // INSERT returning last ID
DB::beginTransaction() / DB::commit() / DB::rollback()
```

All queries use PDO prepared statements. Always include `empresa_id` in WHERE clauses for tenant isolation.

### Authentication (`core/Auth.php`)

```php
Auth::init()                    // Initialize session & detect tenant
Auth::login($email, $password)  // Authenticate user
Auth::logout()                  // End session
Auth::usuario()                 // Get current user array
Auth::empresa()                 // Get current company array
Auth::logueado()                // Bool: is logged in?
Auth::es_admin()                // Bool: is admin?
Auth::puede($permiso)           // Bool: has permission?
Auth::requerir_login($url)      // Middleware: redirect if not logged in
Auth::requerir_admin()          // Middleware: require admin role
Auth::requerir_permiso($perm)   // Middleware: require specific permission
```

**Permissions:** `editar_precios`, `aplicar_descuentos`, `ver_todas_cots`, `eliminar_items_venta`, `cancelar_recibos`

### Router (`core/Router.php`)

```php
Router::get($pattern, $handler)
Router::post($pattern, $handler)
Router::any($pattern, $handler)
```

Route patterns use `:param` for dynamic segments (e.g. `/c/:slug`).

### Helpers (`core/Helpers.php`)

Key functions:
- **Escaping:** `e($str)` for HTML, `ej($str)` for JSON
- **CSRF:** `csrf_token()`, `csrf_field()`, `csrf_verify()`, `csrf_check()`
- **Input:** `input($key)`, `input_int($key)`, `input_float($key)`
- **JSON responses:** `json_ok($data)`, `json_error($msg, $code)`
- **Navigation:** `redirect($url)`, `redirect_back()`
- **Flash messages:** `flash($type, $msg)`, `flash_get()`
- **Formatting:** `format_money($amount, $currency)`, `fecha_humana($date)`, `tiempo_relativo($date)`
- **Slugs:** `slug($text)`, `slug_unico($table, $column, $text)`
- **Pagination:** `paginar($total, $per_page, $current)`
- **File uploads:** `upload_archivo($input_name, $subdir)`

## Naming Conventions

| Context | Convention | Examples |
|---------|-----------|----------|
| PHP classes | PascalCase | `Auth`, `DB`, `Router`, `Radar` |
| Functions | snake_case | `slug_unico()`, `generar_token()` |
| Variables | snake_case | `$empresa_id`, `$usuario_str` |
| DB tables | snake_case, mostly plural | `usuarios`, `cotizaciones`, `empresas` |
| DB columns | snake_case | `created_at`, `empresa_id`, `descuento_auto_pct` |
| Module action files | snake_case | `login_post.php`, `crear.php`, `ver.php` |
| URLs | kebab-case segments | `/cotizaciones/nueva`, `/config?tab=empresa` |

## Multi-Tenant Architecture

- Tenant detected from subdomain: `empresa.cotiza.cloud` → slug `empresa`
- Constants `ENTERPRISE_ID` and `ENTERPRISE_SLUG` set per request
- **All database queries MUST filter by `empresa_id`** to maintain tenant isolation
- Uploads are scoped: `/public/assets/uploads/{empresa_id}/`

## URL Routes

**Public (no auth):**
- `GET /` — Landing page (root domain only)
- `GET /registro` / `POST /registro` — Company registration
- `GET /c/:slug` — Public quote view
- `GET /v/:slug` — Public sale view
- `GET /r/:token` — Public receipt view
- `POST /api/track` — Analytics event tracking
- `POST /api/quote-action` — Accept/reject quote

**Protected (requires login):**
- `/dashboard` — Main dashboard
- `/cotizaciones` — Quote management (CRUD)
- `/clientes` — Client management
- `/ventas` — Sales & receipts
- `/costos` — Cost tracking
- `/reportes` — Reports
- `/radar` — Conversion analytics
- `/config` — Admin settings (tabs: empresa, articulos, cupones, usuarios)

## Security Practices

- CSRF tokens on all forms — use `csrf_field()` in forms and `csrf_check()` in POST handlers
- Passwords hashed with bcrypt (cost 12)
- All SQL uses PDO prepared statements — never concatenate user input into queries
- HTML output escaped with `e()` — always escape user-generated content
- Sessions: HttpOnly, SameSite=Lax cookies; 8-hour expiration
- Sanitized input via `input()`, `input_int()`, `input_float()`

## Database

**Key tables:** `empresas`, `usuarios`, `user_sessions`, `cotizaciones`, `cotizacion_lineas`, `cotizacion_log`, `ventas`, `recibos`, `clientes`, `articulos`, `cupones`, `categorias_costos`, `gastos_venta`, `folios`

**Radar tables:** `quote_sessions`, `quote_events`, `quote_session_summary`, `radar_visitors_internos`, `radar_ips_internas`, `radar_fit_calibracion`

**Quote states:** `enviada` → `vista` → `aceptada` | `rechazada`

**Folio generation:** `DB::siguiente_folio($empresa_id, $tipo, $prefijo)` for sequential numbering (COT-, VTA-, REC- prefixes)

## Environment Variables

Required in `config.php` (gitignored):

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | `development` or `production` | — |
| `DEBUG` | Show detailed errors | `true` in dev |
| `DB_HOST` | MySQL host | `localhost` |
| `DB_PORT` | MySQL port | `3306` |
| `DB_NAME` | Database name | — |
| `DB_USER` | Database user | — |
| `DB_PASS` | Database password | — |
| `APP_SECRET` | Session encryption key (32+ chars in prod) | — |
| `BASE_DOMAIN` | Base domain | `cotiza.cloud` |
| `BASE_URL` | Full base URL | `https://cotiza.cloud` |
| `APP_TIMEZONE` | PHP timezone | `America/Hermosillo` |

## Development Notes

- **No package manager** — no Composer, no npm. All dependencies loaded via CDN.
- **No automated tests** — manual testing only.
- **No CI/CD pipeline** — deploy by pushing files to server.
- **Single entry point** — all requests route through `index.php`.
- **Language** — codebase uses Spanish for business logic names (cotizaciones, clientes, ventas, etc.) and English for technical/framework code.
- **Error log** — check `/error_log` for runtime errors.

## Common Tasks

**Adding a new module action:**
1. Create a PHP file in the appropriate `modules/<module>/` directory
2. Register the route in `index.php`
3. Use `Auth::requerir_login()` at the top for protected routes
4. Include `csrf_check()` in POST handlers

**Adding an API endpoint:**
1. Create a PHP file in `api/`
2. Register the route in `index.php` with `Router::post()`
3. Return responses via `json_ok()` or `json_error()`

**Working with quotes:**
- Quote creation: `modules/cotizaciones/nueva.php` (largest file, ~56K)
- Public view: `public/cotizacion.php` (~53K)
- Line items stored in `cotizacion_lineas` linked by `cotizacion_id`
- Audit trail in `cotizacion_log`

## Radar Analytics Engine

Located in `modules/radar/Radar.php` (~1,057 lines). Predicts quote conversion using 15 behavioral buckets based on tracked events (opens, scrolls, section views, price reviews, etc.). Three sensitivity modes: `agresivo`, `medio`, `ligero`. Internal users/IPs are excluded via anti-gaming filters.
