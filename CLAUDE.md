# CotizaCloud - Notas de Desarrollo

## Resumen del Proyecto
- **Tipo**: SaaS de cotizaciones (PHP backend + Capacitor para apps nativas)
- **URL producción**: https://cotiza.cloud
- **Stack**: PHP puro (sin framework), Capacitor 8, iOS/Android
- **Estructura**: Router propio (`core/Router.php`), módulos en `modules/`, vistas públicas en `public/`

## Estado Actual de la App Móvil (iOS)

### Completado
- Xcode instalado en Mac del usuario (Intel)
- Ruby 4.0.2 instalado via Homebrew (ruta: `/usr/local/opt/ruby/`)
- CocoaPods 1.16.2 instalado (ruta gems: `/usr/local/lib/ruby/gems/4.0.0/bin/`)
- Node.js v24.14.0 y npm 11.9.0 instalados
- Proyecto usa Swift Package Manager (SPM), NO CocoaPods - no necesita `pod install`
- `npx cap sync ios` funciona correctamente
- App compila y corre en simulador iOS
- Root URL (`/`) redirige a `/login` en vez de mostrar landing (commit 5d0e727)
- App carga correctamente en simulador mostrando pantalla de login

### Configuración Capacitor (`capacitor.config.ts`)
- appId: `com.cotizacloud.app`
- appName: `CotizaCloud`
- server.url: `https://cotiza.cloud` (app remota, no local)
- Splash screen: fondo `#1a5c38`, duración 2s
- StatusBar: estilo DARK, fondo `#1a5c38`

### Entorno del Usuario (Mac Intel)
- PATH de Ruby: `/usr/local/opt/ruby/bin`
- PATH de Gems: `/usr/local/lib/ruby/gems/4.0.0/bin`
- Ambos agregados a `~/.zshrc`

## Push Notifications (iOS) — Estado

### Completado
- Plugin `@capacitor/push-notifications@8.0.2` instalado y sincronizado
- Tablas BD: migración ejecutada en servidor (`migrations/add_push_notifications.sql`)
  - `dispositivos_push` — tokens de dispositivos
  - `notificaciones_push` — log de notificaciones enviadas
- Servicio PHP APNs: `core/PushNotification.php` — envío via HTTP/2 con JWT ES256
- API endpoints:
  - `POST /api/push/register` — registra token del dispositivo (requiere login)
  - `POST /api/push/unregister` — desactiva token
- Hooks en `api/quote_action.php` — dispara push al aceptar/rechazar cotización
- JS cliente: `assets/js/push.js` — pide permisos, registra token, muestra banner en foreground
- Plugin configurado en `capacitor.config.ts` con `presentationOptions: ['badge', 'sound', 'alert']`
- Cuenta Apple Developer activa
- App ID `com.cotizacloud.app` registrado con Push Notifications habilitado
- APNs Key creada (Key ID: `D2AW3CT2UF`, Team ID: `T3LPNPVHZ2`)
- Archivo `.p8` subido al servidor en `/home/key/AuthKey_D2AW3CT2UF.p8`
- Config APNs configurado en `config.php` con Key ID, Team ID y ruta al .p8
- Push Notifications capability habilitado en Xcode (`App.entitlements`)

### Pendiente
1. **Compilar y probar** en dispositivo real (push no funciona en simulador)
2. Para producción: cambiar `aps-environment` en `App.entitlements` de `development` a `production`

## App Store Submission — Estado

### Completado
- Build 1 (v1.0) subido a App Store Connect
- Ficha de la app configurada (screenshots, descripción, categoría)
- Pricing: Free (USD 0.00)
- Disponibilidad: 175 países
- Página de privacidad: `public/privacidad.php` → https://cotiza.cloud/privacidad
- Privacy Policy URL configurada en App Store Connect
- App Privacy: 5 data types (Name, Email, Phone, User ID, Product Interaction) — todos "App Functionality", linked to user, no tracking
- Export Compliance: no usa encriptación propia (solo HTTPS del sistema)
- Cuenta de prueba para Apple Review: `review@cotiza.cloud` / `Review2026!`
  - Script: `migrations/create_review_user.php` (ejecutar en servidor antes de que Apple revise)
  - Crea empresa "Apple Review Demo" (slug: `apple-review`) con cliente y cotización de ejemplo
- **App enviada a revisión el 20 marzo 2026** — esperando respuesta de Apple (24-48 hrs)

### Pendiente
1. Esperar aprobación de Apple (o corregir si rechazan)
2. Agregar `ITSAppUsesNonExemptEncryption = NO` al Info.plist para evitar pregunta de compliance en futuros builds
3. Probar push notifications en dispositivo real
4. Cambiar `aps-environment` en `App.entitlements` de `development` a `production`
5. Android: carpeta `android/` ya existe, falta probar y publicar en Google Play

## Sistema de Planes (Free / Pro / Business)

### Implementado
- ENUM en BD: `free`, `pro`, `business` (migración automática desde `trial`)
- `core/Helpers.php` → `trial_info()` retorna: `es_free`, `es_pro`, `es_business`, `es_pagado`, `plan_label`
- `es_trial` se mantiene como alias de `es_free` para compatibilidad
- Límite de 25 cotizaciones totales en plan Free (enforcement en `modules/cotizaciones/crear.php`)
- SuperAdmin puede activar/renovar/cambiar entre los 3 planes (`modules/superadmin/toggle_plan.php`)
- Tab "Usuarios" en Configuración solo visible para plan Business (`modules/config/index.php`)
- Sidebar muestra nombre del plan dinámico con color (Free=amber, Pro=verde, Business=azul)
- Landing page con sección de precios en `/landing` (toggle mensual/anual, precios tachados)
- Ruta `/` sigue yendo a `/login` (seguro para app Capacitor en App Store review)

### Precios
| Plan | Mensual | Anual (20% desc) |
|------|---------|-------------------|
| Free | $0 | — |
| Pro | $299 MXN | $239 MXN/mes ($2,868/año) |
| Business | $799 MXN | $639 MXN/mes ($7,668/año) |

### Diferenciadores por plan
- **Free**: 25 cotizaciones total, todos los módulos, 1 usuario
- **Pro**: cotizaciones ilimitadas, todos los módulos, 1 usuario, app móvil
- **Business**: usuarios ilimitados, tab Usuarios visible, costos con categorías avanzadas (pendiente), módulo proveedores (pendiente), reportes avanzados (pendiente), soporte prioritario

### Pendiente — Próxima sesión
1. **Módulo Costos Avanzados** (Business) — categorías avanzadas de costos, márgenes por categoría, análisis por proveedor
2. **Módulo Reportes Avanzados** (Business) — dashboards de equipo, comparativas entre vendedores, métricas de conversión
3. **Permisos por usuario** (Business) — en el tab Usuarios, poder activar/desactivar acceso a módulos por vendedor: Costos, Reportes, Radar. Esto permite al admin controlar qué ve cada miembro del equipo.
4. Con estos tres features queda completo el diferenciador Business vs Pro

### Archivos clave del sistema de planes
| Archivo | Función |
|---------|---------|
| `core/Helpers.php` | `trial_info()` — lógica central de planes |
| `core/Auth.php` | Mensajes de licencia vencida/suspendida |
| `core/layout.php` | Sidebar con label de plan |
| `modules/config/index.php` | Tab Usuarios condicionado a Business |
| `modules/superadmin/toggle_plan.php` | API de gestión de planes |
| `modules/superadmin/empresa.php` | UI SuperAdmin para planes |
| `modules/ayuda/licencia.php` | Página de solicitud de licencia |
| `modules/auth/landing.php` | Landing con sección de precios |

## Módulo Marketing (Business) — Pendiente

### Concepto
Complementa al módulo Radar. Radar trackea aperturas de cotizaciones; Marketing permite al empresario **hacer retargeting y medir campañas**.

### Features planeados
1. **Pixels de tracking** — El empresario configura sus IDs (Facebook Pixel, GA4, TikTok Pixel) en Configuración > Marketing. Se inyectan automáticamente en las URLs públicas de cotizaciones.
2. **Retargeting** — Clientes que vieron la cotización y no aceptaron les aparecen anuncios (gracias a los pixels).
3. **UTM tracking** — Registrar de qué campaña/fuente vino cada lead (utm_source, utm_medium, utm_campaign). Reportes de origen de leads.
4. **Página pública de empresa** — Mini landing con SEO en `cotiza.cloud/empresa/{slug}`, catálogo de servicios, botón "Solicitar cotización".

### Resumen
| Feature | Esfuerzo | Valor |
|---------|----------|-------|
| Pixels (FB/GA/TikTok) en cotizaciones | Bajo | Alto |
| UTM tracking de leads | Bajo | Medio |
| Página pública empresa con SEO | Alto | Medio |

### Implementación pendiente
- Tabla `marketing_config` (empresa_id, pixel_fb, pixel_ga, pixel_tiktok)
- Inyectar scripts en vista pública de cotización
- Config UI en módulo Configuración > Marketing (solo Business)
- UTM capture en registro/creación de clientes

## Termómetro APC (ontimetermo.php) — Radar OnTime

### Arquitectura
- `ontime.php` — Radar principal. Calcula buckets por cotización, escribe transiciones a `wp_radar_bucket_transitions`, agrega stats a `wp_options`
- `ontimetermo.php` — Termómetro de productividad del vendedor. Lee transiciones, events, usage para calcular score 0-100
- Tablas clave: `wp_radar_bucket_transitions` (historial de cambios de bucket), `wp_sliced_quote_events` (JS events del cliente), `wp_radar_usage_events` (actividad del vendedor)

### Bugs corregidos (23 marzo 2026)
1. **dormidas=0**: El filtro `NOT EXISTS _sliced_log` excluía TODAS las cotizaciones porque `_sliced_log` es log interno de Sliced Invoices (se crea al editar/crear), NO indica vista del cliente. Eliminado — solo JS events de `quote_events` son indicador real de apertura.
2. **Transiciones chicken-and-egg**: La tabla `radar_bucket_transitions` estaba vacía. `prev_buckets` se cargaba de ella, pero solo escribía transición cuando `old_bucket !== null`. Tabla vacía = nunca se escribe la primera. Fix: escribir seed con `bucket_anterior=NULL` la primera vez. También cambié query de `prev_buckets` de últimas 24h a último bucket sin límite de tiempo.
3. **cierres_bucket=0**: Consecuencia de tabla de transiciones vacía. Se resuelve solo después del primer seed.

### Resultados post-fix
- dormidas: 0 → 35 (3/13/19 en bandas 7-14d/14-21d/21+d)
- trans_periodo: 0 → 31 (seeds iniciales)
- cierres_bucket: 0 → 2
- Score: 53 (Regular) → 61 (Activo)

### Métricas que siguen en 0 (legítimamente)
- `trans_up/trans_down`: Necesitan >1 refresh del radar para detectar cambios de bucket
- `buckets_estancados`: Necesitan >14 días sin movimiento desde el seed
- `senales_ignoradas`: 0 porque el vendedor revisa radar dentro de 24h (tasa_reaccion=0.8)

### Pendiente termómetro
- Tras varios días de uso, verificar que trans_up/down y buckets_estancados empiecen a poblar
- `vencidas_sin_accion` está hardcoded a 0 (Sliced Invoices no tiene fecha de vencimiento estándar)
- Branch de trabajo: `claude/debug-connection-issues-Lc1Jr`

## Comandos Útiles
```bash
# Sincronizar cambios web con iOS
cd ~/cotizacloud && npx cap sync ios

# Abrir proyecto en Xcode
open ~/cotizacloud/ios/App/App.xcodeproj

# Sincronizar Android
npx cap sync android
```

## Sesión 24 marzo 2026

### Completado
1. **Tema Naranja** — Agregado color naranja-amarillo (#d97706) como opción de tema para cotizaciones
   - `modules/config/index.php` — botón en picker de temas
   - `public/cotizacion.php` — colores del tema en vista pública
   - `modules/config/guardar_empresa.php` — validación del valor
   - No requiere migración BD (columna es VARCHAR(20))

2. **Landing - Business plan** — Agregado "Archivos adjuntos en cotizaciones" como feature del plan Business en la sección de precios de la landing page
   - `modules/auth/landing.php`

### Branch de trabajo
- `claude/debug-connection-issues-Lc1Jr` — commits pusheados
