# Auditoría post-migración — Escudo, Radar y cookies

**Fecha:** 27 julio 2026 · **Contexto:** primera auditoría después de mover el
sistema de cPanel/LiteSpeed (hosting compartido) a VPS Contabo con nginx 1.24 +
PHP-FPM 8.3 + MariaDB 10.11, sin panel de control.

**Método:** comprobación en vivo contra producción (peticiones HTTP reales) +
lectura del código + agentes de auditoría con verificación adversarial.

**Regla aplicada:** nada se reporta como hecho si no se verificó. Lo que no se
pudo comprobar desde fuera aparece marcado como *pendiente de comprobar* con el
comando exacto para hacerlo en el servidor.

**Nota sobre las pruebas:** todas las peticiones de esta auditoría salieron con
`User-Agent` de `curl`, que está en la lista de `es_bot()`
(`core/Helpers.php:404`). Entraron por `capa_3_bot` → `skip_tracking`, así que
**no contaron visitas ni crearon sesiones**. Solo dejaron registro en
`escudo_log`, que es justo para lo que existe esa tabla.

---

## LO QUE ESTÁ BIEN (verificado, no asumido)

Vale la pena registrarlo porque varias cosas **mejoraron** con la migración:

| Comprobación | Resultado |
|---|---|
| `.php` accesibles directo por URL | **404** en todos — nginx no los sirve, todo pasa por `index.php`. Con el `.htaccess` viejo sí se servían (`RewriteCond -f`). **Mejoró.** |
| `/data/`, `/.git/`, `/logs/`, `/migrations/`, `/.env` | 404 — no expuestos |
| Path traversal (`/assets/../config.php` y variantes codificadas) | 404 — bloqueado |
| Assets legítimos (`/assets/js/*.js`) | 200 con `content-type` correcto |
| Cabeceras de seguridad | `nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, **HSTS** presentes |
| Sintaxis PHP de todo el repo | 0 errores (`php -l` sobre todos los archivos) |
| `mail()` de PHP | ya no se usa en ningún lado (solo comentarios) |
| Rutas del hosting viejo hardcodeadas | solo en comentarios y en `.cpanel.yml` (muerto) |
| Flag `Secure` de cookies | PHP usa `!DEBUG`, **no** depende de `$_SERVER['HTTPS']` → la migración no lo afecta. El JS usa `location.protocol` en los 3 puntos (`cotizacion.php:1846`, `login.php:299`, `layout.php:721`) |
| Endpoint del bridge en dominio custom | responde `400 missing token` — la ruta existe y valida |

---

## HALLAZGOS CONFIRMADOS

### 1 · CRÍTICO — Los dominios custom sirven cotizaciones por HTTP sin cifrar
**Causado por la migración.** Configuración de nginx.

Comprobado en vivo:
```
http://hermosillo.cotiza.cloud/c/<slug>      → 301 a HTTPS        ✅
http://hermosillo.ontimecocinas.com/c/<slug> → 200 en HTTP plano  ❌
```

El subdominio propio sí redirige; el dominio custom **sirve la cotización
completa sin cifrar**.

**Por qué importa más de lo que parece — rompe el Escudo:**
`public/cotizacion.php:189` pone la cookie `cz_vid` con `'secure' => true`. Una
cookie `Secure` **no se puede escribir sobre HTTP**: el navegador la descarta.
Y `cza_session`, también `Secure`, **no se envía** en peticiones HTTP. Es decir,
sobre HTTP en un dominio custom:

- Capa 0 (`cza_session`) → ciega
- Capa 1 (`cz_vid`) → ciega, y además cada visita parece un visitante **nuevo**
- HSTS no se manda (solo viaja por HTTPS), así que el navegador no auto-corrige

Traducción al negocio: un asesor que abra su propia cotización por `http://`
cuenta como cliente e infla el Radar — exactamente la fuga que llevas meses
cerrando. Y los datos del cliente (precios, nombre, dirección) viajan en claro.

**Arreglo:** en el `server` de puerto 80 de los dominios custom, redirigir todo
a HTTPS, igual que ya hace el de `*.cotiza.cloud`.

---

### 2 · ALTO — `obregon.ontimecocinas.com` da 503 en HTTP
**Causado por la migración.** Configuración de nginx.

```
http://obregon.ontimecocinas.com/   → 503  (repetido, no es transitorio)
https://obregon.ontimecocinas.com/  → 302  (funciona)
```

Los tres dominios custom se comportan **distinto** en el puerto 80: hermosillo
sirve 200, nogales redirige 302, obregón devuelve 503. La configuración del
puerto 80 quedó incompleta e inconsistente en el corte.

**Impacto:** un cliente de Obregón que abra el enlace sin `https://` ve una
página de error del servidor. Enlace muerto = prospecto perdido, y el asesor
nunca se entera.

**Arreglo:** el mismo del hallazgo 1 — un bloque de puerto 80 uniforme para los
tres dominios que redirija 301 a HTTPS.

---

### 3 · MEDIO — Las peticiones `HEAD` devuelven 404 en todo el sitio
**Preexistente**, no lo causó la migración. Pero ahora molesta más.

```
GET /login → 200   |   HEAD /login → 404
GET /landing → 200 |   HEAD /landing → 404
(igual en /registro, /privacidad, /terminos)
```

Causa exacta, `core/Router.php:35-38`:
```php
$method = $_SERVER['REQUEST_METHOD'];
foreach (self::$routes as [$rMethod, $pattern, $handler]) {
    if ($rMethod !== 'ANY' && $rMethod !== $method) continue;
```
Las rutas se registran como `'GET'`, así que un `HEAD` nunca coincide y cae en
`not_found()`.

**Por qué importa ahora:** saliste de cPanel, que traía monitoreo. En un VPS
propio vas a querer un monitor de disponibilidad (UptimeRobot y similares usan
`HEAD` por defecto): reportaría el sitio caído estando bien. Algunos
previsualizadores de enlaces también hacen `HEAD` primero.

**Arreglo:** una línea en `dispatch()` — tratar `HEAD` como `GET`. PHP y nginx
descartan el cuerpo automáticamente.

---

### 4 · MEDIO — La cookie de sesión de PHP no funciona en dominios custom
**Preexistente.** Es una trampa a futuro más que un problema hoy.

`core/Auth.php:41` fija el dominio de la cookie de sesión:
```php
'domain' => '.' . BASE_DOMAIN,
```
Comprobado en vivo: desde `hermosillo.ontimecocinas.com` el servidor manda
`set-cookie: cza_php=...; domain=.cotiza.cloud`. El navegador **rechaza** esa
cookie por no corresponder al host. Resultado: en dominios custom no hay
`$_SESSION` — se crea una nueva en cada petición.

Hoy el impacto es bajo porque `csrf_token()` (`core/Helpers.php:216`) usa
`$_SESSION`, y los 5 endpoints con CSRF (`soporte`, `mesa_agendar`,
`onboarding_completar`, `radar_feedback`, `mesa_estado`) son todos internos, del
panel, que vive en `cotiza.cloud`. Los dominios custom solo sirven slugs
públicos, que hoy no usan CSRF.

**Dónde muerde:** en la lista de pendientes de seguridad está *"agregar CSRF a
`quote_action.php`"*. El día que se haga, **aceptar y rechazar cotizaciones
dejará de funcionar en los dominios custom** y el síntoma será desconcertante
("el botón no hace nada, pero solo en OnTime"). Hay que arreglar el dominio de
la cookie **antes** de tocar ese CSRF.

**Arreglo:** que `'domain'` se calcule según el host — `.cotiza.cloud` cuando el
host termina en el dominio base, y omitirlo (host-only) en dominios custom.

---

### 5 · MEDIO — El slug de inmuebles no manda `device_sig` al tracking
**Preexistente.**

`public/cotizacion.php:1954` manda al beacon `device_sig`, `session_id` y
`page_id`. `public/cotizacion_inmueble.php:610` manda solo:
```js
var d={cotizacion_id:COT,empresa_id:EID,tipo:tipo,visible_ms:visibleAccum,max_scroll:maxScroll,visitor_id:VID};
```
Sin `device_sig`, sin `session_id`, sin `page_id`. Y esa plantilla tampoco
persiste la cookie `cz_dsig` (`cotizacion.php` lo hace en la línea 1904;
`cotizacion_inmueble.php` no tiene ni un `document.cookie`).

**Impacto:** en cotizaciones de inmuebles, `api/track.php:85` nunca aprende el
`device_sig` del asesor en `user_sessions`, y `quote_events.device_sig` queda
NULL. El descarte por dispositivo no opera en ese giro.

**Antes de arreglarlo, medir si aplica.** Cuántas empresas usan el giro:
```sql
SELECT giro, COUNT(*) FROM empresas GROUP BY giro;
```
Si no hay ninguna en `inmuebles`, es deuda técnica sin urgencia.

---

## PENDIENTES DE COMPROBAR EN EL SERVIDOR

No se pueden verificar desde fuera. Los certificados que ve una auditoría
externa desde este entorno son los del proxy de salida, **no** los reales — por
eso no se reporta su vencimiento aquí.

```bash
# 1) Vencimiento y renovación de los Let's Encrypt (wildcard + 3 dominios custom)
certbot certificates
systemctl list-timers | grep -i certbot

# 2) Por qué obregón da 503 en el puerto 80, y cómo quedaron los server blocks
grep -n "listen 80" -A8 /etc/nginx/sites-available/cotizacloud
tail -30 /var/log/nginx/error.log

# 3) ¿nginx pasa headers falsificables a PHP? (afecta ip_real)
grep -rn "fastcgi_param\|real_ip" /etc/nginx/

# 4) Permisos de escritura de uploads y logs
ls -ld /var/www/cotizacloud/uploads /var/www/cotizacloud/assets/uploads /var/www/cotizacloud/logs

# 5) Zona horaria de PHP y de MySQL (deben coincidir o se desfasan las ventanas del Radar)
php -r 'echo date_default_timezone_get(), " ", date("P"), "\n";'
mysql -u root -e "SELECT @@global.time_zone, @@session.time_zone, NOW();"
```

---

## ORDEN SUGERIDO

1. **Hallazgos 1 y 2** — mismo arreglo, un bloque de nginx. Es lo único que hoy
   pierde clientes y contamina el Radar.
2. **Hallazgo 3** — una línea, y te habilita el monitoreo del VPS.
3. **Hallazgo 4** — antes de tocar el CSRF de `quote_action.php`.
4. **Hallazgo 5** — solo si hay empresas con giro `inmuebles`.

Ningún cambio de código se aplicó: esto es auditoría de solo lectura, para
acordar contigo qué se toca antes de tocarlo.
