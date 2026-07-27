# Runbook de migración a Contabo — guía maestra + bitácora de ejecución

> Documento único. La **Parte A** es el estado real de lo ejecutado (bitácora).
> La **Parte B** es lo que falta, con la secuencia del plan maestro original.
> La **Parte C** es la auditoría de seguridad/Escudo previa al corte.

**Servidor destino:** Contabo VPS · IP `212.28.186.247` · Ubuntu 24.04.4 LTS ·
11 GB RAM · 6 vCPU · 290 GB NVMe.
**Origen:** hosting cPanel Limitless (LiteSpeed + Imunify360), IP `107.161.23.124`
— sigue siendo producción hasta el corte.
**Branch de trabajo:** `claude/migracion-contabo-lezerv`.

**Estrategia (no perder de vista):** construir Contabo en paralelo → probar sin
tocar DNS → cortar solo cuando todo pase → dejar el host viejo 3-7 días como
rollback. **Cero downtime, con vuelta atrás.** El drift entre la primera copia y
el corte se cierra con **dump + rsync FINAL** justo antes de cambiar el DNS.

---

## ⚠️ DESVIACIONES vs el plan original (decididas en ejecución)

| Plan original | Lo que se hizo | Por qué |
|---|---|---|
| **DirectAdmin** ($5/mes) | **Sin panel**: nginx + PHP-FPM a mano | El motivo de dejar cPanel fue que una capa de panel (Imunify360) bloqueaba los webhooks de MercadoPago. Meter otro panel reintroduce esa clase de capa. Sin panel = control total, cero licencia, máximo rendimiento. phpMyAdmin protegido se instala aparte al final. |
| **acme.sh** (vía DirectAdmin) | **certbot** + plugin dns-cloudflare | Sin DirectAdmin no hay acme.sh integrado. certbot hace lo mismo (DNS-01 con API de Cloudflare) y auto-renueva. |
| Cloudflare **proxy naranja** + Full(strict) | **DNS-only (gris)** por ahora | Los 3 dominios custom de OnTime son **CNAME → cotiza.cloud**. Con proxy, esos CNAME resuelven a IPs de Cloudflare, que **no tiene la zona `ontimecocinas.com`** → no puede presentar cert válido → **error SSL y las 3 sucursales caídas**. En gris siguen el CNAME solos, sin tocar DirectAdmin de OnTime. El naranja se puede activar después convirtiendo esos CNAME en registros A. |
| Correo por **relay** desde el arranque | SMTP sigue en `mail.cotiza.cloud` | Deja el correo intacto durante el corte. **PERO crea dependencia del host viejo** — ver Parte B, punto 5. |

---

# PARTE A — LO QUE YA QUEDÓ (verificado en el servidor)

| # | Qué | Estado |
|---|---|---|
| 1 | Base: swap 4 GB, `ufw` (22/80/443), `fail2ban`, timezone `America/Hermosillo` | ✅ |
| 2 | Stack: nginx 1.24, PHP 8.3.6 (openssl, curl, pdo_mysql, mbstring, gd, xml, zip, intl, bcmath), MariaDB 10.11, certbot | ✅ |
| 3 | Deploy key read-only en GitHub; clone de `main` en `/var/www/cotizacloud-src` | ✅ |
| 4 | `deploy-cotizacloud.sh` (reemplaza `.cpanel.yml`) + publicación al webroot | ✅ |
| 5 | BD `cotizacloud` + usuario propio; `config.php` escrito con secretos reales | ✅ |
| 6 | Import del dump (50 tablas · 9 empresas · 17 usuarios · 2347 cotizaciones · 257 ventas) | ✅ (⚠️ **re-importar en el corte**) |
| 7 | Nginx vhost: front-controller, anti-leak de rutas y extensiones | ✅ |
| 8 | **DNS movido a Cloudflare** (NS `albert`/`imani`). 8 registros, DKIM copiado y verificado. Todo DNS-only apuntando aún al host viejo | ✅ |
| 9 | SSL: Cloudflare Origin Certificate instalado (sirve si algún día se activa el naranja) | ✅ |
| 10 | `real_ip` de rangos Cloudflare + **`APP_ENV=production`** por `fastcgi_param` | ✅ |
| 11 | **Archivos migrados**: `uploads/logos`, `public/uploads/logos`, `public/assets/uploads`, `data/` | ✅ |
| 12 | 6 correcciones de código (ver Parte C) commiteadas | ✅ |

**Verificado por HTTPS local (Host header, sin DNS):** `/`→200 · `/login`→200 ·
`/landing`→200 · `/registro`→200 · `/dashboard`→302 ·
`apple-review.cotiza.cloud/`→302 (detección de empresa OK) · **0 errores PHP**
(confirma `APP_ENV=production`).

## Rutas y archivos clave en el servidor

| Ruta | Qué |
|---|---|
| `/var/www/cotizacloud-src` | Clone completo de `main` (privado, NO servido) |
| `/var/www/cotizacloud` | **Webroot**. Copia selectiva del src |
| `/var/www/cotizacloud/config.php` | Config real (640, `www-data`). NO está en git |
| `/var/www/cotizacloud/logs/` | `error.log` de la app (debe ser escribible por `www-data`) |
| `/var/www/cotizacloud/data/` | Estado runtime: `comisiones_pagadas_*.json`, `equilibrio.json`, `soporte_config.json`, `exchange_rate.json` |
| `/var/www/cotizacloud/uploads/logos/` | **Logos de empresa** (`logo.php` escribe aquí, en la RAÍZ — no en public/) |
| `/var/www/cotizacloud/public/assets/uploads/` | Adjuntos, fotos de propiedades, imágenes de tickets |
| `/var/www/cotizacloud-keys/` | Llaves push (VAPID ✅ · **APNs `.p8` pendiente**) |
| `/usr/local/bin/deploy-cotizacloud.sh` | Deploy: `git reset --hard origin/main` + rsync selectivo + permisos |
| `/root/cotizacloud-credenciales.txt` | DB_PASS + APP_SECRET generados (600) |
| `/etc/nginx/sites-available/cotizacloud` | vhost |
| `/etc/ssl/cloudflare/` | Origin Certificate + llave |
| `/root/.secrets/cloudflare.ini` | API token de Cloudflare para DNS-01 (600) |

**`config.php` — cambios vs cPanel:** DB `cotizacloud`/`cotizacloud`/pass nuevo ·
`APP_SECRET` nuevo (el viejo era el default débil) · `APNS_KEY_PATH` y
`VAPID_PRIVATE_PEM` → `/var/www/cotizacloud-keys/`. SMTP, MP, APNs IDs y
`SUPERADMIN_EMAIL` iguales.

**Deploy futuro:** `git push` a `main` → en el server `/usr/local/bin/deploy-cotizacloud.sh`.

---

# PARTE B — LO QUE FALTA

### 1. Llave APNs (push iOS)
`AuthKey_D2AW3CT2UF.p8` **no está en el repo** (`.gitignore *.p8`). Estaba en el
cPanel viejo en `/home/key/`. Subirla por `scp` a `/var/www/cotizacloud-keys/`,
`chown www-data:www-data` + `chmod 600`. Sin ella el push iOS no firma.
(El `vapid_private.pem` sí está en el repo y ya se copió.)

### 2. Ajustes de nginx pendientes
- **`location ^~ /.well-known/acme-challenge/`** en el bloque de `cotiza.cloud`
  (el deny de dotfiles lo bloquea → certbot HTTP-01 falla). El catch-all ya lo tiene.
- **Bloquear `/data/`** (tiene `comisiones_pagadas_*.json`).
- **HSTS**: `add_header Strict-Transport-Security "max-age=31536000" always;`
  (vivía en el `.htaccess` de `public_html`, que nunca estuvo en el repo; sin
  `includeSubDomains`, igual que el viejo).
- **Limpiar headers falsificables**: borrar `CF-Connecting-IP` y `X-Forwarded-For`
  del tráfico que no venga de rangos de Cloudflare. `ip_real()` los lee primero,
  así que sin esto cualquiera puede fingir una IP interna y burlar el Escudo o
  envenenar el aprendizaje de IPs. Se resuelve en nginx para no tocar una función
  que usa medio sistema.
- **`max_execution_time` a 120** (php.ini). `Radar::recalcular_empresa()` tiene
  guardia interna de 120 s; con el límite en 60 la guardia es código muerto y PHP
  fatalea antes → **página en blanco** en `/radar` y `/dashboard`.
- `session.use_strict_mode = 0` explícito en el pool (cinturón y tirantes; el
  rename de `session_name` ya elimina la colisión de raíz).

### 3. SSL
- **Wildcard** `cotiza.cloud` + `*.cotiza.cloud` por **DNS-01** con el plugin
  `dns-cloudflare` y el API token (Zone·DNS·Edit, zona `cotiza.cloud`).
  Auto-renueva. Se puede emitir **antes** del corte porque no requiere que el DNS
  apunte al VPS.
- **Dominios custom** (`hermosillo/obregon/nogales.ontimecocinas.com`): cert por
  **HTTP-01**, solo funciona **cuando ya apuntan a Contabo**.
  ⚠️ **Es requisito duro, no cosmético** — ver Parte C, hallazgo CRÍTICO 2.
- Crear `nuevo-dominio.sh <dominio>` para automatizar el cert de cada empresa nueva.

### 4. Tuning de MySQL (del plan maestro, pendiente)
`innodb_buffer_pool_size` a **~50-60% de la RAM** (≈6 GB con 11 GB). El default de
MariaDB son **128 MB** — con 2347 cotizaciones y las queries del Radar, esto es la
diferencia entre "se siente rápido" y "se siente igual que el compartido".
Editar `my.cnf`, reiniciar MariaDB, verificar.

### 5. Correo — relay (del plan maestro, PENDIENTE y con dependencia)
Hoy `SMTP_HOST = mail.cotiza.cloud` → **el correo sale por el host viejo**.
Funciona durante y después del corte… **pero muere el día que canceles el hosting
viejo**. Antes de cancelar:
1. Crear cuenta de relay (Brevo o Amazon SES).
2. Apuntar `SMTP_*` del `config.php` al relay.
3. Autenticar el dominio en el relay (SPF/DKIM que pidan) → agregar esos TXT en
   Cloudflare en **gris**.
4. Probar envío real y revisar que **no caiga en spam**.

> **Nunca mandar transaccionales desde la IP del VPS directo**: IP nueva sin
> reputación = spam.

Además: `api/soporte.php` usa `mail()` de PHP (no PHPMailer) para avisarte de
**leads del landing**. Ubuntu limpio no trae MTA → esos avisos se pierden en
silencio. Instalar `msmtp-mta` apuntando al mismo SMTP, o cambiar esas 2 llamadas
a `Mailer::enviar()`.

### 6. Cron (⚠️ NO activar antes del corte)
```
0 3 * * * /usr/bin/php /var/www/cotizacloud/cron/procesar_suscripciones.php >> /var/log/cotizacloud-cron.log 2>&1
```
Ese script **cobra tarjetas en MercadoPago** y manda 5 tipos de email. Si corre en
Contabo mientras el host viejo sigue vivo, **duplica cobros y correos**. Instalarlo
**después** del corte.
Nota: el cron corre por **CLI**, que no recibe el `APP_ENV` del `fastcgi_param` →
conviene `define('ENV','production')` duro en `config.php`.
No hay otros crons reales: `tools/` y los `.php` de la raíz son one-shot o dev
(`cleanup_bot_views.php` es de la era WordPress y está roto: pide `wp-load.php`).

### 7. Probar SIN tocar el DNS (`/etc/hosts`)
En la Mac, agregar temporalmente:
```
212.28.186.247  cotiza.cloud www.cotiza.cloud apple-review.cotiza.cloud
```
**Checklist:** login (asesor y superadmin) · dashboard · Radar · mesa · termómetro ·
slugs `/c/`, `/v/`, `/r/` · un dominio custom · MercadoPago · push · correo
transaccional · SSL sin errores. Quitar las líneas al terminar.

### 8. EL CORTE (cutover) — secuencia exacta
1. **Dump FINAL** de la BD del host viejo → importar en Contabo.
   ⚠️ **Obligatorio.** Sin esto se pierden las cotizaciones/pagos de los últimos
   días, y sobre todo `user_sessions` / `radar_visitors_internos` — lo que deja
   **las Capas 0 y 1 del Escudo ciegas** hasta que cada asesor vuelva a loguearse.
2. **Copia FINAL de archivos** (`uploads/logos`, `public/assets/uploads`, `data/`)
   para el drift desde la primera copia.
3. Cambiar en Cloudflare la IP de `cotiza.cloud` y `*.cotiza.cloud` → `212.28.186.247`
   (**en gris**). Los CNAME de OnTime siguen solos en ~5 min.
4. **Inmediatamente**: emitir certs HTTP-01 de los 3 dominios custom.
5. Verificar en vivo: web, login (asesor + superadmin), slugs, Radar, correo, MP, push.

### 9. Post-corte
- Dejar el **host viejo prendido 3-7 días** como rollback. No borrar nada.
- Instalar el cron.
- **Reactivar el webhook de MercadoPago con validación HMAC** (ya sin Imunify360).
  Actualizar la URL en el panel de MP si cambia.
- Monitorear `logs/error.log`, rendimiento del dashboard/Radar, entrega de correo.
- Query de control del Escudo (si `capa_0_logueado` cae a casi cero mientras
  `cliente_real` sube, algo está mal):
  ```sql
  SELECT decision, COUNT(*) FROM escudo_log
  WHERE created_at > NOW() - INTERVAL 2 DAY GROUP BY decision;
  ```
- Actualizar `CLAUDE.md`: ya no es cPanel, nuevas rutas, cómo se despliega ahora.

### 10. Seguridad / higiene posterior
- **Rotar credenciales expuestas en chat**: `MP_ACCESS_TOKEN`, password SMTP,
  y el API token de Cloudflare. El Origin Certificate se puede regenerar en 1 clic.
- phpMyAdmin protegido (ruta oculta + IP), o TablePlus por túnel SSH (cero exposición).
- Login SSH por llave y desactivar password (**probar en una 2ª terminal antes**
  de desactivar, para no quedar fuera).
- Confirmar por `curl` que `/migrations/*.sql`, `/tools`, `/core`, `/docs`,
  `/cron`, `/data` devuelven 404.
- Purga de `escudo_log` (>30 días) + índice `KEY idx_vid_time (visitor_id, created_at)`.

## ROLLBACK (si algo sale mal en el corte)
En Cloudflare **regresar la IP** de `cotiza.cloud` y `*.cotiza.cloud` a
`107.161.23.124`. Propaga en minutos. Los dominios custom vuelven solos por el
CNAME. El sistema regresa al host viejo mientras se diagnostica.

---

# PARTE C — AUDITORÍA PRE-CORTE (Escudo / Radar / cookies)

Tres auditorías cruzadas del código frente al server nuevo. **Conclusión: el
código está sano; los riesgos grandes son de configuración.** Los bugs que
salieron son **preexistentes** — ya estaban en producción, no los causó la
migración.

## CRÍTICO

**1. `ENV=production` es el fix #1.** Con `development`, `display_errors=1`: en
`cotizacion.php` el `setcookie('cz_vid')` y un `header('Location')` del bloque de
tracking corren **antes** del HTML. Cualquier Notice previo emite output →
*headers already sent* → **`cz_vid` no se planta** → Capa 1 rota de forma
intermitente, y errores/SQL visibles en el slug del cliente. **Ya aplicado.**

**2. Los dominios custom SIN HTTPS válido rompen el LOGIN, no solo el Escudo.**
`login_post.php:126-161`: tras un login correcto, si la empresa tiene
`dominio_custom`, **siempre** redirige por `https://<custom>/api/safari-bridge…`.
Sin cert válido → interstitial del navegador → **el usuario nunca llega al
dashboard**. Y para el **superadmin la cadena recorre TODOS los dominios custom**:
uno roto y tampoco entras tú. Es `header()+exit`, no hay fallback.
Además las cookies del bridge son `Secure` hardcoded → sobre HTTP el navegador las
descarta **en silencio** y el bridge igual pinta "Escudo activado" (le miente al
asesor). **→ Certs de los 3 dominios custom ANTES de mover su DNS.**

**3. El dump de BD envejece.** Ver Parte B, punto 8.1.

**4. Certbot HTTP-01 falla con el deny de dotfiles.** Falta la excepción
`acme-challenge` en el bloque de `cotiza.cloud`. Ver Parte B, punto 2.

## Correcciones de código aplicadas (commit `28f2505`)

| # | Archivo | Qué se corrigió |
|---|---|---|
| 1 | `core/Auth.php` | `session_name('cza_php')` en vez de `SESSION_NAME`. La sesión nativa de PHP compartía nombre con la cookie del token de auth; **funciona hoy solo porque `session.use_strict_mode=0`**. Con `strict_mode=1` PHP reemite la cookie con su propio id y **pisa el token** → deslogueo masivo y **Capa 0 ciega**. Reproducido empíricamente. Efectos laterales que también elimina: el token quedaba como nombre de archivo en disco (`sess_<token>`), y PHP le plantaba `cza_session` a **clientes anónimos**, ensuciando `escudo_log` (no distinguía asesor de cliente). |
| 2 | `api/set_vid.php` | **Eliminado.** Endpoint huérfano (cero referencias, sin ruta en Router) y el único de `api/` sin el guard `COTIZAAPP`. Permitía fijar `cz_vid` arbitrario 730 días — **desactiva la Capa 1 de un asesor**, o vuelve invisible a un visitante — más open redirect sin validar host. Estaba **vivo** en el server viejo (el `.htaccess` servía archivos existentes). |
| 3 | `core/Router.php` | `redirect_to_subdomain`: 301 → **302** + guardia anti-bucle. El 301 se cachea **permanentemente** en el navegador; si la resolución del dominio custom falla (`config.php` se traga el error de PDO en silencio), el destino es el mismo host → bucle infinito cacheado. |
| 4 | `public/cotizacion_inmueble.php` | El beacon mandaba `scroll_max` pero `api/track.php` lee **`max_scroll`** → **el scroll de inmuebles llegaba SIEMPRE en 0** (roto desde mayo). Efecto: el ghost cleanup (`scroll_max=0 AND visible_ms<200`) **borraba visitas reales de clientes** y los buckets de lectura nunca disparaban. |
| 5 | `public/cotizacion.php` | CAPI de Meta **diferido** (`register_shutdown_function` + `fastcgi_finish_request`, el patrón que ya usa la notificación de DI). `capi_enviar()` usa `curl_exec` **síncrono** (timeout 5 s) en la ruta caliente: bloqueaba el render del slug en cada visita nueva. |
| 6 | `modules/radar/Radar.php` | `score()` ahora filtra `es_interno = 0` en las dos variantes de la query de `quote_sessions`, como ya hacían `layout.php` y `track.php`. |

## Pendientes de la auditoría (no bloqueantes)

- **`safari_bridge.php`**: el parámetro `next` se valida solo con
  `str_starts_with('https://')` y **no va dentro del payload firmado por HMAC** →
  open redirect para quien capture un token. Firmarlo o validarlo contra la lista
  de `dominio_custom` + `BASE_DOMAIN`.
- **`detectar_empresa_slug()`** abre una **segunda conexión PDO** por request en
  dominios custom y se traga el error en silencio. Cachear y loguear el fallo.
- **`Auth::logout()`** no limpia la cookie host-only del dominio custom (higiene,
  no bypass: el token se invalida en BD).
- **`cz_vid` duplicada** (host-only vs `.cotiza.cloud`): ante duplicado gana la
  **más vieja**. Si la host-only es la antigua, el bridge registra un valor y el
  navegador manda otro → Capa 1 falla en silencio para ese asesor.
- **`MarketingPixels.php:166-168`** usa `REMOTE_ADDR` y `isset($_SERVER['HTTPS'])`
  → afecta la atribución de Meta CAPI, no el Escudo.
- **`dashboard/index.php`**: subquery a `quote_sessions` sin filtro de empresa ni
  `es_interno` → full scan en cada carga.
- **`propiedad_foto.php`** borra fotos con una ruta mal armada (le falta `/public`)
  → las "borradas" siguen en disco. Por eso el archivo de uploads se copia
  completo, sin confiar en la BD como inventario.

## Verificado que está BIEN (no tocar)

- `ip_real()` lee `CF-Connecting-IP` primero → correcto detrás de Cloudflare.
  (El riesgo de spoofing se cierra en nginx, ver Parte B punto 2.)
- Cloudflare **preserva el Host** → la lógica `.cotiza.cloud` vs host-only intacta.
- **El `APP_SECRET` nuevo NO cierra sesiones**: las sesiones son tokens en BD, no
  HMAC. Sus 3 únicos usos son tokens efímeros (5 min / 24 h) que se firman y
  verifican en el mismo servidor.
- `SameSite=Lax` no rompe el bridge: son navegaciones top-level GET. El beacon
  postea a ruta relativa (mismo origen).
- `Radar::BOT_IP` está vacío y **no hay IPs ni rutas del server viejo** en el
  código de Escudo/Radar/tracking/push.
- Los endpoints del slug (`/api/track`, `/api/quote-action`, `/api/cot-feedback`,
  `/api/safari-bridge`) están registrados en el Router — **ningún JS llama a un
  `.php` literal**, así que el bloqueo de nginx no rompe el tracking.
- `vendor/` es PHPMailer vendorizado a mano: **no correr `composer install`**.
- `GRANT ALL PRIVILEGES` es necesario: el código hace `ALTER TABLE` y
  `CREATE TABLE IF NOT EXISTS` en runtime (`trial_info()`, superadmin, tickets…).

---

## Gotchas técnicos encontrados en la ejecución

- **Las desconexiones SSH** se resuelven con `ServerAliveInterval` en la Mac +
  trabajar dentro de **tmux** (`tmux new -s mig` / `tmux attach -t mig`).
- **La llave privada del cert se enmascara como `••••`** al pegarla por SSH/chat
  (el cliente la redacta al ver `-----BEGIN PRIVATE KEY-----`). Transferirla en
  **base64** (`base64 -w64`) y decodificar con `base64 -d > archivo.key`. Si queda
  en DER (no empieza con `-----BEGIN`), convertir con
  `openssl pkey -inform DER -in k.key -outform PEM -out k.pem`. Verificar el par:
  los `openssl … -modulus | openssl md5` de cert y llave deben coincidir.
- **nginx 1.24** usa `listen 443 ssl http2;` (NO `http2 on;`, que es 1.25+).
- El dump se importa con `SET FOREIGN_KEY_CHECKS=0` (47 FKs; el dump no las desactiva).
- Pegar bloques largos por SSH se enreda: preferir bloques compactos.
- **Los logos NO viven en `public/`** sino en `{ROOT}/uploads/logos/`. Son 3 árboles
  de archivos distintos, no uno.
- **`data/` nunca se desplegó** por `.cpanel.yml` (deliberado): es estado runtime y
  se copia a mano. `comisiones_pagadas_*.json` es el más caro de perder (riesgo de
  doble pago).
- **NO tocar `APP_TIMEZONE`**: `DB.php` sincroniza MySQL con `date('P')`. Si queda
  en UTC, las filas nuevas salen 7 h corridas respecto a las importadas y se
  desfasan las ventanas de 24/48 h del Radar.
- Los CNAME de OnTime tienen TTL 3600, pero **la resolución final sigue el TTL del
  A de `cotiza.cloud`** (~300 s en Cloudflare) → siguen el cambio en minutos sin
  tocar DirectAdmin.
