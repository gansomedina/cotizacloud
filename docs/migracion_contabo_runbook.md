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
| 12 | 6 correcciones de código (ver Parte C) commiteadas y desplegadas | ✅ |
| 13 | **CORTE HECHO** (27 jul): dump FINAL re-importado, DNS a `212.28.186.247` en gris | ✅ |
| 14 | Let's Encrypt wildcard `cotiza.cloud` + `*.cotiza.cloud` (DNS-01, auto-renueva) | ✅ |
| 15 | Let's Encrypt de los 3 dominios custom OnTime + su bloque 443 en nginx | ✅ |
| 16 | Correo separado: `mail` → A → `107.161.23.124`; MX → `mail.cotiza.cloud` | ✅ |
| 17 | Tuning MariaDB: `innodb_buffer_pool_size=6G`, `max_connections=200` | ✅ |

### Verificación post-corte (todo desde afuera, con certs reales)
`https://cotiza.cloud/login`→200 · `/landing`→200 · `granitodepot.cotiza.cloud`→302 ·
`hermosillo/obregon/nogales.ontimecocinas.com/login`→302 (SSL válido) ·
**correo enviado y recibido** con `Mailer::enviar()` desde el server nuevo ·
BD final: 2347 cotizaciones · 257 ventas · 9 empresas · **868 user_sessions**
(los asesores NO tuvieron que volver a loguearse).

**Radar verificado byte a byte:** `md5sum` de `modules/radar/index.php` y
`core/layout.php` **idénticos** entre viejo y nuevo. El único archivo distinto es
`Radar.php`, y su md5 previo coincide exactamente con el commit anterior a la
corrección — o sea, la única diferencia es el filtro `es_interno = 0`.

### ⛔ EL FALLO MÁS CARO DEL CORTE: 15 h sin poder crear cotizaciones

**Qué pasó.** Desde el corte (26 jul 18:41) hasta el mediodía del 27, NADIE pudo
crear, editar, enviar, clonar ni convertir una cotización. Lo reportó un asesor,
no el monitoreo.

**Causa raíz.** MariaDB 10.11 trae `sql_mode` **estricto** por defecto
(`STRICT_TRANS_TABLES,...`); el hosting viejo lo tenía relajado. La tabla
`cotizacion_log` arrastra una columna huérfana:
```sql
`evento` varchar(80) NOT NULL,     -- sin DEFAULT; el código migró a `accion` hace tiempo
`accion` varchar(80) DEFAULT NULL,
```
Los INSERT del código escriben `accion` y **no** `evento`. Con modo relajado MySQL
le ponía `''` y guardaba; con modo estricto responde
`1364 Field 'evento' doesn't have a default value` y **revienta la transacción
entera** de `crear.php`. Cinco caminos afectados: `crear.php:221`,
`guardar.php:256`, `enviar.php:36`, `clonar.php:124`, `convertir.php:150`.

**Arreglo aplicado** (instantáneo, solo metadatos, y conserva el modo estricto):
```sql
ALTER TABLE cotizacion_log ALTER COLUMN evento SET DEFAULT '';
ALTER TABLE venta_log      ALTER COLUMN evento SET DEFAULT '';
```
Se prefirió esto sobre relajar el `sql_mode` global: relajarlo también lo
arreglaba de un golpe, pero volvería a **esconder** esta clase de error en todo el
sistema, que es justo lo que nos tuvo horas a ciegas. Barrido posterior: fuera de
esos cinco, **ningún otro INSERT de producción** omite una columna `NOT NULL` sin
default (los que salen en `tools/` son scripts de simulación).

**Por qué tardó 15 horas en detectarse — el error de método.** La verificación
post-corte comparó conteos entre las dos bases y dio `cotizaciones 2347 = 2347`,
`ventas 257 = 257`, y se leyó como *"migración íntegra"*. Era cierto **y era la
evidencia del problema al mismo tiempo**: los conteos coincidían porque no se
había creado NADA desde el corte. Se probaron rutas, SSL, dominios, correo, push y
lecturas — **ninguna escritura real**.

**REGLA para cualquier corte futuro — prueba de humo de ESCRITURA, obligatoria,
antes de dar por bueno el corte:**
1. Crear una cotización de prueba (toca `cotizaciones`, `cotizacion_lineas`,
   `cotizacion_log`, `folios`).
2. Editarla y guardarla.
3. Enviarla.
4. Convertirla a venta y registrar un abono (toca `ventas`, `recibos`, `venta_log`).
5. Aceptarla desde el slug público (toca `quote_sessions`, `quote_events`).
6. Borrar lo de prueba.

Si los conteos post-migración salen **idénticos** varias horas después del corte,
eso no es señal de salud: es señal de que **nadie está escribiendo**. Verificarlo.

### Gotcha del corte: "invalid signature" durante la propagación
`api/safari_bridge.php:33` devuelve `invalid signature` si el token del bridge se
**firma** en un servidor y se **verifica** en otro — pasa mientras el DNS propaga,
porque el server viejo tiene el `APP_SECRET` viejo y el nuevo el nuevo. No es un
bug: se resuelve solo al terminar la propagación, o limpiando la caché DNS local
(`sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder`) y reabriendo el
navegador. Ojo mientras dure: quien caiga en el server viejo trabaja sobre la
**BD vieja** y ese trabajo no se refleja en Contabo.

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

### 1. Llave APNs (push iOS) ✅ HECHO (27 jul)
`AuthKey_D2AW3CT2UF.p8` **no está en el repo** (`.gitignore *.p8`). Estaba en el
cPanel viejo en `~/key/`. Ya está en `/var/www/cotizacloud-keys/` con
`root:www-data` + `chmod 640`. (El `vapid_private.pem` sí está en el repo y ya
se copió.)

**No transferir el `.p8` pegándolo como texto**: empieza con
`-----BEGIN PRIVATE KEY-----` y el cliente/chat lo enmascara con `••••` (mismo
problema que la llave del cert Origin). Va en **base64**:
`base64 -w0 ~/key/AuthKey_*.p8` en el origen → `base64 -d > destino` en Contabo.

**Verificado contra Apple de verdad** (no solo "el archivo existe"): se firma un
JWT ES256 con el código real de la app (`generar_jwt_apns` por Reflection) y se
POSTea a `api.push.apple.com` con un device token falso de 64 ceros:
```
JWT: eyJhbGciOiJFUzI1NiIsImtpZCI6IkQyQVczQ1Qy...
HTTP: 400 → {"reason":"BadDeviceToken"}
```
`BadDeviceToken` **es el éxito**: Apple validó llave + Key ID + Team ID y solo
rechazó el token inventado. `403 InvalidProviderToken` sería el fallo real.
No le llega notificación a ningún dispositivo, así que la prueba es repetible.

### 1b. Integridad de datos post-corte — VERIFICADA (27 jul)
Se comparó viejo vs Contabo tabla por tabla: `cotizaciones` 2347=2347 ·
`ventas` 257=257 · `recibos` 318=318 · `quote_sessions` 2606=2606 ·
`radar_feedback` 599=599 · `cot_feedbacks` 10=10 · `clientes` 880=880, con los
mismos `MAX(created_at)`. Y el contador agregado: `SUM(visitas)` **2456 = 2456**.
Cero divergencia.

Momento exacto del corte: `21:41:56 -0400` en el access log del viejo ≡
`18:41:56` (Hermosillo) del último `quote_sessions`. Contabo ya registraba
visitas a las `18:44:56` → la ventana de riesgo fue de **~3 minutos**.

Hipótesis descartada con datos: se supuso que las visitas con `200` posteriores
al corte en el log del viejo las había filtrado el Escudo como internas — falso,
`escudo_log` también está vacío en esa ventana. La explicación real está en el
código: `cotizacion.php` solo inserta en `quote_sessions` **y** en `escudo_log`
cuando la sesión NO existía; un cliente que recarga con su `cz_vid` vigente deja
`200` en el log sin fila nueva. Los slugs `/v/` (ventas) tampoco usan
`quote_sessions`.

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

### 5. Correo — relay Brevo ✅ HECHO (27 jul)

**Envío ya NO depende del hosting viejo.** `Mailer::enviar()` probado desde el
server nuevo con `ENVIADO OK`.

Config aplicada en `/var/www/cotizacloud/config.php` (respaldo en
`config.php.bak-antes-brevo`):
```
SMTP_HOST   = smtp-relay.brevo.com
SMTP_PORT   = 587
SMTP_SECURE = tls          ← STARTTLS, NO 'ssl' (eso es para 465)
SMTP_USER   = b36550001@smtp-brevo.com
SMTP_PASS   = (SMTP key de Brevo, empieza con xsmtpsib-)
SMTP_FROM   = noreply@cotiza.cloud   (sin cambio)
```

**DNS agregado en Cloudflare (todo DNS-only/gris):**
| Tipo | Nombre | Contenido |
|---|---|---|
| TXT | `@` | `brevo-code:0808f1ab4967b9a1d7da0b64067dccef` |
| CNAME | `brevo1._domainkey` | `b1.cotiza-cloud.dkim.brevo.com` |
| CNAME | `brevo2._domainkey` | `b2.cotiza-cloud.dkim.brevo.com` |

**⛔ Registros de Brevo que se OMITIERON a propósito** (y por qué):
- `CNAME mail → …brand.brevosend.com` — **rompería el correo**: `mail.cotiza.cloud`
  es un registro **A** al server viejo y el **MX apunta ahí**. Un CNAME de branding
  ahí mata la recepción de correo.
- `CNAME img.mail` y `CNAME r.mail` — dependen del anterior.
- El `_dmarc` que pide Brevo — **ya existe uno**; solo puede haber un DMARC por dominio.

Brevo muestra esos 3 como "mismatch": es **cosmético** (branded tracking links).
La autenticación real la dan el `brevo-code` + los 2 DKIM, y el envío funciona.
Si algún día se quiere el branding, recrear el dominio en Brevo usando
**`send`** como subdominio de marca en vez de `mail`.

**⚠️ Filtro de IPs autorizadas:** Brevo bloquea el SMTP desde IPs no dadas de alta
(el síntoma es `SMTP Error: Could not authenticate`, que despista). Está autorizada
`212.28.186.247`. **Si algún día cambia la IP del servidor, el correo deja de salir
hasta darla de alta en Brevo.**

### 5b. Correo — lo que SIGUE dependiendo del hosting viejo
El **envío** ya salió del hosting viejo (Brevo). Lo que TODAVÍA depende de él:

1. ~~**Recepción de correo (MX)** — `MX → mail.cotiza.cloud → 107.161.23.124`~~
   ✅ **RESUELTO (28 ago) con Cloudflare Email Routing — ver §5c.**
2. ~~**`api/soporte.php` usa `mail()` de PHP**~~ ✅ **RESUELTO por partida doble**:
   las 2 llamadas se cambiaron a `Mailer::enviar()`, y además se instaló
   `msmtp-mta` (ver §6), así que `mail()` también funciona ya en el servidor.
   Falta la prueba de humo: mandar un mensaje por el chat del landing y confirmar
   que llega el aviso del lead.

### 5c. Correo — recepción migrada a Cloudflare Email Routing ✅ HECHO (28 ago)

Último lazo con Limitless cortado. **El correo ya no toca el hosting viejo en
ninguna dirección**: sale por Brevo, entra por Cloudflare y se reenvía a Gmail.

**Por qué Cloudflare Email Routing y no Zoho/Google/buzón en el VPS:** el DNS ya
vivía en Cloudflare, es gratis, no hay buzón que mantener, y el catch-all cubre
*cualquier* dirección `@cotiza.cloud` (incluidas las que un tercero pudiera tener
registradas como contacto: registrador, Apple, MercadoPago, banco). El único buzón
que existía (`noreply@`, 175 KB) tenía **puros rebotes**, nada que rescatar.

**Limitación asumida:** Email Routing **solo recibe**. Al responderle a un cliente,
la respuesta sale de la dirección personal, no de `@cotiza.cloud`. El atajo de
"enviar como" desde Gmail vía el SMTP de Brevo **no funciona**: Brevo tiene filtro
de IPs autorizadas con solo `212.28.186.247`. Para escribir desde `hola@cotiza.cloud`
haría falta Google Workspace o Zoho, y volver a mover el MX (~15 min).

**Estado final de la zona:**
```
MX   cotiza.cloud  →  73 route3 / 81 route1 / 95 route2 .mx.cloudflare.net
TXT  cotiza.cloud  →  v=spf1 a include:spf.brevo.com include:_spf.mx.cloudflare.net ~all
Catch-all          →  josealfonsomedina@gmail.com
```

**Orden de ejecución (importa):**
1. Verificar la dirección de destino en Cloudflare (no toca DNS).
2. **Fusionar el SPF ANTES de activar** (ver trampa abajo).
3. **Borrar el MX viejo primero** — Cloudflare se niega a agregar los suyos
   mientras exista un MX externo (*"Existing non-Cloudflare MX records conflict"*).
   El hueco sin MX no pierde correo: el emisor recibe fallo temporal y reintenta.
4. `Add missing records` → activar → poner el catch-all en **Send to an email**
   (el default es **Drop**, que tira el correo en silencio).

**⚠️ TRAMPA 1 — el SPF duplicado.** `Add missing records` agrega los 5 de golpe,
sin dejar elegir, e incluye `TXT cotiza.cloud "v=spf1 include:_spf.mx.cloudflare.net ~all"`.
Un dominio **solo puede tener UN SPF**; dos = PERMERROR = el correo transaccional
a spam. Por eso el SPF se fusiona ANTES. Hecho así, Cloudflare detectó el existente
y **no** agregó el suyo. La fila del SPF queda marcada **"Missing" para siempre** en
su panel: es correcto y esperado (su propia doc manda fusionar). **No volver a darle
a "Add missing records"** por arreglar ese "Missing" — recrearía el duplicado.

**⚠️ TRAMPA 2 — la prioridad.** El botón dice *agregar*, no *reemplazar*. Los MX
nuevos entran con prioridad 73/81/95 y el viejo tenía **0**. En correo gana el número
más bajo: si el viejo sobrevive, todo sigue yendo al host viejo aunque los nuevos existan.

**Lo que SPF quitó y por qué:** `mx` (autorizaba lo que apuntara el MX, o sea el host
viejo) y `include:relay.mailchannels.net` (relay de salida de cPanel/LiteSpeed, muere
con la cuenta). Se conservó `a` como red de seguridad y `include:spf.brevo.com`.

### 🔑 Lo que de verdad sostiene el correo: DKIM, no SPF

Hallazgo de los encabezados reales de un correo entregado en Gmail:
```
Return-Path: <bounces-...@ha.d.sender-sib.com>
spf=pass   smtp.mailfrom=bounces-...@ha.d.sender-sib.com
dkim=pass  header.i=@cotiza.cloud header.s=brevo2
dmarc=pass header.from=cotiza.cloud
```
El SPF se evalúa contra el dominio del **sobre**, y Brevo lo reescribe al suyo → **el
registro SPF de `cotiza.cloud` ni siquiera se consulta** para el correo de la app. Lo
que alinea y hace pasar el DMARC es la **firma DKIM con el selector `brevo2`**.

**Consecuencia operativa: `brevo1._domainkey` y `brevo2._domainkey` son los dos
registros intocables de la zona.** Si se rompen, DKIM falla → DMARC falla → todo a
spam, aunque el SPF esté perfecto. El SPF vale tenerlo correcto (defensa en
profundidad), pero hoy está dormido.

Corolario: los rebotes van a `ha.d.sender-sib.com` (Brevo), no a `noreply@`. Por eso
ese buzón solo había juntado 175 KB en un año.

### 5d. Inventario previo a cancelar Limitless (28 ago) — todo limpio

| Revisión | Resultado |
|---|---|
| Registrador del dominio | **GoDaddy**, no Limitless → cancelar el hosting NO arrastra el dominio. Expira 2027-03-09. Verificado por RDAP (`https://rdap.registry.cloud/rdap/domain/cotiza.cloud`) |
| Dominios en la cuenta | `cotiza.cloud`, `*.cotiza.cloud` y los 3 de OnTime — **todos resuelven a 212.28.186.247 y responden `nginx/1.24.0`**, con Let's Encrypt vigente. Las entradas del cPanel son cascarones huérfanos |
| Cron jobs | **Ninguno** → descartado el riesgo de `procesar_suscripciones.php` cobrando en MercadoPago por duplicado contra la base vieja |
| Bases de datos | Una (`cotizacl_cotizacloud`, 17.67 MB) |
| **Escrituras posteriores al corte en la BD vieja** | **0 en las 4 tablas** (`cotizaciones`, `ventas`, `recibos`, `quote_sessions`). Ningún resolvedor rezagado escribió ahí. Esta es la revisión que exige el §"Consecuencia de negocio" de los gotchas |
| Buzones | 1 (`noreply@`, 175 KB), puros rebotes |

Query usada (solo lectura, en phpMyAdmin del cPanel viejo):
```sql
SELECT 'cotizaciones' AS tabla, COUNT(*) AS despues_del_corte, MAX(created_at) AS ultimo
  FROM cotizaciones   WHERE created_at > '2026-07-27'
UNION ALL SELECT 'ventas',   COUNT(*), MAX(created_at) FROM ventas   WHERE created_at > '2026-07-27'
UNION ALL SELECT 'recibos',  COUNT(*), MAX(created_at) FROM recibos  WHERE created_at > '2026-07-27'
UNION ALL SELECT 'quote_sessions', COUNT(*), MAX(created_at) FROM quote_sessions WHERE created_at > '2026-07-27';
```

**Simulacro "como si Limitless ya no existiera" (28 ago).** En vez de esperar a la
cancelación para borrar los registros del host viejo, se borraron **antes**, con la
cuenta todavía viva: si algo se rompía, se veía con la IP aún en nuestro poder. Es el
orden correcto para un simulacro. Borrados:
```
mail.cotiza.cloud    A    107.161.23.124
*.cotiza.cloud       TXT  "v=spf1 +a +mx +ip4:107.161.23.124 include:relay.mailchannels.net …"
default._domainkey   TXT  "v=DKIM1; k=rsa; p=…"   (DKIM del servidor viejo)
```
Los tres apuntaban a una IP que Limitless reciclará y le dará a otro cliente. El del
comodín es el más peligroso: autorizaba a ese futuro desconocido a enviar correo como
`loquesea.cotiza.cloud`. (Opcional, mejor que borrarlo: dejar `*.cotiza.cloud TXT
"v=spf1 -all"` — declara que ningún subdominio envía.)

Resultado del simulacro: **envío OK** (DKIM `brevo2` PASS, DMARC PASS) y **recepción
OK** (`prueba2@cotiza.cloud` → Gmail, con la firma DKIM del emisor original intacta
tras el reenvío). Con eso, cancelar Limitless es un trámite administrativo sin
consecuencia técnica. Solo falta bajar el *Full Account Backup* del cPanel antes de
darle clic, porque cancelar sí es irreversible.

### Patrón para migrar otro sitio que SÍ tenga correo (ej. ontimecocinas.com)
Su MX apunta al **dominio** (`MX → ontimecocinas.com`), así que si se mueve el
registro A del apex al VPS, **el correo se va con él y se cae**. Orden correcto:
1. Crear `mail.ontimecocinas.com` → **A** → IP del servidor de correo actual.
2. Cambiar el `MX` → `mail.ontimecocinas.com`.
3. Esperar propagación y **verificar que el correo sigue llegando**.
4. Recién entonces mover `ontimecocinas.com` → A → IP del VPS.

Es exactamente lo que se hizo con `mail.cotiza.cloud` en este corte.

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

**✅ HECHO (27 jul) — el cron ya avisa por correo, como hacía cPanel.**
cPanel mandaba la salida del cron por correo porque tenía MTA local; Ubuntu
limpio **no**, así que el cron corría y su salida se perdía. Se instaló `msmtp`
+ `msmtp-mta` (`/usr/sbin/sendmail` → `/usr/bin/msmtp`) apuntando al mismo relay
Brevo. Crontab final:
```
MAILTO=josealfonsomedina@hotmail.com
0 3 * * * APP_ENV=production /usr/bin/php /var/www/cotizacloud/cron/procesar_suscripciones.php 2>&1 | tee -a /var/log/cotizacloud-cron.log
```
El `tee -a` es deliberado: guarda en el log **y** deja la salida en stdout, que
es lo que hace que cron mande el correo (con `>>` a secas no hay salida → no hay
correo). Verificado: envío sin cabecera `From` → msmtp usa el `from` de
`/etc/msmtprc` → `smtpstatus=250 exitcode=EX_OK`. No hace falta `set_from_header`.
Ejecución manual del script OK (`Cobros: 0 ok, 0 err`).

`/etc/msmtprc` se genera leyendo las constantes `SMTP_*` de `config.php` (la
contraseña nunca se teclea), `chmod 600 root:root`. Log en `/var/log/msmtp.log`.
Esto también deja operativa la función `mail()` de PHP para cualquier script que
la use. El comando `mail` NO se instala (viene de `mailutils`, que arrastra
Postfix) — no hace falta: cron usa `sendmail` directamente.

### 6b. Respaldos ✅ HECHO (27 jul)
cPanel traía respaldo del hosting; Ubuntu limpio **no**. Contabo tiene contratada
la **imagen diaria del VPS**, pero eso es otra cosa: la imagen sirve cuando muere
el servidor, no cuando el daño es **lógico** (un borrado por error, una migración
mal hecha, un `DELETE` sin `WHERE`) — para eso hace falta **historial**.

Script: `/usr/local/bin/respaldo-cotizacloud.sh` · cron `0 2 * * *` (una hora antes
del de suscripciones, ambos cubiertos por el `MAILTO` ya existente).

Qué guarda, y por qué cada cosa:
| Archivo | Contenido | Por qué |
|---|---|---|
| `bd_FECHA.sql.gz` | `mysqldump --single-transaction --routines --triggers --events` | la BD entera sin bloquear InnoDB |
| `archivos_FECHA.tar.gz` | `public/assets/uploads`, `uploads`, `data` | subidas de clientes + estado runtime que **no está en git** (`equilibrio.json`, `comisiones_pagadas_*`) |
| `llaves_FECHA.tar.gz` | `/var/www/cotizacloud-keys` | APNs `.p8` + VAPID: sin ellas el push **no se puede reconstruir** |
| `config_FECHA.php.gz` | `config.php` | tiene los secretos y no está en el repo |

Guardas del script:
- **Aborta si el dump pesa < 100 KB** y borra el archivo. Un dump vacío que se
  rota durante 14 días destruye el historial en silencio — es el modo de fallo
  clásico de los respaldos, y el peor, porque solo se descubre al restaurar.
- Rotación local `find -mtime +14 -delete`, y la misma ventana del lado remoto.
- `set -euo pipefail`: si algo truena, el cron manda correo.

**Copia fuera de Contabo — Cloudflare R2** (un respaldo que vive en el mismo
servidor que protege no es un respaldo). Bucket `cotizacloud-respaldos`, token de
API **limitado a ese bucket** (no a la cuenta), config en
`/root/.config/rclone/rclone.conf` con `chmod 600`. ~4 MB por corrida, 14 días
≈ 60 MB: cabe de sobra en los 10 GB gratuitos y R2 no cobra la descarga, que es
justo cuando la vas a necesitar.

⚠️ **Gotcha de rclone con R2 — el `501 Not Implemented`.** La primera corrida
reportó `Failed to copy: NotImplemented` en **todos** los archivos y solo pasó en
el reintento. La subida nunca estuvo mal. La secuencia real, vista con
`--dump headers`:
1. `PUT` → **200 OK**, ETag correcto, archivo completo en R2.
2. R2 responde con la cabecera `X-Amz-Version-Id`.
3. rclone, al verla, hace `HEAD ...?versionId=...` para verificar.
4. **R2 no implementa la API de versiones** → `501`.
5. rclone marca el archivo como fallido aunque ya está arriba y bien.

El reintento "funciona" solo porque el archivo ya existe y rclone lo salta por
tamaño+fecha, sin repetir el HEAD. Solución: `no_head = true` en `rclone.conf`, y
la integridad se comprueba con `rclone check` (compara **MD5** local contra el de
R2, más estricto que el HEAD que se quitó). Verificado: `0 differences found`,
8/8 archivos. También hace falta `no_check_bucket = true`, porque un token
limitado a un bucket no puede hacer el `HeadBucket` de la cuenta.

Los tres comandos de rclone llevan `-q`: en éxito no imprimen nada, así el correo
del cron llega **vacío cuando todo está bien**. Un correo que siempre trae ruido
se termina ignorando, y entonces el día que falle de verdad tampoco se lee.

**Restauración — probada, no supuesta** (27 jul, contra una BD desechable
`prueba_restauracion`): 2350 cotizaciones · 257 ventas · 883 clientes · 9 empresas
· 17 usuarios. Un respaldo que nunca se restauró es una hipótesis.
```bash
gunzip -c /var/backups/cotizacloud/bd_FECHA.sql.gz | mysql -u root prueba_restauracion
```

### 6c. Endurecimiento del servidor ✅ HECHO (27 jul)
cPanel traía firewall, parches y protección de SSH del hosting. Ubuntu limpio en
una IP pública **no**. Revisión completa y lo que se encontró:

**Ya estaba bien (no se tocó):** `ufw` activo negando todo lo entrante salvo
22/80/443 · **MariaDB escuchando solo en `127.0.0.1`** (inalcanzable desde
internet, que es lo que hace que el firewall no sea la única defensa) ·
`fail2ban` activo · `unattended-upgrades` activo · los 7 servicios en `enabled` ·
zona horaria `America/Hermosillo` (deliberada, ver el gotcha de `APP_TIMEZONE`).

**⚠️ El hallazgo: SSH aceptaba contraseña de root desde internet.**
```
permitrootlogin yes · passwordauthentication yes · authorized_keys VACÍO
1555 intentos fallidos en el log · 314 IPs baneadas por fail2ban
```
`PasswordAuthentication` aparecía **dos veces con valores contrarios**: `no` en
`/etc/ssh/sshd_config` y `yes` en `sshd_config.d/50-cloud-init.conf`. Leer los
archivos no basta — **la verdad la da `sshd -T`**, que mostró `yes` efectivo.

Arreglo: llave ed25519 desde la Mac (`ssh-copy-id`) y luego
`/etc/ssh/sshd_config.d/00-hardening.conf`:
```
PermitRootLogin prohibit-password
PasswordAuthentication no
KbdInteractiveAuthentication no
```
**El prefijo `00` es obligatorio, no estético:** SSH toma el **primer** valor que
encuentra y `sshd_config` hace el `Include` de esa carpeta en la línea 12, antes
de todo. Un archivo `99-*` NUNCA ganaría sobre `50-cloud-init.conf`.

Secuencia segura, en este orden: instalar la llave → probarla con
`ssh -o PasswordAuthentication=no root@IP hostname` (falla a propósito si la
llave no sirve) → `sshd -t` → `sshd -T` para ver cómo quedaría → recargar **sin
cerrar la sesión abierta** → verificar desde una terminal nueva que la llave
entra y que la contraseña da `Permission denied (publickey)`.
Revertir: `rm /etc/ssh/sshd_config.d/00-hardening.conf && systemctl reload ssh`.

**Cada dispositivo necesita su llave.** Para sumar la laptop: generar la llave
ahí, y desde un equipo que ya entra, `echo "ssh-ed25519 AAAA..." >> /root/.ssh/authorized_keys`.
Red de seguridad si se pierden todas: la **consola VNC de Contabo** sigue
aceptando la contraseña de root — se cerró el SSH desde internet, no el acceso
local.

**⚠️ Gotcha: `systemctl is-enabled ssh` devuelve `disabled` y NO es un problema.**
Ubuntu 24.04 arranca SSH por activación de socket: el habilitado es `ssh.socket`
(`enabled` + `active`, dispara `ssh.service` al llegar una conexión). Pista
previa: en `ss -tlnp` el puerto 22 aparece en manos de `systemd` (pid 1) además
de `sshd`. Verificar con `systemctl is-enabled ssh.socket` antes de alarmarse.

**Parches:** 6 de seguridad pendientes, todos glibc (`libc6` y hermanos) +
`locales`. Aplicados con `NEEDRESTART_MODE=l` para **no** reiniciar servicios en
horario laboral (glibc reiniciaría nginx, PHP-FPM y MariaDB). Los procesos vivos
siguen con la glibc vieja en memoria: **la protección real llega con el
reinicio**, pendiente de hacer a hora muerta.

**Rotación del log de la app:** `/etc/logrotate.d/cotizacloud`, semanal, 8
copias comprimidas. Usa `copytruncate` **a propósito**: PHP-FPM mantiene el
archivo abierto, así que si logrotate lo renombrara, PHP seguiría escribiendo en
el archivo viejo y el nuevo quedaría vacío para siempre. Importa más desde que
`config.php` corre con `E_ALL`.

### 6d. Webhook de MercadoPago — VIVO por primera vez (27 jul)
Llevaba meses muerto: **Imunify360**, el firewall del hosting viejo, tenía
baneadas a nivel de red las IPs de MercadoPago (OVH, `51.68.x`). Ese firewall no
existe en Contabo. Verificado el mismo día del corte:

| Modo | URL configurada en el panel MP | Resultado |
|---|---|---|
| Prueba | `/api/mp/webhook` | 200, simulador del panel |
| Productivo | `/hook/c5f8-2a19` | 200, simulador **y** POST manual con `curl` |

Cadena completa confirmada en los logs: MercadoPago → nginx → PHP →
`procesarWebhook`. El `404 Payment not found` del pago simulado `123456` es la
respuesta **correcta**: el código re-consulta cada ID contra la API de MP. Las
peticiones ahora llegan de `35.245.x` (Google Cloud), no de las `51.68.x`
baneadas.

**Por qué la URL productiva es esa ruta rara.** `/hook/c5f8-2a19` la inventamos
para esquivar Imunify360. El motivo ya no existe y se puede unificar a
`/api/mp/webhook`, pero **no vale la pena entrar al panel de MP solo para eso**:
ese panel ya falló una vez (no dejaba guardar), y hoy el webhook es el **único**
camino para activar suscripciones — `MercadoPago::sincronizar()`, el plan B por
polling, quedó sin caller. Unificar cuando se entre al panel por otra razón.

**⚠️ Trampa encontrada antes de causar daño: `$_GET['data.id']` SIEMPRE está
vacío.** PHP renombra los puntos a guiones bajos en los parámetros de la URL:
```
parse_str("data.id=123456&type=payment") → claves: ["data_id", "type"]
```
`validarWebhook()` (`MercadoPago.php:343`) construía el manifiesto
`id:{$dataId};request-id:...;ts:...;` con ese valor. Con `MP_WEBHOOK_SECRET`
puesto, la firma **nunca** habría coincidido y el webhook habría rechazado el
100% de las notificaciones **en silencio** — clientes pagando sin que se les
active el plan. Corregido: se lee `data_id` primero.

**Decisión: NO se configura `MP_WEBHOOK_SECRET`.** MercadoPago sí firma (llega
la cabecera `X-Signature: ts=...,v1=...`), pero la firma aporta poco aquí: el
código ya re-consulta cada ID contra la API de MP con el access token, así que
una notificación falsa no logra nada aunque entre. Activar la validación
cambiaría esa seguridad real por una marginal, metiendo un modo de falla
silencioso justo donde corre el dinero. Si algún día se activa: usar la clave
del **modo productivo** (el simulador de pruebas fallará la firma, y eso es
normal), y revisar que los IDs alfanuméricos de `subscription_preapproval` vayan
en minúsculas, como pide la documentación de MP.

### 6e. Monitoreo externo ✅ HECHO (27 jul)
**UptimeRobot** (gratis), monitor tipo **Keyword** sobre
`https://cotiza.cloud/login`, palabra `Iniciar sesión`, alerta si **no** existe,
cada 5 min.

Tipo `Keyword` y no HTTP(s) simple **a propósito**: si PHP truena, nginx puede
seguir respondiendo 200 con una página de error y un monitor simple diría que
todo está bien. La palabra clave comprueba que el formulario esté ahí de verdad.
Se vigila `/login` y no la raíz porque `/` muestra la landing y redirige distinto
según haya sesión.

Tiene que ser **externo**: un agente dentro del VPS no puede avisar que el VPS
murió. Pendiente opcional: un segundo monitor sobre un dominio custom de cliente
(vigila certificado y enrutamiento, que se rompen solos al caducar) y un cron de
salud interno (disco, RAM, MariaDB, certificados por vencer) que avise **antes**
de la caída, usando el relay de Brevo ya configurado.

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

## PENDIENTE — ancho del título en el Radar (problema viejo, causa ya identificada)

Síntoma histórico: los títulos del Radar se cortan y **subir el `max-width` no
sirve de nada**. Se subió `.rtit` de 200px a 320px y **no se notó ningún cambio**.

**Causa raíz encontrada (no es `.rtit`):**
- El título **NO** se recorta en PHP — `modules/radar/index.php:377` imprime
  `htmlspecialchars($r['titulo'])` completo. Es puramente CSS.
- `modules/radar/index.php:439`: el `.rtit` vive dentro de un
  `<div style='display:flex'>` con **`style='flex:1;min-width:0'`**.
- Con `flex:1; min-width:0`, el ancho real lo impone el **`<td>`** contenedor.
  El `max-width` es solo un **tope superior**: si la celda ofrece menos de 320px,
  el texto se corta al ancho de la celda y el tope nunca llega a aplicar.
- `.rdrt` es `width:100%; min-width:520px` (línea 599) y **todas las demás
  columnas llevan `white-space:nowrap`** (línea 600) → se quedan con su ancho
  natural y a la columna de título le toca **solo la sobra**.

**Cómo se arregla de verdad (cuando se retome):**
1. Dar ancho explícito a la columna en su `<th>`: `width:38%` (o el % que se
   decida), que es lo mínimo invasivo.
2. O `table-layout:fixed` en `.rdrt` + anchos por columna — más control, pero
   obliga a definir TODAS las columnas.
3. O reducir columnas en pantallas medianas (ocultar PRIOR% o IMPORTE bajo cierto
   breakpoint) para liberar espacio.

Estado: `.rtit` quedó en **320px** (inofensivo, es solo el tope). El cambio real
está pendiente de decidir cuál de las 3 opciones se toma.

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
- **El resolvedor DNS de tu propia red puede seguir en el server viejo días
  después del corte** — y eso se disfraza de bug en el código. Caso real (27 jul):
  el Radar mostraba HTML viejo en Firefox y Safari de la iMac, mientras que otra
  computadora veía el correcto. Se revisó OPcache, caché de nginx, `root` de los
  vhosts, copias huérfanas del archivo y `md5sum` del desplegado — **todo estaba
  bien**. La causa: `dig +short cotiza.cloud` desde esa Mac devolvía
  `107.161.23.124` (viejo) porque el DNS del router/proveedor tenía cacheado el
  registro con el TTL largo de cPanel (14400 s), mientras `@1.1.1.1` y `@8.8.8.8`
  ya devolvían `212.28.186.247`. Ojo: los **subdominios sí resolvían bien** — solo
  el apex estaba pegado, así que `hermosillo.cotiza.cloud` se veía correcto y
  `cotiza.cloud` no. `dscacheutil -flushcache` NO lo arregla (la caché no es de
  macOS sino de la red); se resuelve poniendo `1.1.1.1` / `8.8.8.8` en
  Ajustes → Red → Detalles → DNS.
  **Diagnóstico de 10 segundos antes de tocar código, siempre:**
  ```bash
  dig +short cotiza.cloud            # tu resolvedor
  dig +short cotiza.cloud @1.1.1.1   # verdad autoritativa
  curl -sI https://cotiza.cloud/login | grep -i "^server:"
  ```
  `server: LiteSpeed` = servidor viejo · `server: nginx/1.24.0 (Ubuntu)` = Contabo.
  **Consecuencia de negocio:** quien caiga en el viejo escribe en la BD vieja
  (cotizaciones, ventas, abonos, taps del Radar) y ese trabajo NO llega a Contabo.
  Es la segunda razón —además del rollback— para dejar el server viejo encendido
  varios días, y obliga a revisar escrituras posteriores al corte antes de darlo
  de baja.
