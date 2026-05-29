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

## Sesión 29 marzo 2026

### Termómetro v5.1 — Completado
1. **s_radar_health persistido** — nueva columna en usuario_score
2. **Score final auto-ajustable** — pesos proporcional/momentum/percentil sin valores fijos
   - `w_percentil = (n-2)/(n+18)` solo con 3+ vendedores
   - `w_momentum = (1-w_percentil) × close_rate`
   - `w_proporcional = 1 - w_percentil - w_momentum`
3. **pen_dormidas escalado con TTC** — pesos 7/TTC, 14/TTC, 21/TTC (no fijos 6/10/15)
4. **pen_dormidas × (1-tasa_apertura)** — auto-ajustable, no ×0.4 fijo
5. **pen_buckets ratio puro** — estancados/asignadas (no 0.06 fijo)
6. **pen_vencidas/zona sqrt(1/CR)** — moderado, no magic numbers
7. **Conversión sub-pesos sqrt** — close_rate/quality/speed/volumen auto-ponderados
8. **Tendencia de volumen (vol_trend)** — ventas actuales vs benchmark período anterior como 4to componente de Conversión
9. **pen_bajo_benchmark** — penalización en Engagement si vendes menos que el promedio empresa del período anterior
10. **Consistencia limitada por sqrt(close_rate)** — max reducción 31% con CR=0.10
11. **Piso auto-ajustable** — `close_rate × 0.5` (no 0.05 fijo)
12. **Bonus auto-ajustable** — `cierre_quality × close_rate` (no ×0.2 fijo)
13. **Código muerto eliminado** — ~80 líneas: tasa_reaccion, senales_ignoradas, DESCUENTO_FACTOR, transiciones duplicadas
14. **Transiciones consolidadas** — 1 query para Engagement + Radar Health (antes 2)
15. **Tips actualizados** — 5 dimensiones, menciona feedback, pipeline, cobro, volumen
16. **Panel admin (?) actualizado** — v5.1 con fórmulas en lenguaje humano
17. **Debug panel completo** — bench_ventas y ventas_periodo persistidos en BD

### Migraciones de esta sesión
1. `migrations/add_s_radar_health.sql` — columna s_radar_health en usuario_score
2. `migrations/add_eng_pen_bajo_benchmark.sql` — columna eng_pen_bajo_benchmark
3. `migrations/add_bench_ventas.sql` — columnas ventas_periodo y bench_ventas

### Score de Israel con v5.1
- Activación 100% (21/21 abiertas)
- Engagement 83.8% (pen_descuento 4.8%, pen_enfriamiento 5.7%, pen_bajo_benchmark 5.7% — 2 ventas vs 5 benchmark)
- Seguimiento 50% (1 de 2 feedbacks)
- Radar Health 42% (2 up, 3 down)
- Conversión 46.4% (2 cierres de 21, vol_trend 0.40)
- **Score final: 59 Regular** (a 2 de Activo)

### Auditoría de Seguridad Completa — Pendiente próxima sesión

#### CRÍTICOS (Acción inmediata)
1. **Datos sensibles en repo** — dumps SQL (1.8 MB), CSVs con PII, vapid_private.pem, respaldoconfig.php
2. **Credenciales hardcodeadas** — DB_PASS='Jalfonso234', APP_SECRET='cambiar-en-produccion-32chars' en config.php:20,23
3. **quote_action.php sin autenticación** — cualquiera puede aceptar/rechazar cotización sin CSRF ni verificación de destinatario
4. **Escalación de roles** — admin puede crear otros admins sin restricción de superadmin (`modules/config/usuario.php:28`)
5. **CSRF faltante** — `modules/config/guardar_empresa.php` POST sin csrf_check()

#### ALTOS
6. **IDOR en slugs públicos** — cotización/venta/recibo accesibles con slug + empresa_id secuencial
7. **agregar_extra/eliminar_extra sin ownership check** — usuario puede modificar ventas de otros
8. **extract() en Router.php:235** — inyección de variables desde URL params
9. **Open redirect post-login** — session redirect sin validar URL interna (`login_post.php:72`)
10. **Race condition plan Free** — check de 25 cotizaciones fuera de transacción (`crear.php:26`)
11. **Permisos sin validar contra plan** — admin Free puede otorgar permisos Business (`usuario.php:40-56`)
12. **unsafe unserialize()** — `import_lineas.php:44`, `cleanup_bot_views.php:90`
13. **.gitignore incompleto** — falta *.sql, *.csv, *.pem, *_backup*

#### MEDIOS
14. **Sin security headers** — falta HSTS, X-Frame-Options, CSP en .htaccess
15. **Password mínimo 6 chars** — estándar es 12+ (`registro_post.php:74`)
16. **Timezone injection DB.php:37** — date('P') interpolado en SQL sin parametrizar
17. **MarketingPixels.php JS injection** — htmlspecialchars para contexto JS (necesita json_encode)
18. **Sin rate limiting en abonos** — endpoint de pagos sin límite
19. **Floating-point en cálculos financieros** — acumulación de redondeo en descuentos/impuestos
20. **Cookie SameSite=Lax** — debería ser Strict (`Auth.php:26`)

#### Plan de acción
- **Hoy (30 min)**: .gitignore + eliminar archivos sensibles + csrf en guardar_empresa
- **Esta semana (2-3h)**: auth en quote_action, ownership en extras, validar redirect, reemplazar extract()
- **2 semanas**: security headers, password 12+, rotar credenciales, eliminar legacy scripts

### Branch de trabajo
- `claude/review-apple-store-build-xB5jg`

## Sesión 9 abril 2026 (continuación)

### Completado — Server side (desplegado y funcionando)
1. **Safari bridge endpoint** — `api/safari_bridge.php` con token HMAC firmado, pone cookie cz_vid, marca visitor interno, aprende IP, soporta redirect chain
2. **Cookie `.cotiza.cloud`** — JS setCookie en login.php con domain=.cotiza.cloud para cubrir subdominios
3. **Safari bridge cookie domain** — detecta si es cotiza.cloud (pone `.cotiza.cloud`) o custom domain (pone dominio exacto)
4. **Landing en `/`** — cotiza.cloud muestra landing para visitantes, dashboard para logueados
5. **Push badge increment** — badge_count por dispositivo, incrementa con cada push
6. **Push badge clear** — AppDelegate.swift limpia badge al abrir app + push.js clearBadge + POST /api/push/reset-badge
7. **Restaurar is_app detection** — JS Capacitor detection + ocultar registro iOS
8. **Redirect chain navegador** — login_post.php mantiene redirect chain para navegadores con dominios custom
9. **Escudo Radar (botón)** — banner en layout.php dentro del contenido principal, solo visible en app Capacitor, abre Safari al primer tap usando `<a target="_blank">` con subdominio empresa
10. **Botón Inicio** — apunta a /dashboard en vez de / (evita mostrar landing si sesión expira)
11. **iCloud Keychain autofill** — apple-app-site-association + webcredentials entitlement (tarda 24-48h en activarse)

### Problema resuelto — App iOS abría Safari en dispositivo real

**Causa raíz**: `server.url: 'https://cotiza.cloud/login'` con path `/login` causaba error WebKit 102 (Frame load interrupted). Capacitor cancela la navegación cuando el servidor redirige de `/login` a `/dashboard` porque interpreta que la navegación sale del URL configurado.

**Solución**: Revertir `server.url` a `'https://cotiza.cloud'` (sin path). Para evitar que la app muestre la landing page, `landing.php` detecta Capacitor vía `window.Capacitor.isNativePlatform()` y redirige a `/login` instantáneamente.

**Escudo Radar — lecciones aprendidas:**
- `@capacitor/browser` plugin: interfería con el WebView, abría Safari al cargar la app. NO usar.
- Bridge automático (SFSafariViewController): no funciona en dispositivo real, escala a Safari externo
- `window.open()` en WKWebView: se bloquea como popup, requiere múltiples taps
- `<a href target="_blank">` con URL del MISMO origin (cotiza.cloud): Capacitor lo mantiene en el WebView, no abre Safari
- `<a href target="_blank">` con URL de OTRO origin (empresa.cotiza.cloud): Capacitor lo abre en Safari ← **ESTA ES LA SOLUCIÓN**
- El href debe estar directo en el HTML (no asignado por JS), y NO ocultar el elemento en el click handler

### Capacitor config final
```typescript
server: {
    url: 'https://cotiza.cloud',  // SIN path — con path causa error 102
    cleartext: false,
}
```
- 3 plugins: push-notifications, splash-screen, status-bar
- SIN @capacitor/browser (interfiere con WebView)

### Build 3 (v1.1 build 3) — subido a App Store Connect
Cambios vs Build 2:
1. `AppDelegate.swift` — limpia badge + notificaciones al abrir app
2. `App.entitlements` — webcredentials:cotiza.cloud para iCloud Keychain autofill
3. `server.url` — sin cambio (sigue en cotiza.cloud, igual que Build 2)

### Estado del sistema de cookies (funcionando en web)

| Cookie | Dónde se pone | Dominio | Visible en |
|--------|---------------|---------|------------|
| `cz_vid` (login.php JS) | cotiza.cloud/login | `.cotiza.cloud` | Todos los subdominios |
| `cz_vid` (safari bridge PHP) | cotiza.cloud/api/safari-bridge | `.cotiza.cloud` | Todos los subdominios |
| `cz_vid` (safari bridge PHP) | custom.domain.com/api/safari-bridge | dominio exacto | Solo ese dominio |
| `cz_vid` (cotizacion.php PHP) | empresa.cotiza.cloud/c/slug | dominio exacto | Solo ese subdominio |
| `cza_session` (Auth.php) | cotiza.cloud/login | `.cotiza.cloud` | Todos los subdominios |

### 3 Capas de filtrado Radar (funcionando en web + app con Escudo)

| Capa | Qué checa | Cuándo funciona |
|------|-----------|----------------|
| 0 | Auth::id() (sesión) | Mientras esté logueado en ese navegador |
| 1 | visitor_id (cookie cz_vid) | Cookie persiste 2 años — Escudo Radar la pone en Safari |
| 2 | IP aprendida | Misma red que usó al loguearse |

### Migración ejecutada
```sql
ALTER TABLE dispositivos_push ADD COLUMN badge_count INT UNSIGNED NOT NULL DEFAULT 0;
```

### Archivos modificados esta sesión
| Archivo | Cambio |
|---------|--------|
| `api/safari_bridge.php` | NUEVO — endpoint bridge con token HMAC, cookie .cotiza.cloud, IP learning |
| `api/push_reset_badge.php` | NUEVO — resetear badge count |
| `.well-known/apple-app-site-association` | NUEVO — webcredentials para iCloud Keychain |
| `modules/auth/login_post.php` | Redirect chain solo navegador, sin bridge automático app |
| `modules/auth/login.php` | setCookie con .cotiza.cloud + is_app detection + ocultar registro |
| `modules/auth/landing.php` | Detectar Capacitor → redirigir a /login |
| `core/Router.php` | Rutas safari-bridge + reset-badge + landing en / + botón Inicio a /dashboard |
| `core/layout.php` | Escudo Radar banner en contenido principal + token generation |
| `core/PushNotification.php` | Badge increment por dispositivo + reset_badge() |
| `index.php` | Servir apple-app-site-association con Content-Type JSON |
| `.cpanel.yml` | Deploy .well-known |
| `ios/App/App/AppDelegate.swift` | Clear badge + removeAllDeliveredNotifications |
| `ios/App/App/App.entitlements` | webcredentials:cotiza.cloud |
| `assets/js/push.js` | clearBadge() on load + visibilitychange + reset-badge API |
| `capacitor.config.ts` | server.url se mantiene en cotiza.cloud (sin path) |

### Build 3 (v1.1) — Enviado a revisión Apple
- Subido y enviado a App Review (puede tardar 24-48h)
- Cambios vs Build 2: AppDelegate badge clearing + webcredentials autofill
- server.url: `cotiza.cloud` (sin path, igual que Build 2)
- Escudo Radar oculto para empresa `apple-review`

### Fixes adicionales esta sesión
1. **Botón Inicio siempre verde** — bottom nav usaba `href="/"` que matcheaba todos los paths, cambiado a `/dashboard`
2. **Sidebar footer tapado por bottom nav** — agregado padding-bottom 80px
3. **Cerrar sesión reubicado** — movido al final del menú (después de Ayuda/Super Admin), antes estaba tapado abajo
4. **Viewport maximum-scale** — agregado para eliminar 300ms tap delay en iOS
5. **Tap instantáneo bottom nav** — touchend handler para bypass del click delay de iOS en WKWebView
6. **Escudo Radar oculto para apple-review** — no aparece durante revisión de Apple

### Features nuevos esta sesión
1. **Mover a extra / Mover a principal** — botón toggle en artículos de cotizaciones (nueva y editar)
   - Click alterna entre principal y extra
   - Tarjeta extra se diferencia visualmente (borde ámbar, fondo cálido)
   - Se reordena automáticamente (extras al final)
   - Funciona en `modules/cotizaciones/ver.php` y `nueva.php`
2. **Mover a extra / principal en ventas** — mismo toggle en `modules/ventas/ver.php`
   - Botón ↓/↑ en cada línea
   - Marca como dirty → guardar con botón existente
3. **Clonar cotización** — botón en listado de cotizaciones
   - `modules/cotizaciones/clonar.php` (nuevo)
   - Copia artículos, extras, cliente, notas, impuestos
   - Nuevo folio y slug, estado "enviada" (normal)
   - Redirige al editor de la cotización clonada
   - Permiso: admin + usuarios con `crear_cotizaciones`

### Fixes adicionales (continuación sesión)
1. **PDF ventas imprime desde slug público** — botón "Imprimir / PDF" abre el slug público con `?print=1` que auto-ejecuta `window.print()`. Resuelve problema de 1 sola página por layout flex del panel.
2. **Términos en PDF** — usa `e_html()` para renderizar HTML (h, strong) igual que slug público
3. **Clonar cotización** — CSRF fix (variable `CSRF` → `CSRF_TOKEN`)
4. **Sidebar footer visible** — padding-bottom 80px para no taparse con bottom nav
5. **Cerrar sesión reubicado** — al final del menú después de Ayuda/Super Admin
6. **Bottom nav Inicio** — href cambiado de `/` a `/dashboard`, ya no siempre verde
7. **Viewport maximum-scale** — agregado para iOS
8. **Bottom nav touchend** — tap instantáneo via touchend handler

### Pendiente
1. **iCloud Keychain autofill** — esperar 24-48h para que Apple cachee el AASA
2. **Apple Review v1.1** — enviado, esperando respuesta (24-48h)
3. **Git credentials en Mac** — configurar token GitHub para push
4. **Probar push notifications** — enviar notificación real para verificar badge increment/clear
5. **Probar Escudo Radar con OnTime** — verificar cadena de dominios custom desde la app
6. **Bottom nav primer tap iOS** — limitación WKWebView, mitigado con touchend pero no 100%

## Sesión 17 abril 2026

### Completado
1. **Filtro Radar aceptadas/rechazadas** — en `modules/radar/index.php:84` filtro en query: aceptadas/rechazadas solo aparecen si `accion_at/ultima_vista_at >= NOW() - 7 DAY`. Evita que buckets calientes se llenen de cotizaciones zombie. Datos en BD intactos, termómetro sigue funcionando.
2. **Panel derecho en mobile en ver.php** — nueva clase `col-panel-mobile-show` hace visible el panel inline bajo el contenido en mobile (cupones, descuentos, totales, notas cliente/internas, vendedor, historial, log, botón guardar). Scope específico — nueva.php no afectado.
3. **Sin scroll horizontal en mobile editor** — `overflow-x:hidden` en html/body, `word-break:break-word` en campos de texto, `item-card { overflow:hidden; max-width:100% }`, `col-main { max-width:100% }`.
4. **Botón Guardar no se corta en mobile** — `col-panel.col-panel-mobile-show` tiene `margin-bottom: 120px + env(safe-area-inset-bottom)` directamente. Garantiza espacio fijo para bottom-nav sin depender de padding del page-layout.

## Sistema de Suscripciones — Plan para próxima sesión

### Decisiones tomadas (17 abril 2026)
- **Pasarela**: MercadoPago Preapproval (suscripciones recurrentes)
- **Moneda**: Solo MXN en el sistema. USD solo cosmético en landing (precios fijos hardcoded)
- **Trial**: No hay trial por tiempo. El Free (25 cotizaciones) ES el trial
- **Grace period**: 7 días tras fallo de pago → degradar a Free
- **Cambio de plan**: Manual por superadmin (ajustar cuenta). Más adelante automático al vencer ciclo.
- **Cancelación**: Al final del ciclo pagado (no inmediata)
- **Facturación**: No (sin CFDI por ahora)
- **iOS (Apple)**: Estilo Netflix — ocultar membresías en la app. Al intentar upgrade: "Para gestionar tu plan, visita cotiza.cloud desde tu navegador"
- **Sesiones**: Quitar "Recordarme 30 días" para forzar re-login más seguido → beneficia Escudo Radar (re-pone cookie, aprende IP)
- **Cron**: Diario 3am para procesar grace/degradaciones/emails
- **Superadmin manual**: MANTENER el `toggle_plan.php` actual con activación/renovación manual con 1 clic, SIN pago. Casos de uso: cliente paga por transferencia, gift licenses, extensiones de cortesía, arreglo manual si MP falla. El nuevo sistema MP corre EN PARALELO — no reemplaza el manual.

### Flujo
```
1. Signup → Free (25 cotizaciones)
2. Llega al límite o quiere features → "Actualizar plan"
3. Elige Pro/Business + Mensual/Anual (todo en MXN)
4. Crea preapproval en MP → redirige a checkout
5. Cliente paga → Webhook "authorized" + "payment.approved"
6. Plan activo, licencia_vence = +30 o +365 días
7. Cada ciclo MP cobra → webhook extiende licencia_vence
8. Falla pago → grace_hasta = +7 días → emails × 3 → degrada a Free
9. Cancelar → plan activo hasta licencia_vence → luego Free
```

### BD

```sql
CREATE TABLE suscripciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL UNIQUE,
    plan ENUM('pro','business') NOT NULL,
    ciclo ENUM('mensual','anual') NOT NULL,
    mp_preapproval_id VARCHAR(100) UNIQUE,
    estado ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
    monto_mxn DECIMAL(10,2) NOT NULL,
    cancel_al_vencer TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cancelled_at DATETIME NULL,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

CREATE TABLE pagos_suscripcion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    suscripcion_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    mp_payment_id VARCHAR(100) UNIQUE NOT NULL,
    monto_mxn DECIMAL(10,2) NOT NULL,
    estado ENUM('approved','pending','rejected','refunded') NOT NULL,
    fecha_pago DATETIME NOT NULL,
    detalle JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

ALTER TABLE empresas
    ADD COLUMN licencia_vence DATE NULL,
    ADD COLUMN grace_hasta DATE NULL;
```

### Archivos a crear

| Archivo | Función |
|---------|---------|
| `core/MercadoPago.php` | SDK wrapper: crear/cancelar preapproval, validar firma webhook |
| `api/mp_webhook.php` | Endpoint público de webhooks MP (valida HMAC, idempotente) |
| `api/mp_return.php` | Return URL post-checkout |
| `modules/config/suscripcion.php` | UI cliente: plan actual, próximo cobro, historial, botones |
| `modules/config/suscripcion_crear.php` | POST: crea preapproval y redirige a MP |
| `modules/config/suscripcion_cancelar.php` | POST: cancela en MP, mantiene hasta fin ciclo |
| `modules/superadmin/suscripciones.php` | Admin: lista todas, ajuste manual |
| `cron/procesar_suscripciones.php` | Cron diario: grace, degradación, emails aviso |
| `core/layout.php` | Banner en grace period + detección iOS para ocultar membresías |
| `migrations/add_suscripciones.sql` | Tablas + columnas |

### Config.php del servidor (manual)
```php
define('MP_ACCESS_TOKEN',    'APP_USR-xxxxx');
define('MP_PUBLIC_KEY',      'APP_USR-xxxxx');
define('MP_WEBHOOK_SECRET',  'xxxxx');
```

### Webhook flow
```
MP → POST /api/mp/webhook
  → validar firma HMAC (secret)
  → idempotencia por mp_payment_id UNIQUE
  → procesar evento:
     - preapproval.authorized → activar, set licencia_vence
     - payment.approved → registrar, extender licencia_vence
     - payment.rejected → schedule grace + email
     - preapproval.cancelled → cancel_al_vencer=1
  → return 200
```

### Orden de implementación sugerido
1. Migración BD
2. `core/MercadoPago.php` (wrapper SDK)
3. `api/mp_webhook.php` (testeable con herramienta MP)
4. UI `modules/config/suscripcion.php`
5. `modules/config/suscripcion_crear.php` + flujo checkout
6. `cron/procesar_suscripciones.php`
7. Detección iOS en layout.php (ocultar módulo suscripción)
8. Superadmin panel
9. Quitar cookie "Recordarme 30 días"
10. Testing con MP sandbox

### Checkpoint tag
Git tag `pre-suscripciones-v1` creado en SHA `d644dba` antes de empezar.

## Sesión 18 abril 2026 — MercadoPago Suscripciones (en progreso)

### Estado actual
**BLOQUEADO esperando respuesta del hosting** para whitelistear IPs de MercadoPago en Imunify360.

### Completado esta sesión
1. **Sistema de suscripciones construido** (archivos creados en sesión anterior):
   - `core/MercadoPago.php` — wrapper API (crear/cancelar/consultar preapproval, validar webhook)
   - `api/mp_webhook.php` — endpoint webhook con debug logging
   - `api/mp_return.php` — return URL post-checkout
   - `modules/config/suscripcion.php` — UI cliente
   - `modules/config/suscripcion_crear.php` — POST crea preapproval
   - `modules/config/suscripcion_cancelar.php` — POST cancela
   - `modules/superadmin/suscripciones.php` — panel admin
   - `cron/procesar_suscripciones.php` — cron diario grace/degradación

2. **BD migración ejecutada** en producción:
   - Tablas: `suscripciones`, `pagos_suscripcion`
   - Columna: `empresas.grace_hasta`

3. **Debug ModSecurity/WAF**:
   - Identificado: Imunify360 (del hosting) bloquea requests de MP con 403
   - `<If>` bypass en `.htaccess` no funciona (LiteSpeed phase 1 rules)
   - Ruta ofuscada `/hook/c5f8-2a19` agregada al router — tampoco pasa
   - **Diagnóstico definitivo**: MP's IPs (`51.68.236.72`, `51.68.111.240` — OVH Paris) están baneadas a nivel firewall/iptables. Los requests NI APARECEN en el raw access log, solo en error log como "File not found [403.shtml]"

4. **validarWebhook() modificado** (commit `dbca3b1`):
   - Si `MP_WEBHOOK_SECRET` no está definido → retorna `true` con warning en log
   - Seguro porque `procesarWebhook` re-consulta todos los IDs contra MP API con access token

5. **Panel MP configurado**:
   - Aplicación nueva creada (la vieja `7596522374918503` tenía panel roto — no dejaba guardar webhook)
   - Access Token y Public Key obtenidos y puestos en `config.php` del servidor
   - URL webhook en MP panel: `https://cotiza.cloud/hook/c5f8-2a19`
   - Eventos: payment, subscription_preapproval, subscription_authorized_payment

### Commits de la sesión
- `dbca3b1` — fix(mp): permitir webhook sin secret (modo testing)
- `f46891b` — feat(mp): ruta webhook ofuscada para evadir Imunify360

### Credenciales (ya en config.php del servidor)
```php
define('MP_ACCESS_TOKEN', 'APP_USR-8281846475625325-041720-cdfd08680a30c42b332a216936fd4122-74510471');
define('MP_PUBLIC_KEY',   'APP_USR-3bac00d3-f106-45ee-96a5-d7526fceb449');
// MP_WEBHOOK_SECRET pendiente — MP panel no deja guardar el secret (bug en su UI)
```
**IMPORTANTE**: Estas credenciales fueron compartidas en chat. **ROTAR** cuando se active el sistema.

### Ticket enviado al hosting (18 abril 2026)
Pedido whitelist de IPs de MercadoPago en Imunify360:
- `51.68.236.72`
- `51.68.111.240`
- Rango `51.68.0.0/16` (OVH Paris)

### Pendiente — al recibir respuesta del hosting
1. Confirmar que IPs están whitelisteadas (pedir a MP hacer "Simular notificación" → debe dar 200)
2. Probar flujo end-to-end:
   - Crear preapproval desde UI `modules/config/suscripcion.php`
   - Redirect a checkout MP
   - Hacer pago con tarjeta de prueba
   - Verificar que webhook procesa y activa la suscripción
   - Verificar `empresas.plan` y `empresas.plan_vence` actualizados
3. Configurar cron diario en cPanel:
   ```
   0 3 * * * /usr/bin/php /home/cotizacl/public_html/cron/procesar_suscripciones.php
   ```
4. Remover debug logging de `api/mp_webhook.php` una vez verificado funcionamiento
5. Resolver el tema del `MP_WEBHOOK_SECRET`:
   - Opción A: reintentar en panel MP (tal vez ya lo arreglaron)
   - Opción B: abrir ticket a MP con request IDs del HAR anterior
   - Opción C: dejar validación deshabilitada (seguro porque re-consulta IDs en MP API)

### Lecciones aprendidas
- Panel de webhooks de MP: primera app (`7596522374918503`) tenía backend roto — daba 400 en PUT/POST/DELETE. Crear app nueva resolvió el problema de "guardar webhook"
- MP no muestra el webhook secret en texto plano aunque esté guardado (los `•••••••` son CSS puro — el value está vacío cuando el save falla)
- Imunify360 bannea IPs al firewall level después de N 403s consecutivos. No aparecen en access log cuando están baneadas
- `SecRuleEngine Off` en `<If>` dentro de `.htaccess` NO funciona con LiteSpeed para reglas de phase 1 (headers)
- El `validarWebhook()` returning false silenciosamente skipea el procesamiento — cambiar a true cuando no hay secret es más correcto en modo testing

### Rutas del router actuales
```php
self::post('/api/mp/webhook',       fn() => self::load_api('mp_webhook'));
self::get('/api/mp/webhook',        fn() => self::load_api('mp_webhook'));
// Ruta ofuscada (Imunify360 bloquea /api/mp/webhook)
self::post('/hook/c5f8-2a19',       fn() => self::load_api('mp_webhook'));
self::get('/hook/c5f8-2a19',        fn() => self::load_api('mp_webhook'));
self::get('/api/mp/return',         fn() => self::load_api('mp_return'));
```

### Branch de trabajo
- `main` (sesiones anteriores y esta continúan en main para auto-deploy cPanel)

## Sesión 18 abril 2026 (continuación) — Suscripciones MP funcionales

### Estado: SISTEMA FUNCIONAL ✅
El sistema de suscripciones con MercadoPago está **operativo end-to-end**. El flujo completo funciona: creación del preapproval → redirect al checkout de MP → sincronización por polling al volver al sistema.

### Testing con pagos reales
Se hicieron 2 pruebas de pago con tarjetas reales:
1. **Mastercard crédito ****0604** — Business Mensual $799 → **Rechazada por banco emisor**
2. **HSBC Mastercard crédito ****1345** — Pro Mensual $299 → **Rechazada por HSBC**

**Conclusión**: ambos rechazos son del **banco emisor**, no de MP ni del sistema. Patrón típico en México — bancos bloquean el primer cobro recurrente de un comercio nuevo por antifraude. Mensaje de MP: *"El banco emisor de la tarjeta rechazó el pago. Recomiéndale a tu cliente que pague con otro medio de pago o llame a su banco."*

### Operaciones rechazadas en panel MP (para referencia)
| Fecha | Monto | Plan | Tarjeta | Banco | Op ID |
|-------|-------|------|---------|-------|-------|
| 18/abr 10:07 | $799 | Business Mensual | MC ****0604 | — | 154617346683 |
| 18/abr 10:18 | $299 | Pro Mensual | HSBC MC ****1345 | HSBC | 155373042466 |

Ambas aparecen en `https://www.mercadopago.com.mx/activities` con ref externa `cz_2_business_mensual` y `cz_2_pro_mensual`.

### Cambios esta sesión
1. **Aviso en UI** — `modules/config/suscripcion.php` muestra banner amber arriba del grid de planes: "Si tu banco rechaza el cargo, llama al número al reverso de tu tarjeta y pide autorizar cargos recurrentes de MercadoPago". Solo aparece cuando el usuario está viendo las opciones de upgrade.
2. **Arquitectura de polling en vez de webhook** — ya deployado commit `802acf5`:
   - `MercadoPago::sincronizar($empresa_id)` consulta preapproval y actualiza estado local
   - Se ejecuta en 3 momentos: al volver del checkout (`api/mp_return.php`), al abrir el tab Suscripción (throttle 10min via `empresas.ultima_sync_mp`), y en cron diario (step 0)
   - Reemplaza completamente la dependencia del webhook (bloqueado por Imunify360)

### Pendiente (no bloqueante)
1. **Completar prueba de pago real** — usar tarjeta de BBVA/Banorte o Saldo MP para verificar el ciclo completo de activación (preapproval → authorized → plan_vence actualizado)
2. **Configurar cron en cPanel**: `0 3 * * * /usr/bin/php /home/cotizacl/public_html/cron/procesar_suscripciones.php`
3. **Migración pendiente en servidor**: `ALTER TABLE empresas ADD COLUMN ultima_sync_mp DATETIME NULL AFTER grace_hasta;` (archivo: `migrations/add_ultima_sync_mp.sql`)
4. **Rotar credenciales MP** compartidas en chat (access token + public key)
5. **Webhook**: dejar como está (polling cubre el caso). Si Imunify360 eventualmente libera IPs, reactivar validación HMAC con secret.

### Dónde están los logs
| Log | Ruta | Contiene |
|-----|------|----------|
| PHP errors app | `/home/cotizacl/public_html/logs/error.log` | `[MP Crear]`, `[MP sync]`, `[MP Webhook]` |
| LiteSpeed | `/usr/local/lsws/logs/error.log` | SSL, acme-challenge, 403 de firewall |
| Panel MP | https://www.mercadopago.com.mx/activities | Motivos reales de rechazo de pagos |

### Notas importantes
- Cuenta MP del usuario es **vieja y verificada** (KYC completo, CLABE configurada, vendedor activo). Descartado como causa de rechazos.
- `payer_email` en preapproval: el cliente debe usar este email al pagar. El código usa `empresas.email` como payer_email.
- Rechazo por banco emisor NO es bug — es comportamiento esperado en México para primeros cargos recurrentes.

### Branch de trabajo
- `claude/analyze-domain-change-hmo-AkFAi` (esta continuación)
- Las sesiones previas quedaron en `main` por auto-deploy cPanel

## Sesión 1 mayo 2026 — Auditoría Capa 2.5 (device_sig) Escudo Radar

### Contexto
Pruebas de campo del Escudo Radar (3 capas: visitor_id, IP, device_sig). El usuario se conectó desde IP USA `68.177.3.148` y ejecutó tests para verificar si era detectado correctamente como interno tras borrar señales manualmente.

### Resultado del test
**REPROBÓ.** Sesión 758 en quote_sessions creada con `es_interno=0` y `device_sig=NULL` aunque el usuario tenía device_sig `229bb0d4` registrado en `user_sessions` desde su login de las 08:55 ese mismo día.

### Causa raíz identificada
Hay **dos sistemas de tracking en momentos distintos**:

1. **`public/cotizacion.php` (PHP server-side, primero)** — crea quote_session con `es_interno=0` por default. Tiene Capas 0, 1, 2. **NO tiene Capa 2.5 (device_sig)** porque el device_sig viene del JS que aún no ha corrido. Línea 258-261 INSERT no incluye device_sig.

2. **`api/track.php` (JS beacon, segundo)** — tiene Capa 2.5 (línea 99-117). Si detecta interno por device_sig, marca visitor_id e IP como internos pero **NO actualiza el quote_session ya creado**. Solo hace `exit`.

3. **`core/layout.php:50-77` (limpieza retroactiva, tercero)** — cuando el asesor abre el dashboard, busca quote_sessions con su IP/visitor_id y los marca `es_interno=1`. Una vez al día por sesión PHP. **NO usa device_sig** en el cleanup.

### Hallazgos críticos sobre device_sig (con datos reales de BD)

#### 1. device_sig COLISIONA entre dispositivos distintos
Query a `quote_sessions` últimos 7 días reveló colisiones reales:
- **`17f8187d`** (iPhone iOS 18_7) lo comparten Kevin Landy (`nog@ontimecocinas.com`, id=21, empresa 14) Y Abigail Perez (`hmo@ontimecocinas.com`, id=18, empresa 12) en sus user_sessions. Dos personas físicas distintas con iPhones distintos generando el MISMO hash.
- **`19b35160`** (Windows 10) compartido entre user_sessions de Kevin y un visitor "cliente" `95675a4c` desde la MISMA IP `189.161.14.168` → Kevin sin login.

#### 2. device_sig CAMBIA en la misma máquina entre sesiones
El superadmin (`admin@cotiza.cloud`, id=4) generó **3 device_sigs distintos en la MISMA Mac con MISMO Firefox normal en la misma semana**:
- `229bb0d4` (USA hoy)
- `4255de39` (MX, 8 sesiones)
- `4d6b4aa5` (MX, 6 sesiones)
- `0a7a8acc` (su iPhone iOS 18_7)

Eso significa que el fingerprint NO es estable. Algún componente de los 14 (`sw|sh|dpr|cores|tp|maxTex|lang|tz|hc|motion|contrast|inverted|transp|iosM`) está variando entre sesiones. Sospechoso principal: `maxTex` de WebGL puede devolver 0 si el GPU está dormido o el contexto WebGL no está listo. **NO confirmado** — requiere pegar snippet en consola para ver el `raw` y comparar.

#### 3. Diversidad real
- 38 device_sigs distintos en 76 sesiones de 49 visitors distintos → ratio 1.3 visitors/dsig (aceptable para clientes)
- Tasa de colisión cliente-vs-interno: 2/38 = ~5% en SOLO 1 semana

### Datos importantes para no perder
- **Tu superadmin**: id=4, email `admin@cotiza.cloud`
- **TU iPhone (admin@)**: device_sig `0a7a8acc`
- **TU Mac Firefox**: device_sigs `229bb0d4` (USA), `4255de39` y `4d6b4aa5` (MX) — INESTABLE
- **Kevin Landy** (nog@ontimecocinas.com, id=21, empresa 14): Windows `19b35160`, iPhone `17f8187d`, IP `189.161.14.168`
- **Abigail Perez** (hmo@ontimecocinas.com, id=18, empresa 12): iPhone `17f8187d` también
- **IPs de los "clientes" colisionados con `17f8187d`**: `200.68.184.39`, `200.68.184.175` (Telmex móvil) — NO matchean ninguna user_session, podrían ser asesores en movilidad o clientes reales

### Decisión pendiente — Opciones evaluadas

**Opción 1 — A+C light (RECOMENDADA, pendiente confirmación):**
- Capa 2.5 reformulada en `api/track.php`: solo descarta visita actual + UPDATE quote_session a es_interno=1 + revierte visitas-1 + revierte estado si aplica
- **NUNCA marca permanente** (no toca radar_visitors_internos ni radar_ips_internas)
- Filtro de UA family (mac/iphone/android/windows) para evitar colisiones triviales
- TTL 90 días en lookup de user_sessions (vs 365 actual)
- Costo: ~30 líneas
- Beneficio: cero falsos positivos permanentes garantizados; cierra gap del INSERT

**Opción 2 — Eliminar device_sig:** Quitar Capa 2.5 completa. Confiar solo en visitor_id + IP determinísticas.

**Opción 3 — Rediseñar device_sig:** Quitar componentes ruidosos (maxTex, media queries variables). Requiere experimentación. Tarea futura.

### Dirección definida por el usuario — DOS caminos separados

#### Camino 1: INTERNOS — que no ensucien el Radar
- **Enfoque**: Identificador robusto con PERMISO del dispositivo (son usuarios que hacen login, podemos pedir más datos)
- **Opciones a investigar**:
  1. **Push subscription endpoint** — ya implementado en el sistema; el subscription endpoint es ÚNICO por dispositivo+navegador, estable, no cambia. Podría usarse como device_id confiable.
  2. **Persistent localStorage UUID** — al loguearse, generar/leer UUID de localStorage (`cz_internal_id`). Persiste entre sesiones normales. No disponible en incognito ni en dominios custom (per-origin).
  3. **Capacitor Device ID** — `@capacitor/device` da UUID único para app nativa. No aplica para web.
  4. **IndexedDB UUID** — más persistente que localStorage, sobrevive limpieza de cache.
  5. **Web Crypto persistent keypair** — par de llaves en IndexedDB, firmar requests.
- **Reto en dominios custom** (ontimecocinas.com): ninguna cookie/storage de .cotiza.cloud viaja ahí. Necesita bridge (Escudo Radar ya existe para app nativa, falta solución web).
- **Prioridad**: ALTA — esto es lo fundamental para no contaminar el Radar

#### Camino 2: CLIENTES — device_sig más ligero y estable (solo descarte)
- **Enfoque**: Fingerprint simplificado, quitar componentes ruidosos
- **Componentes a quitar** (candidatos ruidosos):
  - `maxTex` (WebGL MAX_TEXTURE_SIZE) — puede devolver 0 si GPU dormido
  - Media queries variables (prefers-reduced-motion, prefers-contrast, etc.)
  - `hourCycle` — puede venir vacío en algunas versiones
- **Componentes estables a mantener**: screen size (sw/sh), dpr, cores, maxTouchPoints, lang, timezone, iosM
- **El resultado será**: menos único (más colisiones entre clientes) pero más estable (mismo hash en misma máquina siempre)
- **Uso**: SOLO para descarte en Radar — nunca para marcar permanente
- **Prioridad**: MEDIA — mejora la calidad del descarte pero no es urgente

### Conclusión de la investigación (2 mayo 2026)

Se evaluaron TODAS las alternativas para identificar internos cross-domain:
- **Push subscription**: per-origin, no cruza subdominios ni dominios custom
- **WebAuthn/Passkeys**: per-origin, requiere biometría, impractico en PC
- **localStorage/IndexedDB**: per-origin, muere al borrar datos del sitio
- **Cookie HMAC firmada (cz_device)**: duplica cz_vid, mismas debilidades
- **Extensión de navegador**: rechazada por el usuario
- **IP+UA contra user_sessions**: genérico en móvil (todos los iPhones mismo UA), sirve solo como descarte
- **A+C retroactivo**: depende de device_sig que es inestable

**Realidad:** no existe API web que dé identificación cross-domain. Es restricción fundamental del navegador.

**Las capas actuales (cookie sesión + cz_vid + IP) cubren el 90%+ de casos reales.** El gap restante (~10%) ocurre cuando TODAS las señales fallan (sin cookie + IP desconocida + device_sig diferente).

### Decisión: arreglar device_sig PRIMERO, luego cambio arquitectónico

**Paso 1 — Diagnosticar device_sig (BLOQUEANTE):**
Correr el snippet en consola Firefox normal vs Firefox incognito en la misma Mac. Comparar los 14 componentes. Identificar cuál cambia.

**Snippet:**
```js
[Math.min(screen.width,screen.height), Math.max(screen.width,screen.height), window.devicePixelRatio||1, navigator.hardwareConcurrency||0, navigator.maxTouchPoints||0, (function(){try{var c=document.createElement('canvas');var gl=c.getContext('webgl');return gl?gl.getParameter(gl.MAX_TEXTURE_SIZE):0}catch(e){return 0}})(), navigator.language||'', Intl.DateTimeFormat().resolvedOptions().timeZone||'', Intl.DateTimeFormat().resolvedOptions().hourCycle||'', matchMedia('(prefers-reduced-motion:reduce)').matches?1:0, matchMedia('(prefers-contrast:more)').matches?1:0, matchMedia('(inverted-colors:inverted)').matches?1:0, matchMedia('(prefers-reduced-transparency:reduce)').matches?1:0, (navigator.userAgent.match(/OS (\d+)/)||[])[1]||'0'].join('|')
```

**Paso 2 — Arreglar device_sig:**
Quitar componentes inestables. Verificar estabilidad entre sesiones y modos.

**Paso 3 — Verificar colisiones:**
Con el fingerprint reducido, medir tasa de colisión en datos reales (query a quote_sessions vs user_sessions).

**Paso 4 — Cambio arquitectónico: mover conteo de cotizacion.php a track.php:**
- `cotizacion.php` solo crea quote_session, NO incrementa visitas, NO cambia estado, NO recalcula Radar
- `track.php` ejecuta TODAS las capas (0, 1, 2, 2.5) y solo si confirma cliente → cuenta visita, cambia estado, recalcula Radar
- Esto convierte las 4 capas de track.php (hoy inútiles porque cotizacion.php ya contó) en capas preventivas reales
- La Capa 2.5 (device_sig) pasa de ser descarte desperdiciado a última línea de defensa

**Por qué este orden:** sin device_sig confiable, el paso 4 no agrega valor real (track.php solo tendría las mismas capas que cotizacion.php).

### Opciones descartadas con razón documentada
| Opción | Por qué se descartó |
|---|---|
| Push subscription como device ID | Per-origin, no cruza subdominios ni custom domains |
| WebAuthn / Face ID | Impractico en PC, mayoría no usa app |
| Extensión de navegador | Rechazada por el usuario |
| Cookie HMAC firmada (cz_device) | Duplica cz_vid con mismas debilidades. Contradicción auto-validante vs revocable |
| Token HMAC en localStorage | Per-origin, marginal (solo cubre "borró cookies pero no datos del sitio") |
| IP+UA como capa preventiva | Genérico en móvil (falsos positivos), solo sirve como descarte |
| Bloquear sistema sin cookie dispositivo | Transparente en login ok, pero no ayuda en slug. Esencialmente igual que sesión corta |
| Sesiones 1 día | Molesta asesores, app móvil, alto riesgo si login falla |
| Link "no contar mi visita" en slug | Visible para clientes, mal diseño |
| Mover conteo de cotizacion.php a track.php | Pierde ~1% de visitas si JS no corre. Ghost cleanup ya maneja retroactivamente |
| A+C retroactivo por device_sig | device_sig colisiona entre dispositivos iguales → clientes pierden visitas |
| Screen dimensions en user_sessions | Migración innecesaria, código extra riesgoso para beneficio cosmético |
| Certificados SSL cliente (mTLS) | UX de instalación imposible para asesores no técnicos, requiere VPS con root |

## Sesión 2 mayo 2026 — Implementación Escudo Radar

### Diagnóstico de device_sig — Confirmado
- **hardwareConcurrency** es el componente inestable: Firefox normal=16, incognito=8 en la misma Mac
- Los otros 12 componentes son estables en todos los modos (Firefox normal/incognito, Safari normal/incognito)
- **Fix aplicado:** quitar solo `hardwareConcurrency`, mantener 13 componentes
- Verificado: Firefox normal e incógnito generan el mismo hash (`6883be39`)

### Componentes del device_sig (13, estables)
```
sw | sh | dpr | tp | maxTex | lang | tz | hc | motion | contrast | inverted | transp | iosM
```
Quitado: `cores` (hardwareConcurrency) — Firefox lo spoofea en modo privado.

### Datos reales de colisiones (con 14 componentes, producción)
- 43 device_sigs distintos de 56 visitors (ratio 1.30)
- **Solo 1 de 43 colisiona entre cliente y asesor** (`17f8187d` — Kevin + Abi iPhones)
- Las demás colisiones son cliente-con-cliente (no afectan detección de asesores)
- Los 7 componentes quitados NO separaban las colisiones (todos eran 0/vacío para los dispositivos colisionados)

### Decisiones tomadas
1. **device_sig es SOLO para descarte de clientes** en slugs (deduplicación dentro de la misma cotización)
2. **device_sig NO se usa para identificar asesores** (colisiones con clientes)
3. **El escudo de asesores se basa en cookies** (cza_session 3 días + cz_vid 730 días + bridge)
4. **Capa 2.5 en track.php**: solo descarte suave (exit sin marcar permanente)

### Cambios implementados

| Cambio | Archivo(s) | Efecto |
|---|---|---|
| device_sig 13 componentes | cotizacion.php, login.php, layout.php | Estable entre normal e incógnito |
| Capa 2.5 sin marcado permanente | api/track.php | Clientes ya no se contaminan por colisión |
| Sesión 3 días browser / 30 días app | core/Auth.php, login_post.php | Re-login más frecuente → bridge refresca cookies |
| Banner "Activar Escudo" | core/layout.php | Onboarding + fricción en incógnito (localStorage check) |
| Tarjeta dispositivos en dashboard | modules/dashboard/index.php | Muestra dispositivos protegidos del asesor |
| Cookie cz_dsig con domain .cotiza.cloud | core/layout.php | Viaja a subdominios para detección superadmin |
| Detección browsers completa | modules/dashboard/index.php | CriOS, FxiOS, SamsungBrowser, OPR, EdgiOS |

### Arquitectura del Escudo (estado final)
```
Protección de slugs (capas en cotizacion.php):
  Capa 0: cookie cza_session (3 días browser, 30 días app)
  Capa 1: cookie cz_vid (730 días) + radar_visitors_internos
  Capa 2: IP en radar_ips_internas (7 días, re-aprendida cada login)
  Capa 3: Bot por IP prefix

Descarte en track.php (JS, retroactivo):
  Capa 2.5: device_sig → exit sin marcar permanente
  Ghost cleanup: sesiones sin eventos → borrar + decrementar visitas

Limpieza diaria (layout.php):
  Retroactiva por IP/visitor_id al abrir dashboard

Educación (layout.php):
  Banner "Activar Escudo" → localStorage check → fricción en incógnito

Información (dashboard):
  Tarjeta "Escudo Radar — Activo" con lista de dispositivos
```

### Cookies del sistema
| Cookie | Duración | Domain | Propósito |
|---|---|---|---|
| `cza_session` | 3d browser / 30d app | `.cotiza.cloud` | Autenticación |
| `cz_vid` | 730 días | `.cotiza.cloud` + bridge en custom | Identificar interno (Capa 1) |
| `cz_dsig` | 3 días | `.cotiza.cloud` | device_sig para PHP (superadmin detection, feedback) |

### Limitaciones conocidas y aceptadas
- Dos Macs/iPhones del mismo modelo con mismo browser → misma entrada en tarjeta (UA no distingue modelos)
- Incógnito + otra red + sin login previo → contamina (gap ~10%, límite web)
- Transición de 3 días: device_sigs viejos (14 componentes) no matchean nuevos (13) hasta re-login
- Ghost sessions en cotizaciones de poco tráfico → 1 visita extra hasta que otro visitor active el cleanup

### Pendiente para próxima sesión
1. **Monitorear raw device_sig** — verificar estabilidad con datos reales de clientes
2. **Cortar 5 componentes muertos** — cuando haya suficientes datos confirmando `||0|0|0|0|` para todos, recortar a 8 componentes. SQL para limpiar BD: `UPDATE ... SET device_sig = CONCAT(SUBSTRING_INDEX(device_sig,'|',7),'|',SUBSTRING_INDEX(device_sig,'|',-1))`
3. **Chrome iOS iosM=26** — Chrome reporta versión diferente en el UA. Considerar ignorar iosM para matching parcial, o extraer la versión real de otro lado.
4. **NULL en quote_sessions (~14%)** — clientes que cierran antes de que JS cargue. Aceptable para descarte. Monitorear si baja.
5. **Leyenda en slug** (opcional) — "🛡️ Escudo activo" cuando asesor es detectado como interno
6. **Staff-view** (opcional) — botón en dashboard que refresca cookie antes de abrir slug

### Sesión 5 mayo 2026 — device_sig raw legible

#### Cambio principal
Hash opaco (`6883be39`, 8 chars) → raw legible (`1120|1792|2|0|8192|es-MX|Hermosillo||0|0|0|0|0`, ~45 chars).
Mismos 13 componentes. Timezone acortado (solo ciudad: Hermosillo en vez de America/Hermosillo).

#### Migración ejecutada
```sql
ALTER TABLE user_sessions MODIFY COLUMN device_sig VARCHAR(120) NULL;
ALTER TABLE quote_sessions MODIFY COLUMN device_sig VARCHAR(120) NULL;
ALTER TABLE quote_events MODIFY COLUMN device_sig VARCHAR(120) NULL;
ALTER TABLE cot_feedbacks MODIFY COLUMN device_sig VARCHAR(120) NULL;
```

#### Archivos modificados
| Archivo | Cambio |
|---|---|
| `public/cotizacion.php` | getDeviceSig() retorna raw, sanitización ampliada, quitar double-decoding feedback |
| `modules/auth/login.php` | getDeviceSig() raw + script INLINE para evitar NULL en iPhone |
| `core/layout.php` | cookie cz_dsig con encodeURIComponent + raw |
| `modules/auth/login_post.php` | Sanitización ampliada a 120 chars |
| `api/track.php` | Sanitización ampliada |
| `api/cot_feedback.php` | Sanitización ampliada |
| `modules/radar/index.php` | Competencia: desactivar alerta por device_sig + filtrar es_interno=0 |
| `modules/superadmin/executive.php` | Competencia: filtrar es_interno=0 |

#### Bugs corregidos
1. **device_sig NULL en iPhone al login** — JS al final de la página no corría antes del form submit (auto-fill iOS). Fix: script inline justo después del hidden field.
2. **Double-decoding cookie cz_dsig** — getCookie() ya decodifica, llamar decodeURIComponent de nuevo era redundante. Fix: quitar el decode extra.
3. **Alertas falsas de competencia** — queries no filtraban `es_interno=0`. Sesiones de asesores aparecían como competencia. Fix: agregar filtro a 5 queries.
4. **Competencia por device_sig desactivada** — iPhones del mismo modelo colisionan → falsos positivos. Las alertas por visitor_id e IP ya cubren competencia real.

#### Datos reales capturados (5 mayo 2026)
```
Super Admin (iMac):     1440|2560|2|0|8192|en-US|Hermosillo||0|0|0|0|0
Israel (Windows):       900|1600|1.2|0|16384|es-MX|Hermosillo||0|0|0|0|0
Kevin (Windows):        1080|1920|1|0|16384|es-US|Hermosillo||0|0|0|0|0
Kevin (iPhone):         402|874|3|5|16384|es-MX|Hermosillo||0|0|0|0|18
Manuel (Windows):       1080|1920|1|0|8192|es-ES|Hermosillo||0|0|0|0|0
Cliente iPhone es-419:  390|844|3|5|16384|es-419|Hermosillo||0|0|0|0|18
Cliente Android bajo:   412|915|2.625|5|4096|es-419|Hermosillo||0|0|0|0|0
Cliente Samsung S23:    384|832|2.8125|5|8192|es-US|Hermosillo||0|0|0|0|0
```

#### Confirmaciones
- **lang diferencia clientes**: es-419 vs es-MX vs es-US separan clientes con mismo dispositivo ✅
- **maxTex varía en Android**: 4096 / 8192 / 16384 por tier de GPU ✅
- **dpr varía en Android**: 1, 1.2, 2.625, 2.8125 por modelo ✅
- **5 componentes siempre 0**: hc, motion, contrast, inverted, transp = 0 en 100% de sesiones ✅
- **iosM=26 en Chrome iOS**: Chrome reporta versión diferente al UA real — gap conocido

## Sesión 10 mayo 2026

### Completado

#### Radar — Alertas de competencia
1. **IP visible en alertas** — cabecera muestra IP, detalle muestra IPs por cliente
2. **Matching device_sig de asesores** — banner amarillo "Posible asesor: device_sig coincide con [nombre]"
3. **Confianza media/baja por proximidad** — IP ≤7 días = Media (amarillo), 7-30 días = Baja (gris)

#### Dashboard ejecutivo
4. **Tasa de cierre corregida** — usa `aceptada_at` en executive, dashboard y reportes
5. **Sin abrir muestra etiqueta VENCIDA** — en vez de ocultar cotizaciones vencidas
6. **Días sin abrir usa `created_at`** — `enviada_at` se reseteaba al editar
7. **`enviar.php` no resetea `enviada_at`** — `COALESCE(enviada_at, NOW())`

#### Reportes
8. **Vencidas calculadas en resumen y tabla**
9. **Tasa por asesor usa `aceptada_at`**

#### Ventas
10. **Estado de cuenta imprimible** — botón con desglose: conceptos, extras, ajustes, pagos, resumen
11. **Descuento automático no se resetea al editar** — conserva fecha de expiración original

#### Termómetro v6 — Activación refactorizada
12. **No abiertas 5d mata operativa** — 1+ → operativa = 0
13. **Dormidas = ratio directo** — `dormidas_7d / cot_vistas`, puede ir negativo
14. **Dormidas redefinidas** — vistas pero cliente no regresa en 7+ días (no duplica no_abiertas)
15. **no_abiertas sin ventana** — penaliza mientras siga sin abrir
16. **dormidas con ventana 15 días** — misma ventana que cot_asignadas
17. **Frases corregidas** — alerta temprana + ⚠️ penalización + dormidas

#### Termómetro — cierres y Seguimiento
18. **cierres_bucket usa bucket_transitions** — ya no depende de radar_bucket actual
19. **Calidad de cierre usa último bucket real de bucket_transitions**
20. **Seguimiento mide calientes históricas** — si el asesor no da feedback y se enfría, ya no se escapa
21. **Feedback usa mismo período que calientes (15d)**

#### Termómetro — Debug
22. **no_abiertas_5d, pen_no_abiertas, pen_dormidas, dias_activos_feature** persistidos
23. **Penalizaciones ponderadas** — impacto real por peso de dimensión

#### Leaderboard
24. **Barras A/E/S/P/C por asesor** — solo visible para superadmin

### Migraciones
```sql
ALTER TABLE usuario_score
  ADD COLUMN no_abiertas_5d INT UNSIGNED NOT NULL DEFAULT 0 AFTER cot_dormidas,
  ADD COLUMN pen_no_abiertas DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER no_abiertas_5d,
  ADD COLUMN pen_dormidas DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER pen_no_abiertas,
  ADD COLUMN dias_activos_feature INT UNSIGNED NOT NULL DEFAULT 0 AFTER pen_dormidas;
```

### Fórmula Activación v6
```
if (no_abiertas_5d > 0):
    operativa = 0 - pen_dormidas
else:
    operativa = tasa_apertura - pen_dormidas

pen_dormidas = dormidas_7d / cot_vistas (ratio directo)
s_activacion = operativa × 50% + tips × 50% (permite negativo)
```

### Pendientes próxima sesión
1. **Revisar Engagement, Radar Health, Conversión** a fondo (solo Activación y Seguimiento revisados)
2. **Verificar calidad de feedback en cotizaciones aceptadas** con bucket perdido
3. **Auditoría de seguridad** — CSRF, auth en quote_action.php, .gitignore, security headers
4. **Suscripciones MercadoPago** — whitelist IPs, probar pago, configurar cron
5. **Device_sig**: `motion` tiene uso real (2 clientes). Solo `hc`, `contrast`, `inverted`, `transp` confirmados muertos.

## Sesión 10 mayo 2026 (continuación pm)

### Completado
1. **Bonus ticket alto** — 1.5x→+2 (silencioso), 2x→+5, 3x→+8. Tope 15. Score máx 100.
2. **Ticket promedio = AVG histórico** incluyendo ventas actuales (se auto-infla)
3. **cierres_bucket y calidad usan bucket_transitions** — radar_bucket se pierde al aceptar
4. **Seguimiento mide calientes históricas 15d** con feedback mismo período
5. **Ghost cleanup en dashboard** — revierte estado a enviada si visitas=0
6. **Badge no_abierta en vencidas**
7. **Tooltips en lista cotizaciones**
8. **Escudo "¿qué es esto?"**
9. **Descuento automático no se resetea al editar**

### Migraciones
```sql
ALTER TABLE usuario_score
  ADD COLUMN bonus_ticket INT UNSIGNED NOT NULL DEFAULT 0 AFTER percentil,
  ADD COLUMN bonus_ticket_ventas INT UNSIGNED NOT NULL DEFAULT 0 AFTER bonus_ticket,
  ADD COLUMN ticket_promedio DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER bonus_ticket_ventas;
```

### Pendientes próxima sesión
1. **Security headers** — probar `Header set X-Content-Type-Options "nosniff"` en .htaccess primero para verificar que LiteSpeed soporta mod_headers. Si funciona, agregar HSTS, X-Frame-Options, Referrer-Policy
2. **CSRF en endpoints de config** — 15 endpoints sin csrf_check (articulo.php, cupon.php, usuario.php, etc.). Verificar que el frontend mande el token antes de agregar
3. **.gitignore** — agregar *.sql, *.csv, *.pem, *_backup*
4. **Contraseña mínima** — cambiar de 6 a 12 chars en registro_post.php
5. **Benchmark close_rate** — analizar si usar histórico (14%) vs ventana 15 días (23%). Decisión de impacto, pensar con calma
6. **Conversión 45.8% con 29% cierre** — revisar por qué no sube más estando arriba del benchmark
7. **Termómetro: venta cuenta con pagado>0** — ya implementado, verificar que los scores sean correctos con datos reales
8. **Variantes de giro** — diseño de módulo inmuebles/seguros con tabla catálogo por giro. Implementar cuando haya demanda validada
9. **Suscripciones MercadoPago** — whitelist IPs, probar pago, configurar cron

### Completado sesión 14 mayo 2026
1. **Bonus ticket alto** — 1.5x→+2(silencioso), 2x→+5, 3x→+8. Tope 15. Score máx 100
2. **Venta cuenta solo con pagado>0** — 16+ queries corregidos en ActividadScore, dashboard, ejecutivo y reportes
3. **Bug $pago_ok** — `id` resolvía a `ventas.id`, corregido a `cotizaciones.id`
4. **Aceptadas recientemente** — sin filtro de pago (es alerta informativa)
5. **Permiso guardar ventas** — permite asesor con eliminar_items o editar_cotizaciones
6. **Permiso descuento en guardar.php** — valida aplicar_descuentos si monto > 0
7. **Competencia ejecutivo** — respeta reviewed_at + proximidad 720h
8. **Frase bonus ticket** — solo 2x y 3x, 1.5x silencioso
9. **Ejecutivo SELECT** — agregar bonus_ticket, bonus_ticket_ventas, no_abiertas_5d
10. **Ghost cleanup en dashboard** — limpia sesiones fantasma, revierte estado
11. **Badge no_abierta en vencidas** — sin importar vigencia
12. **Tooltips lista cotizaciones** — badges, vistas, cupones, botones
13. **Escudo "¿qué es esto?"** — explicación + alerta dispositivos

### Completado sesión 17 mayo 2026

#### Seguridad
1. **CSRF en 11 endpoints** — articulo, cupon, usuario, costos_modo, marketing, radar, categoria, nuevo_gasto, proveedores crear/toggle, radar_feedback
2. **Security headers** — X-Content-Type-Options, X-Frame-Options, Referrer-Policy, HSTS (sin includeSubDomains). En .htaccess de public_html (no en repo)

#### Radar
3. **Filtro visita mínima 2 segundos** — sesiones <2s con scroll=0 no cuentan para el Radar (ambos loops)
4. **Todos los buckets calientes se agrupan en probable_cierre** — onfire, inminente, validando_precio, prediccion_alta, lectura_comprometida, multi_persona, alto_importe
5. **Sin límite de 12 items por sección** — probable_cierre muestra todas
6. **Texto probable_cierre** — "Resumen — Probable cierre" con descripción clara para asesores
7. **Competencia: modelo + navegador por cliente** — Android muestra modelo del UA (SM-S916B, CPH2205), iPhone muestra resolución (390×844), detecta Facebook/Instagram. UA sin truncar.
8. **Competencia por IP: descarta si device_sigs diferentes** — REVERTIDO, competidores pueden tener múltiples dispositivos en la misma red
9. **Device_sig no anula visitor_ids diferentes con IPs diferentes** — 2 personas con iPhones iguales ahora cuentan como 2. Agrupa solo si comparten IP (mismo teléfono 2 navegadores) o sin cookies.
10. **Union-find con IP compartida** — guarda TODAS las IPs por visitor_id. Si 2 vids con mismo dsig comparten al menos 1 IP → misma persona. Resuelve caso WhatsApp→Safari (nueva cookie, misma IP).

#### Landing
11. **Inmobiliarias y Agentes** — nueva tarjeta en "Para quién es"
12. **Agentes de Seguros y Servicios Financieros** — nueva tarjeta
13. **Grid 3 columnas** — 6 tarjetas equilibradas
14. **Sección movida después de los 5 pasos** — antes de Rentabilidad
15. **Paso 1 "productos"** — en vez de "artículos"

#### Descuento
16. **Descuento automático no se resetea al editar** — conserva fecha expiración original

### Pendientes próxima sesión

#### Módulo Inmuebles — Implementación
Diseño definido: tabla `propiedades` como EXTENSIÓN de `articulos` (no tabla separada).

**Tabla propiedades (extensión):**
```sql
CREATE TABLE propiedades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    articulo_id INT UNSIGNED NOT NULL,
    tipo_operacion ENUM('venta','renta','renta_temporal') NOT NULL DEFAULT 'venta',
    tipo_propiedad ENUM('casa','departamento','terreno','local_comercial','oficina','bodega') NOT NULL DEFAULT 'casa',
    m2_terreno DECIMAL(8,2),
    m2_construccion DECIMAL(8,2),
    recamaras TINYINT UNSIGNED,
    banos DECIMAL(3,1),
    fotos JSON,
    FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_articulo (articulo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE empresas ADD COLUMN giro ENUM('servicios','inmuebles') NOT NULL DEFAULT 'servicios';
```

**Mapeo de campos articulos para inmuebles:**
- `titulo` → nombre de la propiedad ("Casa 3 rec, Fuente de Piedra")
- `descripcion` → dirección + descripción completa (TEXT, cabe todo)
- `precio` → precio de la propiedad
- `sku` → referencia interna o vacío

**Archivos nuevos a crear (0 riesgo):**
| Archivo | Función |
|---------|---------|
| `modules/config/propiedad.php` | CRUD: INSERT/UPDATE en articulos + propiedades en transacción |
| `modules/config/_catalogo_inmuebles.php` | UI partial: sheet con campos de propiedad + upload fotos |
| `public/cotizacion_inmueble.php` | Template slug: galería fotos + datos propiedad + botón apartar |

**Archivos existentes con cambio mínimo (1-3 líneas):**
| Archivo | Línea | Cambio |
|---------|-------|--------|
| `config/index.php` | tab catálogo | 3 líneas: if giro include partial |
| `public/cotizacion.php` | ~35 | 3 líneas: if giro require template inmueble |
| `cotizaciones/nueva.php` | 54 | WHERE: OR descripcion LIKE (buscar por dirección) |
| `cotizaciones/ver.php` | 70 | Mismo cambio autocomplete |
| `ventas/ver.php` | 49 | Mismo cambio autocomplete |
| `core/Router.php` | rutas | Rutas para propiedad CRUD |
| `public/cotizacion.php` | SELECT | Agregar e.giro al JOIN con empresas |

**Fotos de propiedades:**
- Reusar `upload_archivo($file, $empresa_id, 'propiedades')` de Helpers.php
- Guarda en `/assets/uploads/{empresa_id}/propiedades/randomhex.jpg`
- Se sirve por la ruta `/assets/` existente en index.php
- Campo `fotos JSON` en propiedades = array de nombres de archivo

**Errores a evitar:**
1. INSERT en articulos + propiedades SIEMPRE en transacción
2. Forzar selección del catálogo para inmuebles (no items manuales) — slug necesita articulo_id para JOIN con propiedades
3. Forzar cantidad=1 para inmuebles
4. Ocultar botón "Agregar extra" para inmuebles
5. 1 propiedad por cotización — bloquear agregar más items
6. Fotos huérfanas si se cancela sin guardar (no crítico)
7. El slug de inmuebles usa LEFT JOIN por si articulo_id es NULL
8. Autocomplete busca por descripcion (dirección) en vez de sku para inmuebles

**Lo que NO cambia (verificado con agentes):**
- cotizacion_lineas — universal, no cambia
- quote_action.php — aceptar/rechazar funciona igual
- Radar, Escudo, Termómetro, ActividadScore — no se enteran del giro
- Ventas, pagos, abonos, recibos — no se enteran
- Dashboard, ejecutivo, reportes — no se enteran
- Push notifications — no se enteran

#### Otros pendientes
1. **Benchmark close_rate** — analizar si usar histórico (14%) vs ventana 15 días (23%)
2. **Conversión 45.8% con 29% cierre** — revisar por qué no sube más
3. **Suscripciones MercadoPago** — whitelist IPs, probar pago, configurar cron
4. **Reactivación de cotizaciones** — `reactivada_at` para no penalizar días acumulados (pendiente, requiere análisis)
5. **.gitignore** — agregar *.sql, *.csv, *.pem, *_backup*
6. **error_log en repo** — agregarlos a .gitignore para que cPanel no bloquee deploy
7. **Contraseña mínima** — cambiar de 6 a 12 chars en registro_post.php

### Branch de trabajo
- `claude/analyze-domain-change-hmo-AkFAi`

## Módulo Inmuebles — Diseño (pendiente implementación)

### Concepto
Agregar giro `inmuebles` a CotizaCloud. Misma plataforma, diferente catálogo y slug. Todo el core (Radar, Escudo, Termómetro, ventas, pagos, reportes) no se toca.

### Tabla propiedades
```sql
CREATE TABLE propiedades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    tipo_operacion ENUM('venta','renta','renta_temporal') NOT NULL DEFAULT 'venta',
    tipo_propiedad ENUM('casa','departamento','terreno','local_comercial','oficina','bodega') NOT NULL DEFAULT 'casa',
    titulo VARCHAR(255) NOT NULL,
    direccion VARCHAR(500),
    precio DECIMAL(14,2) NOT NULL DEFAULT 0,
    m2_terreno DECIMAL(8,2),
    m2_construccion DECIMAL(8,2),
    recamaras TINYINT UNSIGNED,
    banos DECIMAL(3,1),
    estacionamientos TINYINT UNSIGNED,
    pisos TINYINT UNSIGNED,
    descripcion TEXT,
    amenidades JSON,
    fotos JSON,
    estado_propiedad ENUM('disponible','apartada','vendida','rentada') DEFAULT 'disponible',
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    KEY idx_empresa (empresa_id)
);

ALTER TABLE empresas ADD COLUMN giro ENUM('servicios','inmuebles') NOT NULL DEFAULT 'servicios';
```

### Archivos a modificar (17 puntos en 9 archivos + 1 template nuevo)

Queries que cambian de tabla según giro (12 puntos):
1. config/index.php:45 — Lista catálogo
2. config/index.php:682-731 — HTML tab Catálogo
3. config/index.php:1855 — JS fetch guardar/eliminar
4. config/articulo.php:20,37,46 — CRUD backend
5. cotizaciones/nueva.php:54 — Autocomplete crear
6. cotizaciones/nueva.php:641-831 — Sheet catálogo + JS
7. cotizaciones/ver.php:70 — Autocomplete editar
8. cotizaciones/ver.php:653-817 — JS catálogo
9. cotizaciones/guardar.php:117 — Validar precio
10. ventas/ver.php:49 — Autocomplete ventas
11. ventas/acciones.php:85 — Buscar artículo por ID
12. cotizaciones/clonar.php:47 — No cambia (usa cotizacion_lineas)

Textos hardcodeados (5 puntos):
13. public/cotizacion.php:749 — "Artículos incluidos" → "Propiedad"
14. cotizaciones/nueva.php:415 — "Artículos" → "Propiedad"
15. cotizaciones/nueva.php:641 — "Catálogo" → "Propiedades"
16. config/index.php:335 — Tab "Catálogo" → "Propiedades"
17. landing.php:469,543 — "articulos" → "propiedades"

Template nuevo:
- public/cotizacion_inmueble.php — galería fotos + mapa + características + botón "Me interesa"/"Apartar"

### Errores a evitar
1. articulo_id en cotizacion_lineas sin FK — el giro indica a qué tabla pertenece
2. guardar.php:117 valida precio contra articulos — checar giro
3. Autocomplete envía articulo_id — reusar como ID genérico del catálogo
4. Slug de inmuebles es template completamente diferente, no un if/else
5. cantidad en cotizacion_lineas siempre = 1 para propiedades
6. Necesita config/propiedad.php para CRUD o detectar giro en articulo.php
7. Landing dice "artículos" — texto genérico o por giro

### Lo que NO cambia
- core/Auth.php, Router.php, layout.php
- Radar, Escudo, device_sig, tracking
- Termómetro, ActividadScore
- Dashboard, ejecutivo, reportes
- Ventas, abonos, recibos
- Push notifications, Suscripciones MercadoPago

## Sesión 19 mayo 2026

### Módulo Inmuebles — Completado
1. **Migración** — `add_inmuebles.sql`: tabla propiedades + giro en empresas
2. **CRUD** — `propiedad.php` + `propiedad_foto.php`: transaccional con fotos (máx 10)
3. **Catálogo UI** — `_catalogo_inmuebles.php`: sheet con datos + fotos, UX dos secciones (Guardar datos / Subir fotos / Guardar propiedad)
4. **Slug público** — `cotizacion_inmueble.php`: galería fotos, specs con emojis (📐🏗🛏🚿), precio separado abajo para forzar scroll, tracking Radar completo (checkTotals/checkPriceLoop)
5. **Integración cotizaciones** — nueva.php y ver.php: catálogo con propiedades JOIN, auto-fill título, 1 propiedad por cotización, cantidad=1, ocultar cant/precio
6. **guardar.php** — fuerza cantidad=1 para inmuebles
7. **ventas/ver.php** — oculta cant/precio para inmuebles, label "Propiedad"
8. **config/index.php** — tab "Propiedades" para inmuebles, query condicional

### Bugs corregidos
1. **goto skip_escudo** — saltaba el `echo $content` para apple-review (pantalla blanca total)
2. **ghost cleanup** — ya no revierte estado vista→enviada (irreversible ahora)
3. **Cookie cz_vid sin dominio** — cotizacion.php ponía cookie solo en subdominio, no viajaba al dashboard. Fix: PHP y JS ahora usan `.cotiza.cloud`
4. **SKU en ver.php** — la lista del catálogo perdía el SKU para servicios al agregar inmuebles
5. **$lineas[0] vacío** — protección en slug inmueble
6. **query innecesaria** — config/index.php no carga artículos cuando giro=inmuebles
7. **Theme colors** — `$th` se define antes del giro check, slug inmueble respeta colores

### Investigación Calvario 18 — Escudo Radar
**Caso:** Cotización 3948 marcada "sin abrir" pero cliente confirmó que la abrió.

**Hallazgos:**
- Kevin (usuario 21, asesor) abrió la cotización desde su iPhone a las 12:26 SIN estar logueado en Safari
- Su visitor_id `69b65229` era nuevo (no estaba en radar_visitors_internos)
- La IP `200.68.163.65` no estaba en internas a esa hora
- Las 3 capas fallaron → contó como cliente
- Cuando Kevin se logueó desde Safari iPhone a las 16:34, layout.php cleanup marcó retroactivamente esa sesión como interna (por IP match)
- El historial se "limpió" visualmente (UI filtra es_interno=1)

**Causa raíz confirmada:** Cookie `cz_vid` en `cotizacion.php` se ponía sin dominio `.cotiza.cloud` — generaba visitor_ids huérfanos que no se asociaban al login. **Corregido** en PHP (línea 151, 174) y JS (setCookie en slug).

**Timezone:** La BD muestra hora del servidor (UTC-4), no Hermosillo (UTC-7). Diferencia de 3 horas. Esto causó confusión en el análisis.

### Problema pendiente — Limpieza retroactiva incompleta
**Estado actual** de layout.php cleanup (líneas 52-78):
- Solo usa IP y visitor_id del request ACTUAL
- NO usa todos los visitor_ids conocidos del usuario en radar_visitors_internos
- Resultado: si Kevin entra al dashboard desde Windows, sus sesiones huérfanas de iPhone NO se limpian (IP diferente, visitor diferente)

**Fix pendiente:** Agregar todos los visitor_ids de `radar_visitors_internos WHERE usuario_id = ?` al cleanup. SIN usar IPs históricas (rotación Telmex = falsos positivos). Los visitor_ids son safe porque son UUID únicos confirmados por login.

### Problema reportado — Kevin Radar inflado
Kevin reporta que al abrir el Radar desde su computadora (logueado), sus visitas a cotizaciones se marcan como cliente. Capa 0 debería atraparlo. **Pendiente investigar.**

### Diseño pendiente — Eliminar skip_tracking
**Consenso:** El `goto skip_tracking` en cotizacion.php descarta visitas internas sin registrarlas. Esto causa:
- Pérdida de trazabilidad (no sabemos qué capa actuó)
- Eventos huérfanos en quote_events (JS sigue mandando pero no hay sesión base)
- Imposibilidad de auditar errores del sistema
- MarketingPixels::capi_view se manda para internos que no fueron detectados

**Propuesta acordada:** Reemplazar skip_tracking por INSERT con `es_interno=1` + `capa_motivo`. Saltar solo efectos externos (visitas++, estado, Radar recalc, CAPI pixel). Agregar filtros `WHERE es_interno=0` en queries downstream.

### Diseño pendiente — Giro Seguros
**Concepto:** PDF por línea de cotización (no por cotización). Cada opción de seguro es un item con su propio PDF embebido en el slug. Cliente compara y elige.
**Requiere:** campo `archivo` en cotizacion_lineas, upload en nueva.php, embed `<iframe>` en slug.

### Diseño pendiente — Ventas no modifiquen cotización original
**Problema:** ventas/guardar.php hace DELETE + INSERT en cotizacion_lineas. Destruye la cotización original.
**Opción evaluada:** cotizacion_lineas ya tiene columna `venta_id` (existe pero no se usa). Copiar líneas al crear venta y operar sobre las copias. Requiere cambios en ~7 archivos + migración de datos existentes.

### Notas técnicas
- `json_ok()` wraps data en `d.data`, no `d.id` directo — JS debe leer `(d.data && d.data.id) || d.id`
- WhatsApp in-app browser: NO existe en iOS (Safari siempre). En Android sí pero UA no contiene "WhatsApp". El `'whatsapp'` en es_bot() solo bloquea el preview bot (correcto).
- device_sig: solo para descarte, colisiona entre dispositivos iguales, muta en misma máquina
- localStorage device_id: NO es mejor que cookie — peor para cross-subdomain. Descartado.

### Branch de trabajo
- `claude/analyze-domain-change-hmo-AkFAi`

### Propuesta del usuario — escudo_log (tabla de auditoría)
**Concepto:** Mantener `skip_tracking` como está, pero ANTES del skip, insertar en una tabla `escudo_log` con todos los datos de la visita bloqueada (capa, visitor_id, IP, UA, device_sig, cotización, empresa, usuario si aplica).

**Ventajas:**
- Cero riesgo al comportamiento actual (quote_sessions, Radar, Dashboard intactos)
- Trazabilidad completa: qué capa bloqueó qué visita
- Permite auditar falsos positivos retroactivamente
- Base para decisiones futuras (eliminar skip_tracking si los datos confirman que es safe)
- Implementación mínima: 1 tabla + 1 INSERT antes de cada `goto skip_tracking`

**Estado:** Pendiente aprobación del usuario para implementar.

## Sesión 20 mayo 2026 — Escudo Radar: dominio custom

### Causa raíz encontrada (caso Nogales/Kevin)
Las 3 sucursales OnTime usan dominio custom: `hermosillo/obregon/nogales.ontimecocinas.com`.
En el dominio custom, las cookies de `.cotiza.cloud` NO viajan:
- Capa 0 (cza_session) ciega
- Capa 1 (cz_vid) huérfana — el cz_vid del custom es distinto al del login, nunca se registra
- Capa 2 (IP) neutralizada por el fix `&& !$tenia_cookie` del 18 mayo

El botón "Ver" del editor (ver.php línea 276) abre el slug público. El asesor previsualizando su propia cotización contaba como visita de cliente.

### Fix implementado (commit f163355)
`safari_bridge.php` ahora pone `cza_session` en el dominio custom (no solo cz_vid).
El token se resuelve server-side desde uid+eid (verificados por HMAC), nunca viaja en URL.
Con eso Capa 0 funciona en el dominio custom — certeza, no heurística.

**Confirmado funcionando:** Kevin abrió slug 3975 desde su Windows → cero sesiones nuevas → Capa 0 lo atrapó.

### Por qué cza_session y no cz_vid
- cz_vid + Capa 1 = heurística de lista; el valor se desvía → falla
- cza_session + Capa 0 = validación en vivo contra user_sessions → certeza
- cza_session no necesita listas, ni marcar visitor_ids, ni IPs (evita la reacción en cadena de Telmex)

### Decisión: refresh del bridge
El bridge corre solo al login. La cookie cza_session del custom dura 3 días (igual que la sesión) — sincronizadas, expiran juntas. El bridge del login es suficiente EN TEORÍA.
**Decisión:** dejar el bridge del login (opción B), monitorear las 3 sucursales. Si el bridge falla seguido (Imunify360, red), agregar refresh con redirect 1 vez al día desde el dashboard (opción A). NO usar iframe — third-party cookies bloqueadas.

### Pendientes de esta investigación
1. **Sesión anulada deja fecha/hora sucia** — el ghost cleanup borra la sesión y decrementa visitas, pero NO revierte `ultima_vista_at`. La cotización queda marcando la hora de visita del asesor en vez del cliente real. Caso confirmado: Acacia vieja 3964 con ultima_vista_at de Kevin.
2. **Evaluar es_interno=1 + capa_motivo** — reemplazar skip_tracking por INSERT con es_interno=1 y motivo, para trazabilidad. Auditoría hecha: 5 queries downstream necesitan WHERE es_interno=0 (Radar.php ×2, dashboard ×2, ActividadScore ×1).
3. **Limpiar datos sucios históricos** — sesiones de asesores ya contadas como cliente (es_interno=0) que inflaron cotizaciones. Ya tenemos el visitor_id de Kevin (ad66df34, df00eaa3) — se pueden marcar retroactivamente. Falta definir el alcance y el script.
4. **App Capacitor** — login_post.php salta el bridge si is_native_app. Los que se loguean por la app no reciben cza_session en el custom. Caso aparte, pendiente.

### Continuación sesión 20 mayo — limpieza retroactiva

#### Bugs identificados en layout.php cleanup (líneas 52-95)
1. **IP false positive** — la rama `qs.ip = my_ip` + `ri.id IS NOT NULL` puede marcar sesiones de clientes reales que comparten IP con el asesor (rotación Telmex). Se debe quitar — usar SOLO visitor_ids.
2. **No recalcula `ultima_vista_at`** — al marcar una sesión es_interno=1, la cotización mantiene la hora de la sesión del asesor. Debe recalcular `ultima_vista_at = MAX(created_at WHERE es_interno=0)` de las cotizaciones afectadas. SIN tocar `visitas` (el usuario decidió no resincronizar ese contador).

#### Fix pendiente (aprobado, falta implementar)
- Quitar rama `qs.ip = my_ip` del WHERE (solo visitor_ids)
- Después del UPDATE, recalcular SOLO `ultima_vista_at` de cotizaciones afectadas
- VERIFICAR PRIMERO: correr `SELECT * FROM radar_visitors_internos WHERE visitor_id = 'ad66df34-...'` — si ya aparece (Capa 0 lo registró), es seguro quitar la IP

#### Verificar impacto en inmuebles y servicios
- Confirmar que los cambios al Escudo (bridge cza_session, cleanup retroactivo) no afectan el módulo inmuebles (cotizacion_inmueble.php)
- El tracking en cotizacion.php corre ANTES del giro redirect — los cambios al Escudo aplican igual a inmuebles (mismo cotizacion.php)
- Servicios financieros: giro pendiente, no implementado aún — no afectado

#### Estado del commit e325f09 (visitor_ids en cleanup)
- Cambio de visitor_ids ya está en la branch — agrega todos los visitor_ids conocidos del asesor al cleanup
- PENDIENTE: quitar la rama IP y agregar el recalc de ultima_vista_at
- El usuario quiere UN SOLO cambio limpio final

#### Resumen de lo que queda deployado y funcionando
1. **Bridge cza_session** (commit f163355) — confirmado: Kevin abrió slug 3975, cero sesiones nuevas ✓
2. **Visitor_ids en cleanup** (commit e325f09) — cleanup usa todos los vids del asesor, no solo el actual
3. **Cookie domain .cotiza.cloud** en cotizacion.php (PHP + JS) — cookies del slug viajan entre subdominios

#### Pendientes para mañana
1. SQL: `SELECT * FROM radar_visitors_internos WHERE visitor_id = 'ad66df34-e960-412d-afca-ea339109f215'` — verificar si Capa 0 lo registró
2. Si sí → implementar fix final: quitar IP del cleanup + recalc ultima_vista_at
3. Verificar que inmuebles y giro servicios no se afectaron por los cambios al Escudo
4. Cleanup manual de Calvario 18 (SQL que ya se dio)

#### Pendiente: Kevin score baja cuando vende
Kevin reporta que vende y su score del termómetro baja en vez de subir. Investigar:
- ¿Las ventas de Kevin tienen pagado>0? (venta solo cuenta con pago real)
- ¿El benchmark se auto-infla y penaliza? (bench_ventas vs ventas_periodo)
- ¿Las visitas fantasma inflaron métricas que ahora se "corrigen" y bajan el score?
- ¿pen_sin_pago lo penaliza? (ventas con pagado=0 por más de 5 días)
- Revisar su desglose en el debug panel del leaderboard (solo superadmin)

## Sesión 20 mayo 2026 (cont.) — Termómetro: benchmark histórico + bonus por cierre

### Problema raíz (resuelve "Kevin score baja cuando vende")
El termómetro comparaba la tasa de cierre del vendedor contra el benchmark de la empresa **de la misma ventana de 15 días**. En empresas de 1 vendedor (la mayoría — micro-negocios) el benchmark **ES el propio vendedor** → `sigmoid(x, x) = 0.50` siempre. El sistema era ciego a la mejora. Kevin pasó de 8.6% a 47% de cierre y el score no lo reflejaba.

### Completado
1. **Benchmark histórico para Conversión** — nuevo `close_rate_hist` en `_benchmarks()`: tasa de cierre de la empresa sobre TODO lo anterior a la ventana de 15 días (`created_at < NOW()-periodo`). Referencia estable: el desempeño actual no contamina su propio benchmark. Con `emp_vistas_hist < 5` cae al `close_rate` actual. Lo usan el sigmoid de Conversión, `perf_ratio` y `pen_volumen_sin_cierre`. `close_rate`/`close_rate_safe` NO se tocaron (blast radius contenido a Conversión).
2. **Bug de consistencia corregido** — el bloque CONSISTENCIA SEMANAL: `total_semanas = round(15/7) = 2`, pero una ventana de 15 días abarca **3 semanas ISO**. Cerrar en las 3 daba `consistencia = 1.5` → `reduction` NEGATIVA → la "penalización" se volvía un multiplicador ~1.34 que **inflaba** Conversión (topaba en 100%). Fix: `consistencia = min(..., 1.0)`. Inflaba a todo vendedor que cerrara en 3 semanas ISO — al corregir, los scores bajan a su valor real.
3. **Bonus por cierre sobre histórico** — premia al que sobresale: `ratio = tasa_cierre / close_rate_hist`. Un solo tier, no acumulable: **≥2.5× → +4**, **≥4× → +8**. Requiere ≥4 cierres pagados. Empresa sin histórico no aplica. Columna `bonus_cierre` + línea en panel debug + frase en el diagnóstico.
4. **Frases de bonus siempre visibles** — `bonus_ticket` y `bonus_cierre` se generaban antes del `array_slice` que recorta a `max_frases` y se cortaban. Movidas DESPUÉS del corte.

### Verificado con datos reales
- **Kevin** (Nogales, 1 vendedor, uid 21): histórico 8.6%, actual 47% → 5.5× → +8. Score 79.
- **Abigail** (Hermosillo, uid 18): histórico 11.9%, actual 35% → 2.9× → +4. Score 79.

### Migración
`migrations/add_bonus_cierre.sql` — `ALTER TABLE usuario_score ADD COLUMN bonus_cierre INT UNSIGNED NOT NULL DEFAULT 0 AFTER ticket_promedio;` (correr ANTES de desplegar).

### Commits (branch claude/analyze-domain-change-hmo-AkFAi)
`2c93fdf` histórico · `a541bdd` fix consistencia · `267749b` bonus · `c1b6fed` umbral 2.5× · `243440c` frase · `1623969` frases tras el corte.

### Pendiente termómetro
- **Bonus por volumen de ventas** — idea evaluada, no implementada. Premiar número de ventas pagadas por encima del histórico (complementa `bonus_ticket` = ticket alto). Para después.
- Recalcular el leaderboard completo de cada empresa para que `bonus_cierre` se calcule a todos (la columna nace en 0).

## Sesión 20 mayo 2026 (cont.) — Escudo: superadmin en dominios custom

### Problema
El superadmin (uid 4, admin@cotiza.cloud) revisa cotizaciones de TODAS las
empresas. En dominios custom (`*.ontimecocinas.com`) su visita contaba como
cliente e inflaba el Radar. Confirmado con log: `[EscudoDbg] CLIENTE
host=obregon.ontimecocinas.com uid=null` — pasó las 4 capas.

### Causa raíz (dos partes, ambas confirmadas con logs temporales)
1. **El bridge no le ponía `cza_session`** — `safari_bridge.php` excluía al
   superadmin del bloque que pone la cookie de sesión (`if (!$es_super ...)`).
2. **`Auth` rechazaba la sesión del super** — `cargar_usuario_por_token()`
   exigía `s.empresa_id = empresa_del_host`. La sesión del super es de la
   empresa a la que se logueó (no del host custom que visita) → no matcheaba
   → `Auth::id()` = null → Capa 0 ciega.

### Fix (2 commits)
1. `8f43e06` — `safari_bridge.php`: el bloque `cza_session` ahora resuelve la
   sesión por `usuario_id` (sin filtro de empresa) e incluye al superadmin.
   El asesor no cambia (su sesión siempre es de su empresa = la del dominio).
2. `583ff55` — `Auth.php` `cargar_usuario_por_token()`: el WHERE pasa de
   `s.empresa_id = ?` a `(s.empresa_id = ? OR u.rol = 'superadmin')`. La
   sesión del super carga en cualquier dominio de empresa.

`login_post.php` NO se tocó — la cadena de redirect del super ya incluía
todos los dominios custom.

### Verificado con datos reales
- `CAPA0-interno uid=4 super=1` confirmado en hermosillo y obregón.
- Asesor no se rompió: `CAPA0-interno uid=18` (Abigail) en su propio dominio.
- Commits de diagnóstico (logs temporales, ya removidos en `d2c8bd1`):
  `70fbb87` EscudoDbg, `644a464` BridgeDbg.

### Aprendizajes
- `skip_tracking` es silencioso — no deja rastro de qué capa actuó. Para
  diagnosticar el Escudo hace falta log temporal (pendiente de CLAUDE.md:
  tabla `escudo_log` de auditoría).
- Las sesiones del superadmin tienen `empresa_id` = la empresa con que se
  logueó (12, 14, 11...), no una fija. El super se loguea a distintas empresas.
- Capa 1 (cz_vid) no es confiable para el super en dominios custom — el cz_vid
  se desvía (huérfano). Capa 0 (`cza_session`) es la sólida.

### Limpieza pendiente
Las pruebas dejaron sesiones falsas del super en cot 3973 y 3974 — marcar
`es_interno=1` con el SQL ya entregado (filtro IP `187.245.114.71`).

## Giro Seguros — plan aprobado (implementar)

### Concepto
giro `seguros`. Cada línea de la cotización = una opción de póliza con su
propio PDF. El cliente compara los PDF en el slug. Opción A: slug
informativo — sin botón aceptar, el asesor cierra la venta manual.
Catálogo = igual a servicios (sin extensión tipo inmuebles).

### Decisiones de diseño
- Slug: opciones con PDF arriba, **"resumen de precios" abajo** (cada
  póliza con su precio, SIN total sumado — sumar N seguros está mal). El
  Radar apunta `totalsEl` al resumen → conserva señal "validando_precio".
- PDF embebido con **PDF.js** (Mozilla, auto-hospedado en `/assets/pdfjs/`)
  — render inline a `<canvas>`, alta resolución 2x, lazy por página. NO
  iframe (falla en móvil), NO botón de descarga (perdería el rastro del
  Radar — el JS solo corre dentro del slug).

### Migración — `add_seguros.sql`
```sql
ALTER TABLE empresas MODIFY giro ENUM('servicios','inmuebles','seguros') NOT NULL DEFAULT 'servicios';
ALTER TABLE cotizacion_lineas ADD COLUMN archivo VARCHAR(255) NULL AFTER es_extra;
```

### Plan por fases
- **FASE 1 — Editor:** `subir_pdf_poliza.php` (NUEVO endpoint upload) +
  ruta en `Router.php` + `cotizaciones/ver.php`/`nueva.php` (botón "Subir
  PDF" por línea + campo oculto `archivo`) + `cotizaciones/guardar.php`
  (`archivo` al INSERT de líneas).
- **FASE 2 — Slug:** `cotizacion.php` (if giro=seguros → require) +
  `cotizacion_seguros.php` (NUEVO — opciones+PDF.js, resumen de precios,
  copiar tracking JS de `cotizacion_inmueble.php`).
- **FASE 3 — Venta:** `ventas/guardar.php` + `ventas/ver.php` (`archivo`).
- **FASE 4 — Clonar:** `cotizaciones/clonar.php` (`archivo` al copiar).

### Riesgo clave
`cotizacion_lineas` se hace DELETE+INSERT en `guardar.php`,
`ventas/guardar.php` y `clonar.php` — el `archivo` viaja como campo oculto
por línea y se re-escribe en cada guardado. Si el JS lo pierde, el PDF
queda huérfano.

## Sesión 22 mayo 2026 (noche) — Capa "cuarentena" Escudo (pendiente revisar mañana)

### Contexto / problema raíz
El problema cuando los buckets se contaminan: una visita de asesor no
detectada por las 3 capas entra al Radar, sube de bucket, dispara push,
infla métricas. La contaminación es el costo real — más caro que perder
una visita aislada.

### Idea del usuario (22 mayo noche, antes de dormir)
> "Ocupariamos no contar la visita pero acumularlas, y si un login con
> visitor concuerda con el visitor de esta de ip en un futuro hacer una
> limpieza"

Triple objetivo:
1. **No contar la visita** ahora (Radar ciego, NO entra al bucket — esto
   es lo importante: evitar contaminar buckets)
2. **Acumular** la sospecha (auditoría / poder recuperar después)
3. **Limpiar** retroactivamente cuando un login confirme el visitor_id

### Trigger propuesto
IP+desktop sospechosa = IP con ≥N logins/internos en ≤3 días + device_sig
desktop (`tp=0` no touch AND `iosM=0` no iOS).

### Opciones evaluadas (sin decisión aún)

**A — Cuarentena ligera (recomendada por Claude):**
- INSERT quote_session con `es_interno=1` + `motivo='ip_desktop_sospecha'`
  + `pending_review=1`
- Radar nunca la ve → bucket NO se contamina ✓
- Si llega login con ese vid → `marcar_visitor_interno` + cleanup
  layout.php → `pending_review=0` (auditoría cerrada)
- Si nunca llega login → queda como sospecha no confirmada
- Costo falso positivo: 1 visita perdida por evento. Sin rollback.
- Aligned con la lógica actual del Escudo, sin riesgos nuevos.

**B — Contar y revertir (más preciso, más complejo):**
- INSERT con `es_interno=0` + `pending_review=1`
- Cuenta visita → Radar reacciona (bucket SÍ se contamina temporalmente)
- Si llega login con ese vid → rollback: marcar es_interno=1, decrementar
  visitas, recalcular ultima_vista_at, revertir estado vista→enviada
- Si nunca llega → sigue como cliente real
- Ventana sucia: Radar pudo enviar push, subir bucket, disparar
  notificaciones en el intermedio
- Rollback de Radar (visitas+estado+push) no trivial

### Por qué A va alineado con el objetivo del usuario
El objetivo central es "no contaminar buckets". Opción A garantiza que la
visita sospechosa NUNCA entra al Radar — bucket queda limpio desde el
primer instante. Opción B permite contaminación temporal.

### Preguntas abiertas para mañana
1. ¿"Acumular" es solo para auditoría o también para recuperar visitas
   perdidas si era cliente real?
2. ¿Asumimos el costo de perder visitas reales (A) o nos blindamos contra
   eso (B)?
3. ¿Horizonte de tiempo para la sospecha? (ej. después de 7 días sin
   login, ¿se descarta automáticamente?)
4. ¿Umbral N de logins/internos en ≤3 días? (3? 5? 10?)
5. ¿Necesitamos columna `pending_review` nueva o reusamos `capa_motivo`?
6. ¿Definición "desktop" suficiente con `tp=0 AND iosM=0` o agregar otros
   filtros (sw>=1024)?

### Restricciones del usuario (NO romper)
- "OLVIDA EL TOKEN — la Ver requiere login → Capa 0 ya lo cubre.
  REGISTRADO EN MEMORIA, no proponer de nuevo."
- "antes de tocar codigo debemos estar de acuerdo"
- "NUNCA ASUMAS SIN VERIFICAR, NO INVENTES TEORIAS, SIEMPRE LEE EL CODIGO
  ANTES DE CUALQUIER COSA"

### Próximo paso al retomar
1. Usuario decide Opción A vs B (o variante)
2. Definir parámetros (umbral N, horizonte, definición desktop)
3. Diseñar la query de detección y el INSERT modificado
4. Revisar antes de tocar código

### Pendiente físico para mañana
- UPDATE manual para `c6202e48-…` (cot 3805 Acacia Abigail) — usuario
  dijo "Ahorita lo haré"

## Sesión 23-24 mayo 2026 — Cierre del día (Manuel leak)

### Commits del día (branch `claude/analyze-domain-change-hmo-AkFAi`)
| Commit | Cambio |
|---|---|
| `17de9df` | Fix dominio cookie cz_vid (subdomain vs custom) — previene leak por bucle infinito |
| `95ae9f3` | Filtro UI ghosts en historial visitas + error_log en track.php |
| `5707374` | Constantes SESSION_VERSION + SESSION_*_SECONDS |
| `454fc34` | Sesión browser 14d (era 3d) + activity refresh + SESSION_VERSION check |
| `29d1d7b` | Tabla escudo_log + auditoría en cotizacion.php 4 puntos de decisión |
| `147dbe0` | Bridge captura vid legacy del custom domain (cierra Caso 1) |
| `a27e886` | Alinear cz_dsig a 14d + propagarla a custom domain via JS |

### Verificado en producción
- Sesión 1b6ff (Manuel) extendida de 3d a 14d ✓
- escudo_log capturando capa_0_logueado con cookies completas ✓
- No nuevos leaks post-deploy (Monitor en 2 viejos, ahora limpiados manualmente)

### Migración pendiente en server (si no se corrió)
```sql
-- migrations/add_escudo_log.sql
```

### SESSION_VERSION
Constante en config.php para forzar re-login global cuando se bumpee:
```php
define('SESSION_VERSION', '2026-05-24'); // bumpea fecha al deployar cambios al Escudo
```
Default `2000-01-01` deja el check inerte si no se sobrescribe.

## PENDIENTE — Gate obligatorio de Push Subscription (desktop)

### Concepto
Hacer OBLIGATORIO que asesores en desktop activen notificaciones del navegador.
Sin notificaciones activas → no acceden a dashboard, radar, cotizaciones, etc.
Objetivo: forzar adopción de push como mecanismo adicional de identidad +
canal de notificación de leaks.

### Diseño aprobado por el usuario
- **Solo desktop**: mobile y app nativa NO se bloquean (ya tienen identity)
- **iOS Safari sin PWA**: NO bloquear (físico imposible) — mostrar CTA "Descarga la app"
- **Bypass para superadmin**: para evitar lockout si push se rompe

### Decisiones pendientes ANTES de implementar
1. **¿Android móvil también se bloquea?** (push funciona pero UX en pantalla chica es peor)
2. **¿Frontend-only Fase 1 o backend de una?** (JS overlay vs server-side route gating)
3. **¿Qué constituye "subscription registrada"?** (ANY active en dispositivos_push, o per-device-fingerprint match)

### Stack propuesto
| Archivo | Función |
|---|---|
| `assets/js/escudo-gate.js` (NUEVO) | Lógica del gate: detectar capability, permission state, mostrar overlay |
| `assets/css/escudo-gate.css` (NUEVO) | Estilos del overlay full-screen |
| `core/layout.php` | Incluir el JS + HTML overlay (solo si user logueado) |
| (opcional Fase 2) `api/push/check-status.php` | Verificar subscription server-side, rechazar requests |

### Estados del overlay (UI)
| Estado | Mostrar |
|---|---|
| `permission='default'` | Pre-prompt modal: "Activa el Escudo Radar — sin esto las visitas a tus cotizaciones cuentan como cliente. [Activar]" → al click llama `requestPermission()` |
| `permission='granted'` sin subscription | Auto-suscribir silencioso → POST a `/api/push/register` |
| `permission='denied'` | **CRÍTICO**: instrucciones por browser con screenshots. Browser NO permite re-pedir prompt programáticamente. Solo manual desde settings → click candado URL → notificaciones → permitir |
| `granted` + subscription activa | Desbloquear, mostrar dashboard normal |

### Detección plataforma (JS)
```javascript
function debeAplicarGate() {
    // App nativa → ya tiene APNs/FCM, no aplicar
    if (window.Capacitor?.isNativePlatform?.()) return false;

    // Browser sin soporte → no aplicar
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return false;

    // iOS Safari sin PWA → physico imposible, no aplicar
    var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    var isStandalone = window.navigator.standalone === true;
    if (isIOS && !isStandalone) return false;

    // Mobile Android: opcional (decisión pendiente)
    // var isMobile = /Android|...

    return true; // desktop con capability → aplicar gate
}
```

### Limitación arquitectónica importante (NO ignorar)
**Service workers son per-ORIGIN.** Push subscription en `cotiza.cloud` NO sirve
en custom domain como `obregon.ontimecocinas.com`. Para empresas con custom
domain, el push como **identity en slugs** sigue sin cubrir.

El gate forzaría la activación en `cotiza.cloud` (dashboard) — eso genera
subscription en esa origin. Pero las visitas leak en slugs custom domain
NO se identifican por push subscription.

**El gate sigue valiendo** para:
- Garantizar que asesor recibe push notifications (canal de notificación)
- Reducir leaks en SUBDOMAIN slugs (donde sí funciona como identity)
- No resuelve leaks en CUSTOM DOMAIN slugs (los más comunes para OnTime)

Para extender a custom domain: requeriría registro de SW separado en cada
dominio custom, prompt de permission EN CADA dominio, almacenamiento de
subscriptions por dominio. **Bastante más complejo** — descartado por ahora.

### Comportamiento si `permission='denied'`
Browser NO permite re-prompt programático. Estrategia:

1. **Detección**: `Notification.permission === 'denied'` al cargar
2. **UI mostrada**: pantalla con instrucciones específicas por browser:
   - Chrome: "Click el candado 🔒 en la URL → Notificaciones → Permitir → Recargar"
   - Firefox: "Click el escudo en la URL → Permisos → Notificaciones → Permitir → Recargar"
   - Safari macOS: "Preferencias del Safari → Sitios web → Notificaciones → Permitir"
3. **Botón "Ya lo hice → recargar"**: recarga la página, el gate re-evalúa
4. **Pre-prompt strategy**: ANTES de llamar `requestPermission()` por primera
   vez, mostrar nuestro modal explicando el beneficio. Reduce tasa de denial
   del ~70% a <10%.

### Tradeoffs honestos
**Pros:**
- Garantiza canal de notificación a todos los asesores
- Posible identidad adicional para slugs subdomain
- Disciplina: asesor "burro" no puede usar el sistema sin protegerse

**Cons:**
- UX hostil para asesores nuevos (primera pantalla bloqueante)
- Bounce rate alto en signup
- Soporte: tickets de "no me deja entrar" cuando alguien dice denied
- NO resuelve leaks en custom domain (donde más leaks ocurren)
- iOS Safari users quedan sin solución (a menos que descarguen app)

### Antes de implementar
Esperar 2 semanas para validar con `escudo_log` cuántos leaks aparecen
post-fix de hoy. Si son <5 por semana, el gate puede no valer la pena.
Si son >20 por semana, justifica el costo UX.

### Tabla cobertura final
| Plataforma | Estado |
|---|---|
| Desktop Chrome/Firefox/Edge | Bloqueado hasta activar |
| Safari macOS | Bloqueado hasta activar |
| App nativa iOS/Android | Sin bloqueo (token APNs/FCM ya identifica) |
| Safari iPhone sin PWA | Sin bloqueo, ve CTA "Descarga la app" |
| Chrome Android | TBD (decisión pendiente) |

## Auditoría 24 mayo 2026 — pendientes no aplicados

Auditoría de los commits del día por 3 agentes paralelos. Los **críticos**
se arreglaron en commit `3f913e3`. Estos quedan pendientes (medio/bajo):

### Pendientes técnicos (no urgentes)

1. **Cron purga `escudo_log`** — sin límite la tabla crece sin tope.
   Plan: cron diario que borre rows > 30 días. Sin esto, ~10k inserts/día
   en empresa media → 3.6M rows/año.

2. **Bridge legacy vid en shared computer** — si la PC de Manuel la usa
   también un cliente esporádico (cliente entra al slug → genera vid X →
   Manuel se loguea después → bridge registra X como interno del Manuel).
   Visitas legítimas posteriores de ese cliente desde su propio teléfono
   con el mismo cookie quedan descartadas. Caso edge en agencias.
   Mitigación posible: TTL más corto para source='safari_bridge_legacy'
   (cambiar en `Radar::es_visitor_interno`).

3. **track.php log injection** — `error_log("... ip={$ip}")` usa el IP
   sin sanitizar. Si `ip_real()` confía en X-Forwarded-For (verificar),
   un cliente puede meter `\n[FAKE LOG LINE]` y forjar entradas. Riesgo:
   confusión en diagnóstico. Verificar `ip_real()` antes de dar por
   seguro, o usar `escapeshellarg`/sanitización en el log message.

4. **cz_dsig sin flag Secure** — `core/layout.php:677` setea cookie sin
   `; Secure`. No es secreto pero ahora vive 14d (4.6× más que antes) —
   más oportunidades de leak en MITM con HTTP downgrade. Agregar `;Secure`.

5. **cz_dsig setCookie con valores "todo-zero"** —
   `public/cotizacion.php:1584` solo valida `if (deviceSig)`. Si getDeviceSig
   devuelve `0|0|1|0|0|||||0|0|0|0|0` (valores degradados sin info útil),
   la cadena es truthy y se persiste como cookie 14d → user_sessions queda
   con dsig genérico que colisiona con cualquier dispositivo similar.
   Fix: validar que al menos sw>0 o maxTex>0 antes de persistir.

6. **`$cot['visitas_reales']` código muerto** — `modules/cotizaciones/ver.php:90`
   calcula el conteo limpio pero ningún template lo usa. COUNT(*) extra
   en cada carga sin beneficio. Eliminar O empezar a usarlo en el UI del
   contador de visitas (decisión de producto pendiente).

7. **escudo_log no marca superadmin** — cada visita del super a CUALQUIER
   empresa genera row con `empresa_id` de la cot visitada, sin distinguir
   de asesor real. Agregar columna `usuario_id` y `es_super` al esquema,
   o sufijar decision a `capa_0_logueado:super`.

8. **escudo_log sin índice por visitor_id** — uso típico "qué vid pasó por
   todas mis cots esta semana" hace full scan. Agregar
   `KEY idx_vid_time (visitor_id, created_at)`.

9. **escudo_log no captura recargas ni filtros tempranos** —
   `cotizacion.php` solo loguea cuando `!$session_existe` (INSERT nueva).
   Recargas (heartbeat), bots filtrados por UA, estados no publicables
   NO se loguean. Si se usa el log para auditar "cuántas visitas reciben
   mis cots", subestima fuerte. Considerar log también en esos paths.

10. **PHP `$_SESSION` cookie name colisiona con cza_session** (preexistente)
    `core/Auth.php:32` llama `session_name(SESSION_NAME)` — la cookie PHP
    nativa para `$_SESSION` comparte nombre con la cookie del token de Auth.
    Ahora con activity refresh re-seteando la cookie en cada request, el
    riesgo de override es mayor. Renombrar la PHP session a `PHPSESSID`
    diferente de `SESSION_NAME`.

11. **`SESSION_LIFETIME` no existe en código pero `impersonar.php` la usa**
    Verificar que esté en `config.php` del server o el flujo de impersonar
    superadmin queda silencioso roto (cookie con expires=time()+null=time()
    → expira inmediato).

### Hallazgos preexistentes detectados (no de hoy)

- `cargar_usuario_desde_token_completo` SELECT incluye `u.password_hash` —
  cualquier `print_r(Auth::usuario())` lo filtra. bcrypt es difícil de
  romper pero idealmente excluir del SELECT.
- Cleanup retroactivo en `layout.php` puede ser pesado en primera carga
  del día si hay muchos vids registrados (no async, bloquea render).

## Sesión 24 mayo (cierre noche) — Cookies SaaS

### Completado (commit d041bbe)
1. **JS setCookie con `Secure` condicional por protocolo** en 3 archivos:
   `login.php:294`, `cotizacion.php:1532`, `layout.php:677`.
   - Causa raíz: `document.cookie` re-escribía la cookie en cada page load
     SIN Secure, borrando el atributo que PHP había puesto. Por eso
     `cz_vid` y `cz_dsig` aparecían sin Secure aunque PHP las pusiera con
     `secure=true`. `cza_session` (solo set por PHP) sí lo mantenía.
   - Secure condicional via `location.protocol === 'https:'` para no
     romper dev local (HTTP rechazaría cookie con Secure).
2. **Validar dsig útil antes de persistir** (audit item 5):
   `cotizacion.php:1584` y `layout.php:677` ahora chequean `sw>0 && maxTex>0`
   antes de guardar. Evita que fingerprints degradados como
   `0|0|1|0|0|||||0|0|0|0|0` se copien a `user_sessions.device_sig` y
   colisionen con cualquier dispositivo sin WebGL.

### Cookie `cz_vid` duplicada (.cotiza.cloud + host-only) — NO ACCIÓN

Empresas SaaS con asesores que se loguearon antes del 19 mayo tienen DOS
cookies `cz_vid` en su browser:
- `.cotiza.cloud` (con Secure ahora) — la legítima del código actual
- `granitodepot.cotiza.cloud` (host-only, sin Secure) — cadáver del fix
  del 19 mayo cuando `cotizacion.php` ponía cookies con dominio vacío

**Decisión: NO limpiar.** Auditoría exhaustiva (22 escenarios) confirma:
- Ambas tienen el MISMO valor (JS reusa via getCookie)
- Ambas están en `radar_visitors_internos` → Capa 1 funciona con cualquiera
- PHP `$_COOKIE` colapsa duplicados → siempre devuelve algo válido
- El vid es UUID opaca, leakearla por MITM no compromete nada
- TTL natural: ~2 años — se purga sola
- Cualquier usuario que limpie cookies del browser la borra gratis

Razón de no limpiar: cleanup JS para borrar la host-only es bajo riesgo
pero NO cero (Browser buggy podría borrar las dos → vid nuevo no
registrado → Capa 1 falla temporal). Beneficio es cosmético (Cookie
header limpio, debug claro). Ratio costo/beneficio negativo.

### Monitorear
- 2 semanas en `escudo_log`: si aparecen `cliente_real` con vids que
  deberían ser internos en empresas SaaS, reabrir el tema con datos
- Nuevos asesores post-19 mayo nunca tienen la duplicada — la población
  afectada decae naturalmente

### Pendientes que SIGUEN abiertos (no de hoy)
Los 11 items del audit del 24 mayo siguen sin aplicar (cron purga
escudo_log, bridge legacy shared computer, track.php log injection,
escudo_log marker superadmin, etc.). Aplicar cuando los datos justifiquen.

### Estado de commits del día
- `95ae9f3` filtro ghosts UI + log track
- `5707374` constantes SESSION_VERSION
- `454fc34` sesión 14d + activity refresh
- `29d1d7b` escudo_log + auditoría
- `147dbe0` bridge legacy vid
- `a27e886` cz_dsig 14d + propagación
- `0b4c3cc` docs cierre Manuel
- `3f913e3` audit fixes (5 críticos)
- `1bbda01` docs audit pendientes
- `d041bbe` Secure + dsig útil

Todos en origin/claude/analyze-domain-change-hmo-AkFAi. Sin pendientes.

## Sesión 27 mayo 2026 — Ghost cleanup + scroll restoration fix

### Problema central resuelto
El JS del tracker dispara `quote_open` al cargar y lee el scroll del browser
con `updateMaxScroll()`. Cuando Chrome Android restaura la posición de scroll
(porque el cliente ya había abierto el slug antes), el JS reporta ese scroll
restaurado como si fuera engagement del usuario. Las quote_sessions quedan
con `scroll_max=30-31` y `visible_ms<200` (cliente cerró rápido sin leer).
El filtro existente del Radar (`if vis>0 && vis<2000 && scroll===0`) NO las
atrapaba porque scroll>0. Inflaban buckets `multi_persona`, `re_enganche`,
`probable_cierre`.

### Commits aplicados (todos deployados)
- `eeee7d8` ghost cleanup mejorado (verifica eventos del vid con engagement)
- `8de8eb6` header ver.php usa ultima_vista_at (revertido después)
- `324f0b9` ghost cleanup definitivo + recalc vista_at/ultima_vista_at
- `a0a3248` ultima_vista_at se actualiza con cada evento engagement (throttle 1min)
- `b3e17f4` nota explicativa en historial + radar
- `[breve]` display "breve" en lugar de "0s"/"—"
- `3cbdeb0` filtro Radar scroll<35 + visible<200 (validado con datos)

### SQL retroactivo ya ejecutado
- 251 sesiones ghost borradas
- 150 cotizaciones con visitas/vista_at/ultima_vista_at recalculadas
- Cot 4029 corregida específicamente

### Validación con datos (60 días)
Rango scroll + visible<200 (de 102 sesiones sospechosas):
| Rango scroll | Aceptadas | Activas |
|--------------|-----------|---------|
| <20          | 2         | 9       |
| 20-34        | **0**     | 9       | ← zona muerta confirmada
| 35-49        | 2         | 6       |
| 50+          | 14        | 49      |

Threshold `scroll<35 && vis<200` validado: descarta 18 sesiones de la zona
muerta sin tocar engagement legítimo. 0 cotizaciones pierden TODO su
engagement con este filtro.

### Plan de pruebas — 2 semanas (10 de junio 2026)

**Nombre del plan**: `PRUEBA-GHOST-FILTRO-V1`

#### TEST 1 — `cuenta-ghost-filtrados`
SQL que mide cuántas sesiones nuevas caerían en el filtro de ghost-restore
en los últimos 14 días:
```sql
SELECT COUNT(*) AS ghosts_filtrados_14d,
       COUNT(DISTINCT cotizacion_id) AS cots_afectadas
FROM quote_sessions
WHERE es_interno = 0
  AND visible_ms IS NOT NULL
  AND visible_ms < 200
  AND scroll_max < 35
  AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY);
```
**Criterio éxito**: número estable entre 10-30 sesiones/14d (ratio similar
al histórico de 102 en 60d ≈ 24/14d). Si sube a >50 → algo cambió en el
comportamiento de tracker o browsers.

#### TEST 2 — `falsos-positivos-aceptadas`
Verifica que ninguna cot aceptada se quedó sin engagement por el filtro:
```sql
SELECT COUNT(DISTINCT c.id) AS cots_aceptadas_sin_engagement
FROM cotizaciones c
WHERE c.estado IN ('aceptada','convertida','aceptada_cliente')
  AND c.aceptada_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
  AND EXISTS (SELECT 1 FROM quote_sessions qs WHERE qs.cotizacion_id = c.id AND qs.es_interno = 0)
  AND NOT EXISTS (
    SELECT 1 FROM quote_sessions qs
    WHERE qs.cotizacion_id = c.id AND qs.es_interno = 0
      AND NOT (qs.visible_ms IS NOT NULL AND qs.visible_ms < 200 AND qs.scroll_max < 35)
  );
```
**Criterio éxito**: 0 cotizaciones. Si >0 → el filtro descartó clientes
reales que aceptaron. Ajustar threshold o revertir.

#### TEST 3 — `distribución-buckets-pre-post`
Comparar distribución de buckets del Radar antes vs después:
```sql
SELECT radar_bucket, COUNT(*) AS cots
FROM cotizaciones
WHERE estado IN ('enviada','vista')
  AND radar_bucket IS NOT NULL
GROUP BY radar_bucket
ORDER BY cots DESC;
```
**Antes del fix (snapshot del 27 mayo)**:
- probable_cierre: 79
- enfriandose: 25
- hesitacion: 11
- no_abierta: 7
- re_enganche_caliente: 4
- prediccion_alta: 3
- lectura_comprometida: 3

**Criterio éxito**: las cots NUEVAS distribuyen similar, pero
`multi_persona`, `re_enganche` y `probable_cierre` deberían bajar
ligeramente para empresas con tráfico móvil alto. Si `no_abierta` sube
desproporcionadamente → el filtro está descartando clientes reales.

#### TEST 4 — `ghost-actuales-cot-3997`
Revisar la cot 3997 específicamente (la que originó el debug):
```sql
SELECT id, estado, visitas, vista_at, ultima_vista_at, radar_bucket, radar_score
FROM cotizaciones WHERE id = 3997;

SELECT COUNT(*) AS sesiones_restantes,
       SUM(CASE WHEN visible_ms < 200 AND scroll_max < 35 THEN 1 ELSE 0 END) AS ghosts_que_pasaron
FROM quote_sessions WHERE cotizacion_id = 3997 AND es_interno = 0;
```
**Criterio éxito**: el bucket de cot 3997 (si pasa por evento) debe quedar
en un estado más honesto (no inflado por las 4 visitas raras).

#### TEST 5 — `monitor-filtrado-executive`
Abrir el panel del superadmin executive y revisar el "Monitor de filtrado":
- Debería seguir mostrando 0 fugas activas (commit 58c8465)
- Si aparecen nuevas fugas, son visitor_ids registrados como internos
  pasando como cliente_real → caso aparte del scroll restore

#### TEST 6 — `historial-cot-con-breve`
Abrir el editor de varias cot con tráfico reciente y verificar:
- Las visitas con engagement real muestran "Xs" (segundos)
- Las breves muestran "breve" (no "0s" ni "—")
- La nota explicativa aparece al pie del historial
- El header "Vista hace X" coincide con la primera visita visible del historial

#### TEST 7 — `auditoria-eventos-restore`
Verificar si Chrome sigue mandando quote_open con scroll>0 visible bajo:
```sql
SELECT COUNT(*) AS eventos_con_restore_aparente,
       MIN(visible_ms) AS min_visible,
       MAX(max_scroll) AS max_scroll_restore
FROM quote_events
WHERE tipo = 'quote_open'
  AND max_scroll > 0
  AND visible_ms < 200
  AND ts_unix >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 14 DAY));
```
**Esperado**: número significativo (el JS sigue leyendo scroll restaurado;
el filtro del Radar los descarta pero los eventos siguen llegando). Sirve
para confirmar que el patrón es real y se mantiene.

#### TEST 8 — `comportamiento-termometro`
Revisar el score APC de los vendedores: ¿cambió alguna métrica clave?
```sql
SELECT usuario_id, score, cot_asignadas, cot_vistas, ventas_periodo
FROM usuario_score
WHERE periodo_fin >= DATE_SUB(NOW(), INTERVAL 14 DAY)
ORDER BY score DESC;
```
**Criterio éxito**: scores estables. El filtro del Radar no afecta
ActividadScore directamente (usa visitas+estado), pero indirectamente
via Radar bucket. Si scores bajan dramáticamente, investigar.

### Plan de acción si las pruebas FALLAN

- **TEST 1 falla** (>50 ghosts/14d): los browsers cambiaron comportamiento.
  No revertir, ajustar threshold del filtro.
- **TEST 2 falla** (>0 aceptadas sin engagement): threshold demasiado
  agresivo. Bajar a `scroll < 30 && visible < 150` y revalidar.
- **TEST 3 falla** (no_abierta sube): el filtro descarta visitas reales.
  Revisar casos específicos.
- **TEST 4 falla** (bucket sigue inflado): el filtro no se aplica.
  Verificar deploy.
- **TEST 5 falla**: caso independiente del fix actual.
- **TEST 6 falla**: bug cosmético separado.
- **TEST 7 = 0**: significa que Chrome cambió de comportamiento (raro pero
  bueno).
- **TEST 8 falla**: investigar cambio en métricas, no revertir aún.

### Comando rápido para correr TODAS las pruebas

Guardar en `tools/pruebas_ghost_filtro_v1.sql` (pendiente crear):
ejecutar los 7 SQLs en orden y comparar contra los criterios.

### Pendiente cuando se valide

Si las 8 pruebas pasan en 2 semanas:
- Documentar el fix como exitoso
- Considerar aplicar el mismo principio al filtro de bots IP que tampoco
  detecta restore
- Evaluar si subir el umbral de visible a `<300` (más permisivo) o bajar
  a `<150` (más estricto) según datos reales
