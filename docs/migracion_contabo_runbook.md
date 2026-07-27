# Migración a Contabo — Runbook / Checkpoint

**Servidor destino:** Contabo VPS · IP `212.28.186.247` · Ubuntu 24.04.4 LTS ·
11 GB RAM · 6 vCPU · 290 GB disco.
**Origen:** hosting cPanel (LiteSpeed + Imunify360) — sigue siendo producción
hasta el cutover de DNS.
**Branch de trabajo:** `claude/migracion-contabo-lezerv`.
**Stack elegido:** Nginx + PHP-FPM 8.3 + MariaDB 10.11 + Certbot. **Sin panel.**
phpMyAdmin protegido va al final.

> Motivo de la migración: salir de Imunify360 (bloqueaba los webhooks de
> MercadoPago) y tener root para escalar. En Contabo **no se usa `.cpanel.yml`**
> — el deploy es `/usr/local/bin/deploy-cotizacloud.sh`.

---

## Estado — QUÉ YA QUEDÓ (verificado en el servidor)

| Fase | Qué | Estado |
|---|---|---|
| 1 | Base: swap 4 GB, `ufw` (22/80/443), `fail2ban`, timezone Hermosillo | ✅ |
| 2 | Stack: nginx 1.24, PHP 8.3.6 (+openssl,curl,pdo_mysql,mbstring,gd,xml,zip,intl,bcmath), MariaDB 10.11, certbot | ✅ |
| 3 | Deploy key (read-only) en GitHub, clone de `main` a `/var/www/cotizacloud-src` | ✅ |
| 3.4 | `deploy-cotizacloud.sh` (reemplaza `.cpanel.yml`) + primera publicación a `/var/www/cotizacloud` | ✅ |
| 4 | BD `cotizacloud` + usuario, `config.php` escrito (secretos reales, rutas de llaves Contabo) | ✅ |
| 5 | Import del dump fresco `migrations/cotizacl_cotizacloud-3.sql` (50 tablas) | ✅ |
| 6 | Nginx vhost (traducción del `.htaccess`, anti-leak) | ✅ |

**Datos importados (producción viva):** 9 empresas, 17 usuarios, 2347
cotizaciones, 257 ventas.

**Pruebas HTTP locales (Host header, sin DNS):**
`/`→200 · `/login`→200 · `/landing`→200 · `/registro`→200 · `/dashboard`→302 ·
`apple-review.cotiza.cloud/`→302 (detección de empresa OK).

---

## Rutas y archivos clave en el servidor

| Ruta | Qué |
|---|---|
| `/var/www/cotizacloud-src` | Clone completo de `main` (privado, NO servido por web) |
| `/var/www/cotizacloud` | **Webroot** (docroot de nginx). Copia selectiva del src |
| `/var/www/cotizacloud/config.php` | Config real (640, `www-data`). NO está en git |
| `/var/www/cotizacloud/logs/` | `error.log` de la app |
| `/var/www/cotizacloud-keys/` | **Llaves push** (aún vacío — pendiente subir, ver abajo) |
| `/usr/local/bin/deploy-cotizacloud.sh` | Deploy: `git reset --hard origin/main` + rsync selectivo + permisos |
| `/root/cotizacloud-credenciales.txt` | DB_PASS + APP_SECRET generados (chmod 600) |
| `/etc/nginx/sites-available/cotizacloud` | vhost |
| `/root/.ssh/id_ed25519(.pub)` | Deploy key (pub registrada en GitHub como "Contabo VPS") |

**config.php — cambios vs cPanel:** DB → `cotizacloud`/`cotizacloud`/pass nuevo;
`APP_SECRET` nuevo fuerte (antes era el default débil → todos re-login 1 vez, ok);
`APNS_KEY_PATH` y `VAPID_PRIVATE_PEM` → `/var/www/cotizacloud-keys/`. SMTP, MP,
APNs IDs, SUPERADMIN_EMAIL = iguales. `ENV` sigue en `development` (igual que el
viejo) — **endurecer a `production` en el cutover**.

**Deploy futuro:** `git push` a `main` → en el server correr
`/usr/local/bin/deploy-cotizacloud.sh`. (Opcional futuro: webhook/cron que lo dispare.)

---

## QUÉ FALTA — pasos restantes

### 7. Subir llaves de push a `/var/www/cotizacloud-keys/`
- `vapid_private.pem` → **está en el repo** (`/var/www/cotizacloud-src/vapid_private.pem`),
  solo copiarlo: `cp /var/www/cotizacloud-src/vapid_private.pem /var/www/cotizacloud-keys/`
- `AuthKey_D2AW3CT2UF.p8` (APNs) → **NO está en el repo** (`.gitignore *.p8`).
  Estaba en el cPanel viejo en `/home/key/AuthKey_D2AW3CT2UF.p8`. **Subirlo por
  `scp` desde la Mac** o sacarlo del cPanel. Sin él, el push iOS no firma.
- Permisos: `chown -R www-data:www-data /var/www/cotizacloud-keys && chmod 600 /var/www/cotizacloud-keys/*`

### 8. Ver el sitio en el navegador ANTES del DNS (prueba con `/etc/hosts` en la Mac)
Agregar temporalmente en la Mac `/etc/hosts`:
```
212.28.186.247 cotiza.cloud www.cotiza.cloud apple-review.cotiza.cloud
```
Abrir `http://cotiza.cloud` (ojo: http, aún sin SSL). Probar login real,
dashboard, subir un logo, ver una cotización. Quitar la línea del hosts al terminar.
> Nota: `BASE_URL` es `https://` — puede forzar redirecciones a https. Para la
> prueba http pura quizá convenga probar por IP o revisar redirects. Evaluar en el momento.

### 9. SSL — Certbot
- **Wildcard `*.cotiza.cloud` + `cotiza.cloud`**: requiere reto **DNS-01** (TXT
  `_acme-challenge`). **Pendiente saber dónde vive el DNS de cotiza.cloud**
  (Cloudflare / registrador / cPanel) → define si es plugin automático o TXT manual.
- **Dominios custom** (`hermosillo/obregon/nogales.ontimecocinas.com`): cert por
  dominio con reto **HTTP-01** (`certbot --nginx -d ...`) DESPUÉS de que su DNS
  apunte al Contabo. O wildcard `*.ontimecocinas.com` si controlan ese DNS.
- Script `nuevo-dominio.sh <dominio>` para automatizar cert de cada empresa nueva (pendiente crear).

### 10. Cutover DNS
- Bajar TTL del DNS con anticipación.
- **Dejar `mail.cotiza.cloud` apuntando al cPanel viejo** para que el correo SMTP
  siga funcionando (config apunta a `mail.cotiza.cloud`).
- Apuntar A record `cotiza.cloud` + wildcard `*.cotiza.cloud` → `212.28.186.247`.
- Apuntar los dominios custom OnTime → `212.28.186.247` (coordinar con el cliente).
- Emitir certs, verificar, monitorear.
- Endurecer `ENV=production` en config.php (crear/confirmar `logs/` escribible).

### 11. Cron
```
0 3 * * * /usr/bin/php /var/www/cotizacloud/cron/procesar_suscripciones.php
```
(Revisar si hay más scripts de limpieza que corrían en cPanel: `cleanup_bot_views.php`, etc.)

### 12. phpMyAdmin protegido (o TablePlus por túnel SSH)
Instalar phpMyAdmin en ruta oculta + restringido por IP, o usar cliente de
escritorio por túnel SSH (cero exposición).

### 13. Post-migración / seguridad
- **Rotar credenciales expuestas en el chat**: token MercadoPago (`MP_ACCESS_TOKEN`)
  y password SMTP.
- Migrar correo a SMTP externo (SendGrid) — plan de crecimiento en CLAUDE.md.
- Llaves SSH: pasar a login por llave y desactivar password (probar en 2a terminal
  antes de desactivar, para no quedar fuera).
- Verificar que `/migrations/*.sql`, `/tools`, `/core`, `/docs` NO se sirven (nginx ya los bloquea → confirmar con curl tras cutover).

---

## Auditoría Escudo/Radar cross-domain (pre-cutover) — resultados

Revisión exhaustiva del código (Escudo, Radar, cookies, bridge) frente a
Cloudflare proxy + server nuevo + APP_SECRET nuevo. **Conclusión: el código
está sano para el proxy; los riesgos son de CONFIGURACIÓN, no de código.**

### CRÍTICO
1. **`ENV=production` es el fix #1** — con `ENV=development` (default si no se
   setea `APP_ENV`), `DEBUG=true` → `display_errors=1`. En `cotizacion.php` la
   cookie `cz_vid` (setcookie ~185/220) y un redirect de sync (~196) ocurren en
   el bloque de tracking ANTES del HTML. Cualquier Notice/Warning previo emite
   output → **"headers already sent"** → `cz_vid` NO se planta → **Capa 1 del
   Escudo rota de forma intermitente** + fuga de errores en el slug del cliente.
   Además `secure=!DEBUG` deja cookies sin Secure. → Poner `APP_ENV=production`
   (o `ENV=production`) y `logs/error.log` escribible.
2. **Front-controller en nginx** (`try_files → index.php`) — ya resuelto en el
   vhost. Sin él, `/api/track`, `/api/safari-bridge`, slugs = 404.

### ALTO
3. **Cloudflare NO debe "Cache Everything"** sobre `cotiza.cloud`/`*.cotiza.cloud`
   — el HTML del slug planta `Set-Cookie: cz_vid` y cuenta la visita. Default de
   CF (no cachea HTML sin extensión) es seguro. Mantener `/assets/*` cacheado.
   Verificar que no exista Page Rule/APO de cache HTML. Rocket Loader OFF.
4. **Dominios custom requieren Let's Encrypt válido** — cookies del Escudo ahí
   son Secure-only hardcoded (`set_vid.php:16`, `safari_bridge.php:66/126`). Sin
   HTTPS válido el navegador rechaza guardarlas → Capa 0 y 1 ciegas. Verificar
   también que `empresas.dominio_custom` quedó correcto tras el import.

### MEDIO
5. `MarketingPixels.php:166` lee `REMOTE_ADDR` directo (IP de Cloudflare a Meta
   CAPI). Lo arregla el `real_ip` de nginx (reescribe REMOTE_ADDR a la IP real
   desde rangos CF) — o cambiar a `ip_real()`.
6. `SMTP_HOST` debe quedar definido (ya está en config.php nuevo → mail.cotiza.cloud).

### VERIFICADO OK (no se rompe)
- `ip_real()` lee CF-Connecting-IP primero; lo usan cotizacion/track/cot_feedback/
  layout/Auth. `radar/index.php:31` igual.
- Host preservado por CF → lógica cookie `.cotiza.cloud` vs host-only intacta.
- **APP_SECRET nuevo NO cierra sesiones** (tokens en `user_sessions`, no HMAC) ni
  rompe CSRF (token en $_SESSION) ni el bridge (tokens efímeros mismo server).
- `Radar::BOT_IP=[]` vacío; CERO IPs/rutas del server viejo hardcodeadas en Escudo/Radar.
- Endpoints de tracking = POST (no cacheables por CF).

### nginx real_ip (anti-spoof + REMOTE_ADDR real)
Configurar `set_real_ip_from <rangos Cloudflare>` + `real_ip_header CF-Connecting-IP`.
Para blindar contra spoof directo al origen en `cotiza.cloud`: restringir ese vhost
a IPs de Cloudflare (los dominios custom pegan directo por diseño, ahí no aplica).

## Notas / gotchas encontrados
- Las desconexiones SSH se resolvieron con `ServerAliveInterval` en la Mac + trabajar dentro de **tmux** (`tmux new -s mig` / `tmux attach -t mig`).
- El dump se importó con `SET FOREIGN_KEY_CHECKS=0` (47 FKs, el dump no las desactiva).
- El dump NO trae `CREATE DATABASE`/`USE`/DEFINER/vistas → import limpio.
- Pegar bloques grandes por SSH a veces se enreda; preferir bloques compactos.
