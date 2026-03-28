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
- **App enviada a revisión el 20 marzo 2026**
- Build 1 rechazado (2.1.0 App Completeness) — crash en iPad Air al tomar foto
- `ITSAppUsesNonExemptEncryption = NO` agregado al Info.plist (Build 2)
- `NSCameraUsageDescription` y `NSPhotoLibraryUsageDescription` agregados al Info.plist (Build 2)
- Fix crash iPad: `CAPBridgeViewController` envuelto en `UINavigationController` en `AppDelegate.swift` (Capacitor issue #7106)
- Build 2 (v1.0 build 2) subido a App Store Connect

### Pendiente
1. Reenviar Build 2 a revisión de Apple (pendiente de usuario)
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

## Sesión 26 marzo 2026

### Completado
1. **Fix sobrepago en ventas** — `abono.php` ya no trunca `pagado` al total cuando hay sobrepago
2. **Concepto en email de abono** — `Mailer::enviar_abono()` incluye concepto
3. **Modo de costos** — Tab "Costos" en Config con 3 modos: por venta, por empresa, ambos (Business)
4. **Reportes adaptados al modo** — Tab Costos y tab Financiero respetan `costos_modo`
5. **Punto de equilibrio** — Reportes: gastos fijos mensuales, ventas necesarias, cobertura actual
6. **Ranking de rentabilidad** — Reportes: top 5 más y menos rentables
7. **Tab Proveedores en reportes** — Business: top proveedores, pagos mensuales, detalle
8. **Web Push para navegador** — Service Worker + VAPID + RFC 8291, sin dependencias externas
9. **Push de abonos** — Notificación push al registrar pago (web + app)
10. **Toggles de notificaciones** — Config > Empresa: on/off por evento (acepta, rechaza, abono, radar)
11. **Email superadmin** — Notificación por email de nuevas empresas, tickets y licencias
12. **Asesor en ventas** — `quote_action.php` hereda `usuario_id` y `vendedor_id` de la cotización
13. **Permisos crear/editar cotización** — Nuevos permisos granulares por usuario
14. **Ocultar cantidad/precio unitario** — 2 niveles: empresa (slugs públicos) y usuario (editor interno)
15. **Fix dialog "Asignar cliente"** — Ya no se abre solo al cargar la página
16. **Ventas sin pagos** — Tarjeta en dashboard con ventas pendientes sin ningún abono
17. **Fix eliminar cotización** — Limpia dependencias en transacción, muestra error real
18. **Labels reportes cotizaciones** — "Sin abrir", "Suspendidas", "Abiertas" en vez de "Enviadas"/"Vistas"

### Auditoría de seguridad — Corregidos
1. **CRÍTICO**: Total se recalcula server-side al aceptar cotización (no confiar en cliente)
2. **ALTO**: Pagos concurrentes con `FOR UPDATE` (previene corrupción de saldo)
3. **ALTO**: Folios atómicos con `DB::siguiente_folio()` (previene duplicados)
4. **ALTO**: Reset password solo invalida sesiones del usuario afectado
5. **CRÍTICO**: Permisos `usuario.php` — UPDATE/INSERT incluyen las 3 columnas nuevas

### Auditoría — Pendientes (medios/bajos)
- CSRF en 17 endpoints JSON POST (especialmente config/usuario y config/empresa)
- `SELECT *` en Auth.php hot path (cargar solo columnas necesarias)
- `password_hash` en `Auth::usuario()` (remover del SELECT de sesión)
- `ALTER TABLE` en `trial_info()` (mover a migración, no runtime)
- `ENV` default cambiar de 'development' a 'production'
- Falta HSTS y Content-Security-Policy headers
- 16 archivos backup muertos (688 KB)
- 6 tablas sin FOREIGN KEY constraints
- Sesiones expiradas sin cron de limpieza

### Migraciones de esta sesión
1. `migrations/add_costos_modo.sql` — columna `costos_modo` en empresas
2. `migrations/add_web_push.sql` — ampliar token para Web Push subscriptions
3. `migrations/add_notif_config.sql` — columna `notif_config` JSON en empresas
4. `migrations/add_permisos_cotizaciones.sql` — `puede_crear/editar_cotizaciones` en usuarios
5. `migrations/add_ocultar_cantpu.sql` — `ocultar_cant_pu` en empresas, `puede_ver_cantidades` en usuarios

### Config.php del servidor (manual)
```php
// Web Push (VAPID)
define('VAPID_PUBLIC_KEY',  'BH3SNMbyH-Q-f1hIU2TjYc_V6vHjF7s1OPtnBxm3rX5YFPn16Qrbv9-2zg1ghp3vUgVgvHe0YKwSrt45kNdW70s');
define('VAPID_PRIVATE_PEM', '/home/cotizacl/key/vapid_private.pem');
define('VAPID_SUBJECT',     'mailto:noreply@cotiza.cloud');

// Email superadmin
define('SUPERADMIN_EMAIL',  'tu@email.com');
```

### Branch de trabajo
- `claude/review-apple-store-build-xB5jg`

## Dominio Propio por Empresa — Pendiente (análisis completo)

### Concepto
Permitir que una empresa use su propio dominio para los slugs públicos (cotizaciones, ventas, recibos).
Ejemplo: `cotizaciones.muebleria.com/c/slug` en vez de `muebleria.cotiza.cloud/c/slug`

### Arquitectura propuesta
- Columna `dominio_propio VARCHAR(255) UNIQUE` en tabla `empresas`
- En `Auth.php`: si el host NO es `*.cotiza.cloud`, buscar en `dominio_propio`
- El resto del sistema no cambia (`EMPRESA_ID` se define igual)
- Solo vistas públicas (`/c/`, `/v/`, `/r/`), NO login ni dashboard

### Requisitos del cliente
- Crear CNAME: `cotizaciones.sudominio.com → cotiza.cloud`
- O A record apuntando a la IP del servidor

### Reto principal: SSL
| Opción | Complejidad | Notas |
|--------|-------------|-------|
| cPanel AutoSSL | Baja | Genera cert automático si el dominio apunta al servidor. Límite ~100-200 dominios |
| Cloudflare como proxy | Baja | Cliente configura Cloudflare, maneja SSL. El más escalable |
| Certbot manual | Media | Requiere VPS con root. `certbot -d dominio.com` por cliente |
| Caddy server | Media | Certs automáticos. Requiere migrar de Apache |

### Verificación de propiedad del dominio
- **Fase 1**: Manual — superadmin configura después de verificar con el cliente
- **Fase 2**: DNS TXT automático — cliente agrega registro TXT con token generado
- **Fase 3**: HTTP verification — archivo `.well-known/verify-xxx`

### Panel superadmin
Ficha de empresa con: dominio propio, estado DNS (verificado/no apunta), estado SSL (activo/pendiente)

### Plan de implementación
| Fase | Qué incluye | Esfuerzo |
|------|-------------|----------|
| 1 | Solo slugs públicos + config manual superadmin | ~4 horas |
| 2 | Verificación DNS automática + estado en panel | ~3 horas |
| 3 | Login completo con dominio propio (opcional) | ~5 horas |

### Impacto en URLs
| Recurso | Sin dominio propio | Con dominio propio |
|---------|-------------------|-------------------|
| Cotización | `empresa.cotiza.cloud/c/slug` | `cots.empresa.com/c/slug` |
| Venta | `empresa.cotiza.cloud/v/slug` | `cots.empresa.com/v/slug` |
| Recibo | `empresa.cotiza.cloud/r/token` | `cots.empresa.com/r/token` |
| Login/Dashboard | `empresa.cotiza.cloud/login` | NO (sigue en cotiza.cloud) |

### Pricing sugerido
- Free/Pro: subdominio `empresa.cotiza.cloud`
- Business: dominio propio disponible

### Archivos a modificar
| Archivo | Cambio |
|---------|--------|
| `core/Auth.php` | Detección por `dominio_propio` si host no es `*.cotiza.cloud` |
| `modules/superadmin/empresa.php` | Campo para configurar dominio propio |
| BD migración | `ALTER TABLE empresas ADD COLUMN dominio_propio VARCHAR(255) UNIQUE` |
| Emails (Mailer.php) | Usar dominio propio en links si está configurado |

## Sesión 27 marzo 2026

### Completado
1. **Marketing Pixels** — Tab Config > Marketing (Business): Meta, GA4, Google Ads, TikTok con toggles on/off, validación regex, templates fijos (XSS-safe), eventos en aceptar/rechazar
2. **Privacidad actualizada** — Sección de tecnologías de seguimiento de terceros
3. **Extras en cotizaciones y ventas** (Business) — Campo `es_extra` en `cotizacion_lineas`, sección visual separada en editor y slugs públicos, botón "Agregar extra" con mismo catálogo, subtotales separados
4. **Eliminar extras** — Endpoint dedicado `/ventas/:id/eliminar-extra` con recálculo de totales
5. **Venta cruzada en landing** — Feature Business en sección de precios
6. **Fix métricas conversión** — Embudo corregido: Enviadas→Abiertas→Aceptadas→Rechazadas, tasa cierre sobre enviadas no total, excluir borradores/suspendidas de todos los conteos
7. **Fix descuento en slug aceptada** — Usa valores guardados al aceptar, no recalcula
8. **Fix folios** — Sincronizado contador con `siguiente_folio()`
9. **Descuentos copiados a venta** — `descuento_auto_amt` y `cupon_monto` en INSERT
10. **Permiso agregar_extras** — Con endpoint dedicado (no reutiliza guardar.php)
11. **Permiso eliminar_items_venta** — UI ahora muestra botón para asesor con permiso
12. **Permiso ver_reportes** — Nuevo permiso con cadena completa A→I + sidebar gate
13. **Fix proveedores permiso muerto** — Check movido antes del redirect
14. **Fix Auth.php session query** — Incluye las 6 columnas de permisos faltantes
15. **Fix marketing panel** — Sección eventos y botón estaban fuera del tab-panel
16. **Extras solo Business** — Botones y endpoints protegidos por plan
17. **Eliminar cotización robusta** — Transacción, limpia dependencias, error real

### Termómetro v4.0
1. **Período 15 días** — Cambio de 30 a 15 días rolling para feedback más rápido
2. **Benchmark de Radar inteligente** — Auto-ajustable por vendedor:
   - `benchmark = cotizaciones_activas × factor_conversion × factor_actividad`
   - `factor_conversion = max(0.3, 1/(1+ratio_cierre))` — menos cierras = más radar
   - `factor_actividad = 1 + (vistas/activas) × 0.5` — más clientes activos = más urgencia
   - No se auto-compara, no usa promedios de empresa
3. **Superadmin excluido de benchmarks** — Actividad de superadmin no infla promedios
4. **Suspendidas excluidas del score** — Como si no existieran para asignadas/vistas/dormidas
5. **Penalización ventas sin pago** — Ventas con pagado=0 y >5 días: -12% por venta, cap -40%
6. **Penalización descuentos** — Se mantiene (es mérito de la empresa, no del vendedor)
7. **Preservar bucket al aceptar** — Radar ya no borra el bucket de cotizaciones aceptadas
8. **Debug panel por vendedor** — Expandible en leaderboard (solo superadmin): dimensiones, penalizaciones, datos crudos, radar views/benchmark
9. **Tips actualizados** — Diagnóstico muestra radar views vs benchmark concreto

### Pendientes próxima sesión — Termómetro
1. **Activación 59.9% con 20/20** — Parece incorrecta, investigar cómo el período de 15 días afecta
2. **Seguimiento 23.4% con 13/15 radar** — Parece baja para 87% de cumplimiento
3. **Vendedor nuevo (7-9 días)** — Los días sin datos en el período de 15 días penalizan injustamente. Considerar ajustar benchmark proporcionalmente a días reales del vendedor
4. **Score 33 general** — Validar que los pesos estén correctos con período de 15 días
5. **Señales ignoradas** — Verificar que se calculan correctamente
6. **Playbook y tips** — Actualizar textos del leaderboard info a "15 días" y nueva metodología
7. **1 solo vendedor** — Analizar enfoque definitivo: ¿benchmark histórico propio? ¿piso adaptable?
8. **Desglose penalizaciones en debug** — Agregar pen_dormidas, pen_seguimiento, pen_conversion individuales a usuario_score

### Migraciones de esta sesión
1. `migrations/add_marketing_config.sql` — tabla marketing_config para pixels
2. `migrations/add_es_extra.sql` — `es_extra` en cotizacion_lineas
3. `migrations/add_permiso_extras.sql` — `puede_agregar_extras` en usuarios
4. `migrations/add_permiso_reportes.sql` — `puede_ver_reportes` en usuarios
5. `migrations/add_score_debug_cols.sql` — radar_views, radar_benchmark, tasa_cierre, ventas_sin_pago en usuario_score

### Radar v5
- Calibración de buckets: probable_cierre más selectivo (visibilidad 15s+, scroll 70%+)
- Cierre inminente más alcanzable (FIT 5%, 36h, 1 señal)
- Descripciones y playbooks actualizados

### Branch de trabajo
- `claude/review-apple-store-build-xB5jg`

## Termómetro v5 — Diseño final

### Los 4 factores
| # | Dimensión | Peso | Qué mide |
|---|---|---|---|
| 1 | **Activación** | 10% | ¿Envías y llegan? |
| 2 | **Engagement** | 20% | Penalizaciones post-envío |
| 3 | **Seguimiento** | 30% | Feedback del Radar (parte medular) |
| 4 | **Conversión** | 40% | ¿Cierras ventas? |

### Principio: TODO auto-ajustable, CERO valores fijos
- Todas las penalizaciones usan `close_rate` como factor de escala
- `1/close_rate` amplifica cuando cierres son raros
- `close_rate` atenúa cuando cierres son comunes
- Variables usadas: %conversión, #cotizaciones, #ventas, tasa apertura

### 1. ACTIVACIÓN (10%)
```
s_activacion = tasa_apertura - pen_no_abiertas - pen_dormidas

tasa_apertura = cot_vistas / cot_asignadas
pen_no_abiertas = min((no_abiertas / cot_asignadas) × (1 / close_rate), 1.0)
pen_dormidas = escalonada (7d, 14d, 21d) ya implementada
```
- Sin piso fijo — ratio directo
- pen_no_abiertas usa `1/close_rate` (pega fuerte)
- Excluir suspendidas y borradores

### 2. ENGAGEMENT (20%)
Capa de penalizaciones — asumimos que el asesor hizo bien su trabajo inicial.
```
s_engagement = 1.0 - pen_sin_pago - pen_descuento - pen_enfriamiento

pen_sin_pago = min((ventas_sin_pago / ventas_totales) × (1 / close_rate), 1.0)
pen_descuento = (ventas_con_descuento / ventas_totales) × close_rate
pen_enfriamiento = transiciones_down / max(transiciones_up + transiciones_down, 1)
```
- pen_sin_pago: ventas con pagado=0 — usa `1/close_rate` (fuerte)
- pen_descuento: ventas con descuento — usa `close_rate` (suave, mérito de empresa)
- pen_enfriamiento: cotizaciones que bajaron de bucket

### 3. SEGUIMIENTO (30%)
Basado en feedback del Radar. Dos componentes multiplicativos:
```
s_seguimiento = tasa_completado × calidad

tasa_completado = cots_con_feedback / cots_calientes
  (sin calientes → 0.50 neutro)

calidad = aciertos / max(aciertos + fallos, 1)
  aciertos = con_interes_contrata × (1/close_rate)
           + con_interes_regresa × 1
           + sin_interes_correcto × 1
  fallos   = con_interes_no_regresa × 1
           + sin_interes_acepta × (1/close_rate)
```

Resultados y pesos:
| Feedback | Resultado | Tipo | Factor |
|---|---|---|---|
| Con interés + contrata | Cerró la venta | Acierto | × `1/close_rate` |
| Con interés + regresa 5d | Buen seguimiento | Acierto | × 1 |
| Sin interés + no regresa | Evaluación correcta | Acierto | × 1 |
| Con interés + NO regresa | Seguimiento inefectivo | Fallo | × 1 |
| Sin interés + acepta | Perdió venta real | Fallo | × `1/close_rate` |
| Sin feedback | Ignoró señal | Ya en tasa_completado | — |

### 4. CONVERSIÓN (40%)
Se mantiene como v4 con ajustes:
```
s_conversion = sigmoid(tasa_cierre, bench_empresa) × 0.40
             + cierre_quality × 0.35
             + ttc_score × 0.25
             - pen_vencidas - pen_zona_muerta - pen_volumen
```

### Sistema de feedback en el Radar
- Dos botones: **"Con interés"** / **"Sin interés"**
- Solo en cotizaciones en buckets calientes
- Solo para el vendedor asignado
- Botones cambiables (puede corregir)
- Validación posterior: ¿el cliente regresó en 5 días? ¿Aceptó?

### BD
```sql
CREATE TABLE radar_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    tipo ENUM('con_interes','sin_interes') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cot_user (cotizacion_id, usuario_id),
    KEY idx_empresa (empresa_id),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE
);
```

### Qué se ELIMINA del v4
- `tasa_reaccion` → reemplazada por feedback
- `señales_ignoradas` → reemplazada por "sin feedback"
- `transiciones_con_reaccion` → no medible
- `bonus_transiciones` → eliminado
- `pen_trans_down` → movido a pen_enfriamiento en Engagement
- `s_consultas` → eliminado (redundante)
- Piso fijo de activación → eliminado
- `DESCUENTO_FACTOR = 0.6` → reemplazado por pen_descuento auto-ajustable

### Qué se MANTIENE
- Superadmin excluido de benchmarks
- Suspendidas excluidas de todos los conteos
- Período 15 días rolling
- Gracia 15 días para vendedores nuevos
- pen_dormidas escalonada (7d, 14d, 21d)
- Calidad de cierre por bucket (frío=2x, caliente=0.8x)
- Velocidad de cierre vs benchmark empresa
- Debug panel por vendedor (solo superadmin)

### Implementación por etapas
1. ✅ Migración radar_feedback + UI botones en Radar
2. ✅ Activación con nueva pen_no_abiertas
3. ✅ Engagement (pen_sin_pago, pen_descuento, pen_enfriamiento)
4. ✅ Seguimiento (tasa_completado × calidad con tarea/examen 40/60)
5. ✅ Pesos 10/20/30/40 + ajustes Conversión + quitar descuento duplicado
6. 🔄 Testing con datos reales

### Pendiente: 5ta dimensión — Radar Health (salud del pipeline)

#### Concepto
Medir la salud del pipeline del vendedor basado en los movimientos de buckets del Radar. Es la radiografía del producto que vendemos.

#### Datos disponibles (bucket_transitions)
- Cotizaciones que suben de bucket (frío → caliente) = pipeline mejorando
- Cotizaciones que bajan (caliente → frío/NULL) = pipeline enfriándose
- Cotizaciones que entran a buckets por primera vez
- Cotizaciones que pierden bucket (→ NULL)
- Velocidad de transiciones (cuántas por día/semana)

#### Lo que mediría
- % de cotizaciones activas en buckets calientes vs total
- Balance transiciones up vs down (neto positivo = sano)
- Cotizaciones que perdieron bucket completamente (→ NULL)
- Tendencia: ¿mejora o empeora en el período?

#### Pesos propuestos (redistribuir)
```
Activación:    8%  (era 10%)
Engagement:   17%  (era 20%)
Seguimiento:  25%  (era 30%)
Radar Health: 15%  (NUEVO)
Conversión:   35%  (era 40%)
               ───
              100%
```

#### Principios
- Auto-ajustable: basado en ratios propios del vendedor
- Sin valores fijos
- Usa close_rate como escala donde aplique
- No duplicar con pen_enfriamiento de Engagement (mover a Radar Health)

#### Preguntas por resolver
1. ¿El vendedor es responsable de que sus cotizaciones se enfríen? (parcialmente)
2. ¿Las transiciones a NULL cuentan como enfriamiento o como "salida natural"?
3. ¿Cómo manejar cotizaciones aceptadas que pierden bucket? (no es negativo)
4. ¿El volumen de transiciones importa o solo el balance?

#### Migraciones necesarias
- Ninguna — bucket_transitions ya tiene toda la data
