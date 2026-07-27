# Auditoría post-migración — Escudo, Radar y cookies

**Fecha:** 27 julio 2026
**Contexto:** primera auditoría después de mover el sistema de cPanel/LiteSpeed
(hosting compartido) a VPS Contabo — Ubuntu 24.04, nginx 1.24, PHP-FPM 8.3,
MariaDB 10.11, sin panel de control.

**Método:** peticiones HTTP reales contra producción + lectura de código + 20
agentes de auditoría en 5 dimensiones, cada hallazgo sometido a un verificador
adversarial cuyo trabajo era **refutarlo**. De 15 hallazgos, 10 sobrevivieron y
**5 fueron descartados** (van documentados al final para no volver a gastarles
tiempo).

**Regla aplicada:** nada se afirma sin verificarlo. Lo que no se pudo comprobar
desde fuera va marcado como *pendiente* con el comando exacto.

**Las pruebas no ensuciaron datos:** todas las peticiones salieron con
`User-Agent` de `curl`, que está en `es_bot()` (`core/Helpers.php:404`).
Entraron por `capa_3_bot` → `skip_tracking`: no contaron visitas ni crearon
sesiones. Solo quedaron en `escudo_log`.

---

## CONCLUSIÓN PRINCIPAL

**La migración no rompió el Escudo ni el Radar.** Los 10 hallazgos de código son
**preexistentes** — ya estaban en producción en el hosting viejo. Los únicos dos
problemas atribuibles al cambio de servidor son de **configuración de nginx**, no
de código, y ambos se arreglan en el mismo bloque.

Dicho eso, la auditoría encontró **dos cosas graves que no sabíamos** y que no
tienen que ver con la migración: cualquiera puede escribir en el Radar de
cualquier empresa, y el botón "Activar Escudo" entrega una sesión válida durante
24 horas.

Varias cosas además **mejoraron** con nginx (tabla al final).

---

# HALLAZGOS, POR IMPACTO AL NEGOCIO

## 1 · Cualquiera puede contaminar el Radar de cualquier empresa
`api/track.php:42` · **ALTO** · preexistente · *verificado personalmente*

`api/track.php` no ata la cotización al host que hace la petición. El contraste
con el resto del código es la prueba:

```php
api/quote_action.php:29   WHERE id = ? AND empresa_id = ?    ← sí valida el tenant
api/track.php:42          WHERE id = ?                        ← no valida
```

Y lo que ese endpoint escribe:
```php
api/track.php:201  UPDATE cotizaciones SET estado='vista', vista_at=NOW(),
                   ultima_vista_at=NOW() WHERE id=? AND estado='enviada'
api/track.php:286  Radar::recalcular($cot_id, $empresa_id);
```

El endpoint es **público** (lo llama el JS del slug, sin sesión) y los `id` de
cotización son enteros secuenciales. Desde cualquier subdominio público se puede
mandar `POST /api/track` con el `cotizacion_id` de **otra empresa**.

**Impacto:** el Radar es el diferenciador que vas a vender en el anuncio de
Facebook. Alguien iterando ids puede marcar como "vista" y subir de bucket
cotizaciones ajenas, disparar push a asesores de otras empresas, destruir la
señal "sin abrir" y falsear los termómetros. No hace falta ser cliente ni estar
logueado.

**Arreglo:** añadir `AND empresa_id = ?` con `EMPRESA_ID` en la consulta de la
línea 42, exactamente como ya lo hace `quote_action.php`. Es una línea.

---

## 2 · El botón "Activar Escudo" entrega una sesión válida por 24 horas
`core/layout.php:572` + `api/safari_bridge.php:118` · **ALTO** · preexistente · *verificado personalmente*

Tres defectos encadenados que los agentes reportaron por separado y que en
realidad son un solo problema:

**(a) El token vive 24 horas y está impreso en cada carga del dashboard.**
```php
core/layout.php:572     'exp' => time() + 86400,   // 24 horas
modules/auth/login_post.php:146  'exp' => time() + 300,   // 5 minutos
```
El mismo mecanismo en el login dura 5 minutos; el del dashboard, 288 veces más.
Y el enlace se imprime **siempre** en el HTML — el banner solo se oculta con
`style="display:none"`, así que el token está en el código fuente de cada carga
para cualquier usuario logueado.

**(b) El bridge planta la cookie de sesión sin atarla a quien la pide.**
```php
api/safari_bridge.php:118   SELECT token FROM user_sessions
                            WHERE usuario_id = ? AND expires_at > NOW()
                            ORDER BY created_at DESC LIMIT 1
api/safari_bridge.php:126   setcookie(SESSION_NAME, $sess['token'], [...])
```
El HMAC prueba que **nosotros** emitimos el token, no que quien lo presenta sea
el usuario. No hay nonce de un solo uso, ni verificación de navegador. Quien
reproduzca la URL recibe la cookie de sesión de ese usuario en su propio
navegador.

**(c) El parámetro `next` es un redirect abierto.**
```php
api/safari_bridge.php:138   if ($next !== '' && str_starts_with($next, 'https://'))
                                header('Location: ' . $next, true, 302);
```
Acepta cualquier URL `https://`, incluido un dominio ajeno.

**Impacto:** esa URL equivale a la contraseña del usuario durante 24 horas. Viaja
por el historial del navegador, las pestañas sincronizadas de Safari tras el tap
en la app, capturas de pantalla, y los logs de acceso de nginx (va en la query
string). Si el token filtrado es del **superadmin**, la cadena que arma
`layout.php:585-591` recorre los dominios custom de **todas** las empresas
activas.

Un detalle que agrava: `cza_session` es `httponly` para que un XSS no la lea —
pero el `href` del Escudo **sí** es legible por JavaScript. Es un rodeo efectivo
a esa protección.

**Arreglo:** bajar el `exp` a 300 s como el del login; emitir el token bajo
demanda (endpoint al hacer clic) en vez de imprimirlo en cada render; no imprimir
el `<a href>` cuando el banner no aplica; y validar `next` contra una lista de
dominios propios.

---

## 3 · Los dominios custom sirven cotizaciones por HTTP sin cifrar
Configuración de nginx · **ALTO** · **CAUSADO POR LA MIGRACIÓN** · *verificado personalmente*

```
http://hermosillo.cotiza.cloud/c/<slug>      → 301 a HTTPS        ✅
http://hermosillo.ontimecocinas.com/c/<slug> → 200 en HTTP plano  ❌
```

**Por qué rompe el Escudo:** `public/cotizacion.php:189` pone `cz_vid` con
`'secure' => true`. Una cookie `Secure` **no se puede escribir sobre HTTP** — el
navegador la descarta. Y `cza_session`, también `Secure`, **no se envía** en
peticiones HTTP. Sobre HTTP en un dominio custom:

- Capa 0 (`cza_session`) → ciega
- Capa 1 (`cz_vid`) → ciega, y cada visita parece un visitante **nuevo**
- HSTS no viaja (solo va por HTTPS), así que el navegador no auto-corrige

Un asesor que abra su propia cotización por `http://` cuenta como cliente e infla
el Radar: la misma fuga que llevas meses cerrando, reabierta por configuración. Y
los datos del cliente (precios, nombre, dirección) van en claro.

---

## 4 · `obregon.ontimecocinas.com` da 503 en el puerto 80
Configuración de nginx · **ALTO** · **CAUSADO POR LA MIGRACIÓN** · *verificado personalmente*

```
http://obregon.ontimecocinas.com/   → 503  (repetido, no transitorio)
https://obregon.ontimecocinas.com/  → 302  (funciona)
```

Los tres dominios custom se comportan **distinto** en HTTP: hermosillo sirve 200,
nogales redirige 302, obregón devuelve 503. El bloque de puerto 80 quedó
incompleto e inconsistente en el corte.

**Impacto:** un cliente de Obregón que abra el enlace sin `https://` ve un error
del servidor. Enlace muerto, prospecto perdido, y el asesor nunca se entera.

**Arreglo de 3 y 4:** un único `server` de puerto 80 para los tres dominios custom
que redirija 301 a HTTPS, igual al que ya tiene `*.cotiza.cloud`.

---

## 5 · La IP se puede falsificar por cabecera → se anula el límite de intentos de login
`core/Helpers.php:375` · **ALTO** · preexistente

```php
foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key)
```
Con Cloudflare en modo DNS-only nadie limpia esas cabeceras y nginx las pasa tal
cual, así que el visitante decide qué IP ve el sistema.

**Corrección importante al planteamiento inicial** (la verificó el agente leyendo
el código, y tenía razón): **falsificar la IP NO burla el Escudo.** La Capa 2 (IP
contra `radar_ips_internas`) ya no existe — `public/cotizacion.php:283-286` la
documenta como eliminada, y ninguna ruta lee esa tabla para filtrar. Yo mismo
había planteado ese impacto al inicio y era incorrecto.

**Lo que sí rompe, verificado:** `rate_check()` cuenta intentos por `ip_real()`.
Basta variar la cabecera en cada intento para que el contador nunca llegue al
tope. Aplica a **login** (5 intentos), **recuperar** (3/15 min), **registro**
(3/60 min) y al chat de leads del landing.

**Impacto:** con el trial de 30 días abierto y la campaña de Facebook por
arrancar, la superficie sin protección es justo la que va a recibir tráfico. Un
ataque de credenciales sobre cuentas de asesores da acceso a cotizaciones,
precios y cartera de clientes.

**Arreglo:** en nginx, no en PHP. Vaciar `CF-Connecting-IP` y `X-Forwarded-For`
en el `server` block (hoy se pueden vaciar siempre, porque Cloudflare no proxea).
Así `ip_real()` cae a `REMOTE_ADDR` sin tocar una función que usa medio sistema.

---

## 6 · El Radar aplica dos filtros anti-fantasma distintos
`modules/radar/Radar.php:592` · **MEDIO** · preexistente

`score()` recorre las sesiones dos veces. El bucle principal (línea 496) usa el
filtro calibrado el 27 de mayo contra el scroll restaurado del navegador:
descarta si `visible_ms < 200 && scroll < 35`. El segundo bucle (566-596), que
construye `$ips_post_guest` y `$vids_post_guest`, usa el filtro **antiguo**:
descarta solo si `visible > 0 && visible < 2000 && scroll === 0`.

Los dos comentarios dicen *"(paridad con loop principal)"* pero divergen. Caso
típico: `scroll=20, visible=100` (el ghost-restore de Chrome Android) → el bucle
1 lo descarta, el bucle 2 lo cuenta.

**Impacto:** esos contadores alimentan el bucket `multi_persona` (línea 979), la
categoría social de `probable_cierre` (1129) y el impulso de prioridad por
múltiples visitantes (789). Sesiones fantasma que ya se descartaron siguen
sumando "personas distintas" → cotizaciones que aparecen como más calientes de lo
que son. Es justo el problema que se corrigió en mayo, sobreviviendo en el
segundo bucle.

**Arreglo:** extraer el filtro a un closure único y llamarlo desde ambos bucles.

---

## 7 · Dos consultas del motor no filtran `es_interno = 0`
`modules/radar/Radar.php:1892` y `:2181` · **MEDIO** · preexistente

`score()` sí filtra (líneas 407 y 413). Pero `calibrar()` hace
`LEFT JOIN quote_sessions` **sin** `es_interno = 0`, y `engage_avg()` igual.

**Impacto:** `calibrar()` entrena el modelo FIT de la empresa. Cada vista previa
del asesor sobre su propia cotización sube el conteo de sesiones de esa
cotización; si además cerró la venta, el modelo aprende que "muchas sesiones =
cierra" por una razón falsa. El FIT contaminado se propaga a `fit_pct`, que
compuerta los buckets. Es contaminación lenta y silenciosa del modelo.

**Arreglo:** `AND qs.es_interno = 0` dentro del `ON` del LEFT JOIN (no en el
WHERE, para no convertirlo en INNER JOIN) y en el WHERE de `engage_avg()`.
`lista_activas()` tiene el mismo hueco pero no tiene ningún llamador — es código
muerto, se puede borrar.

---

## 8 · El slug de inmuebles manda un payload incompleto y sobrecalienta el Radar
`public/cotizacion_inmueble.php:610` · **MEDIO** · preexistente

El slug de servicios manda `visitor_id, device_sig, session_id, page_id,
max_scroll, open_ms, visible_ms`. El de inmuebles manda cinco campos y omite
`device_sig`, `session_id`, `page_id` y `open_ms`.

La consecuencia no es solo que falte el `device_sig`:
`modules/radar/Radar.php:1276` **sintetiza** un `page_id` con
`md5(sid|ts_unix|tipo)` cuando llega vacío, de modo que **cada evento cae en su
propia "página"**. Entonces `vis_sum` (línea 1298) suma el `visible_ms` acumulado
de cada evento en vez de tomar el máximo de la carga, y `sv_page` (1339) da
verdadero en cuanto un visitante manda dos eventos distintos.

**Impacto:** en empresas con giro `inmuebles` el Radar sobrecalienta de forma
sistemática — propiedades marcadas como cierre inminente sin merecerlo, push
falsos y termómetro inflado.

**Antes de arreglarlo, medir si aplica:**
```sql
SELECT giro, COUNT(*) FROM empresas GROUP BY giro;
```
Si no hay ninguna empresa en `inmuebles`, es deuda técnica sin urgencia.

---

## 9 · Las peticiones `HEAD` devuelven 404 en todo el sitio
`core/Router.php:35` · **MEDIO** · preexistente · *verificado personalmente*

```
GET /login → 200   |   HEAD /login → 404      (igual en /landing, /registro,
                                               /privacidad, /terminos)
```
Causa: `$method = $_SERVER['REQUEST_METHOD']` y las rutas se registran como
`'GET'`, así que un `HEAD` nunca coincide y cae en `not_found()`.

**Por qué importa ahora:** saliste de cPanel, que traía monitoreo. En un VPS vas
a querer un monitor de disponibilidad, y esos usan `HEAD` por defecto: reportaría
el sitio caído estando bien.

**Arreglo:** una línea en `dispatch()` — tratar `HEAD` como `GET`.

---

## 10 · La cookie de sesión de PHP no funciona en dominios custom
`core/Auth.php:41` · **MEDIO** · preexistente · *verificado personalmente*

```php
'domain' => '.' . BASE_DOMAIN,
```
Comprobado en vivo: desde `hermosillo.ontimecocinas.com` el servidor manda
`set-cookie: cza_php=...; domain=.cotiza.cloud`. El navegador la **rechaza**. En
dominios custom no hay `$_SESSION`.

Hoy el impacto es bajo: `csrf_token()` usa `$_SESSION`, y los 5 endpoints con
CSRF son todos del panel, que vive en `cotiza.cloud`.

**Dónde muerde:** en la lista de pendientes de seguridad está *"agregar CSRF a
`quote_action.php`"*. El día que se haga, **aceptar y rechazar cotizaciones
dejará de funcionar en los dominios custom**, con un síntoma desconcertante
("el botón no hace nada, pero solo en OnTime"). Arreglar el dominio de la cookie
**antes** de tocar ese CSRF.

---

## 11 · Una protección documentada del ghost-cleanup no existe
`api/track.php:262` · **BAJO** · preexistente · *verificado personalmente*

El comentario del código dice:
> `visible_ms IS NULL` → cliente con adblocker. **MANTENER**

Pero el esquema real es:
```sql
`visible_ms` int(10) UNSIGNED NOT NULL DEFAULT 0,
```
`NOT NULL`. Así que `visible_ms IS NOT NULL` es **siempre verdadero** y esa
protección nunca se aplicó.

**Aquí los agentes discreparon y vale la pena registrarlo.** Uno lo reportó como
la causa mecánica del reclamo más caro del producto ("el cliente dice que sí la
abrió y el sistema dice sin abrir"). El verificador lo **refutó con código** y
tiene razón: el ghost cleanup **no revierte el estado** (`modules/dashboard/
index.php:391-395` lo documenta explícitamente), y `estado='vista'` lo pone
`cotizacion.php:400-406` del lado del servidor, ajeno a este filtro.

**Lo que sí pasa:** tras el `DELETE`, `track.php:272` recalcula `visitas`,
`vista_at` y `ultima_vista_at` desde las sesiones que quedan. Un cliente cuyo JS
nunca corrió pierde su visita del contador cuando otro visitante con JS activa la
limpieza. La cotización sigue marcada "vista", pero con un contador más bajo del
real. Molesto, no crítico.

---

# LO QUE SE DESCARTÓ (5 hallazgos refutados)

Se documentan para no volver a investigarlos:

| Hallazgo propuesto | Por qué se descartó |
|---|---|
| `?_sv=` deja fijar `cz_vid` 730 días sin firma | Las tres premisas que lo sostenían son falsas al leer `cotizacion.php:181-198` |
| Los uploads de 1-10 MB se rompen por `client_max_body_size` | **Refutado con medición contra producción**: no ocurre |
| El Radar tarda hasta 120 s y PHP-FPM lo mata | La cita es real pero el disparador no se alcanza; se midió la carga y sobra un orden de magnitud |
| La cadena de redirects del login rompe si un dominio custom no tiene certificado | La condición que lo dispara no se cumple; además no es de la migración |
| `visible_ms` causa el síntoma "sin abrir" | El mecanismo es real (hallazgo 11) pero la cadena causal no: el cleanup no revierte el estado |

---

# LO QUE ESTÁ BIEN (verificado, y varias cosas mejoraron)

| Comprobación | Resultado |
|---|---|
| `.php` accesibles directo por URL | **404** en todos — nginx no los sirve. Con el `.htaccess` viejo sí se servían (`RewriteCond -f`). **Mejoró.** |
| `/data/`, `/.git/`, `/logs/`, `/migrations/`, `/.env` | 404 — no expuestos |
| Path traversal (`/assets/../config.php` y variantes codificadas) | 404 — bloqueado |
| Assets legítimos | 200 con `content-type` correcto |
| Cabeceras de seguridad | `nosniff`, `X-Frame-Options`, `Referrer-Policy`, **HSTS** presentes |
| Sintaxis PHP de todo el repo | 0 errores |
| `mail()` de PHP | ya no se usa (solo comentarios) |
| Rutas del hosting viejo hardcodeadas | solo comentarios y `.cpanel.yml` (muerto) |
| Flag `Secure` de cookies | PHP usa `!DEBUG`, **no** depende de `$_SERVER['HTTPS']` → la migración no lo afecta |
| JS que reescribe cookies | los 3 puntos usan `location.protocol` — el bug histórico de perder `Secure` está cerrado |
| `es_interno = 0` en `score()` | sí filtra (líneas 407 y 413) |
| Endpoint del bridge en dominio custom | responde `400 missing token` |

---

# PENDIENTE DE COMPROBAR EN EL SERVIDOR

No se puede desde fuera. **Nota:** los certificados que ve una auditoría externa
desde el entorno de Claude son los del proxy de salida, **no** los reales — por
eso aquí no se reporta su vencimiento.

```bash
# 1) Vencimiento y renovación automática de los Let's Encrypt
certbot certificates
systemctl list-timers | grep -i certbot

# 2) Por qué obregón da 503 en el puerto 80 (hallazgo 4)
grep -n "listen 80" -A8 /etc/nginx/sites-available/cotizacloud
tail -30 /var/log/nginx/error.log

# 3) Cabeceras falsificables que llegan a PHP (hallazgo 5)
grep -rn "fastcgi_param\|real_ip" /etc/nginx/

# 4) Permisos de escritura
ls -ld /var/www/cotizacloud/uploads /var/www/cotizacloud/assets/uploads /var/www/cotizacloud/logs

# 5) Zona horaria PHP vs MySQL (si no coinciden, se desfasan las ventanas del Radar)
php -r 'echo date_default_timezone_get(), " ", date("P"), "\n";'
mysql -u root -e "SELECT @@global.time_zone, @@session.time_zone, NOW();"
```

---

# ORDEN SUGERIDO

| # | Qué | Dónde | Esfuerzo |
|---|---|---|---|
| 1 | Tenant en `track.php` (hallazgo 1) | 1 línea de PHP | minutos |
| 2 | HTTP→HTTPS y 503 de los dominios custom (3 y 4) | 1 bloque de nginx | minutos |
| 3 | Token del Escudo a 300 s + validar `next` (hallazgo 2) | PHP | ~1 hora |
| 4 | Vaciar cabeceras de IP falsificables (hallazgo 5) | nginx | minutos |
| 5 | Unificar el filtro anti-fantasma (hallazgo 6) | PHP | ~1 hora |
| 6 | `es_interno` en `calibrar()` y `engage_avg()` (hallazgo 7) | 2 líneas | minutos |
| 7 | `HEAD` como `GET` (hallazgo 9) | 1 línea | minutos |
| 8 | Dominio de la cookie de sesión (hallazgo 10) | PHP — **antes** del CSRF de `quote_action` | ~30 min |
| 9 | Payload del slug de inmuebles (hallazgo 8) | solo si hay empresas con ese giro | — |
| 10 | Semántica del ghost-cleanup (hallazgo 11) | requiere decidir el diseño | — |

Los puntos 1, 2 y 4 se hacen en una sola sesión corta y cubren lo que hoy tiene
impacto real en clientes y en la integridad del Radar.

**No se aplicó ningún cambio de código.** Esto es auditoría de solo lectura, para
acordar contigo qué se toca antes de tocarlo.
