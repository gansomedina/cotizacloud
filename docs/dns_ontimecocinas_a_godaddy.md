# Sacar `ontimecocinas.com` de los nameservers `.cyou`

**Por qué se hace.** Los servidores de nombres del dominio son
`ns1/ns2.limitless.cyou`. El TLD **`.cyou` está bloqueado entero** en la lista
pública de TLDs más abusados de HaGeZi —la que alimentan Spamhaus, Cloudflare
Radar y Netcraft, y que usan NextDNS, AdGuard, Pi-hole y routers con filtro—:

```
||*.cyou^$denyallow=hammertime.cyou|prometko.cyou
```

Como `.com` **no entrega glue** para esos nameservers, cualquier resolvedor con
esa lista tiene que resolver un nombre `.cyou` para llegar a la zona, ese paso
muere, y **`ontimecocinas.com` entero deja de resolver** — aunque sea un `.com`
inocente y aunque al usuario le funcione el resto de internet.

Le pasó a una clienta de OnTime Hermosillo el 8-sep-2026 (cotización 4816):
navegaba YouTube y MercadoLibre sin problema, y Chrome le decía *"No se encontró
hermosillo.ontimecocinas.com's DNS address"*.

**Y le pega a TODAS las ligas del producto.** `dominio_publico()`
(`core/Helpers.php:1304`) devuelve el dominio custom siempre que la empresa lo
tenga, así que el botón Copiar, el de WhatsApp, el correo y el "Ver" del editor
generan **solo** `<sucursal>.ontimecocinas.com`. Además el ápice redirige ahí
(`Router.php:404-437`, verificado en vivo: `cotiza.cloud/c/<slug>` → 302 →
`hermosillo.ontimecocinas.com/c/<slug>`). Un solo bloqueo tumba todos los caminos.

**Segundo motivo, independiente:** los dos NS viven en un único AS (53667,
Limitless). Cuando su red parpadea, caen juntos y las tres sucursales con ellos.

---

# ▶ OPCIÓN A — Pedirle a Limitless nameservers que no sean `.cyou`

**Empezar por aquí.** Muchos hosts tienen varios juegos de nameservers. Si
Limitless tiene unos en un dominio normal, esto se arregla cambiando **dos
campos en GoDaddy**, sin mover un solo registro, sin tocar correo, sin tocar
certificados y sin riesgo.

El correo a Limitless:

> *"¿Tienen nameservers en un dominio que no sea `.cyou`? El TLD `.cyou` está en
> las listas de bloqueo de TLDs abusados (Spamhaus / HaGeZi) y hay clientes
> nuestros a los que no les resuelve el dominio por eso. Necesitamos apuntar a
> nameservers en `.com` o `.net` — el hosting se queda con ustedes."*

Si dicen que sí → cambiar los NS en GoDaddy y **terminó**. La Opción B ya no
hace falta.

Si dicen que no → Opción B.

---

# ▶ OPCIÓN B — Mover la zona completa al DNS de GoDaddy

GoDaddy ya es el registrador (RDAP: registrado 20-ago-2019, vence 20-ago-2027);
su panel de DNS está dormido porque los NS apuntan a Limitless.

**La página web y el correo NO se mueven**: siguen en Limitless, en
`162.244.93.4`. Esto cambia *quién publica el directorio*, no dónde vive la casa.

## Lo que hay que copiar — 52 registros

> **Por qué esta lista y no la que yo saqué desde afuera:** consultando el DNS
> público encontré 18 registros. La zona real tiene **52**. Preguntar desde
> afuera solo encuentra lo que uno sabe preguntar. Esta tabla sale del export
> de DirectAdmin, que es la buena.

### A — 36 registros, todos a `162.244.93.4`

```
@ (ontimecocinas.com)   app        cen        hmo        nog
ftp        pop        smtp       webdisk    webmail    whm
cpanel     cpcalendars           cpcontacts
cpanel.cen        cpanel.hmo        cpanel.nog
cpcalendars.cen   cpcalendars.hmo   cpcalendars.nog
cpcontacts.cen    cpcontacts.hmo    cpcontacts.nog
webdisk.cen       webdisk.hmo       webdisk.nog
webmail.cen       webmail.hmo       webmail.nog
whm.cen           whm.hmo           whm.nog
www.app           www.cen           www.hmo           www.nog
```

TTL 3600 en todos.

⚠️ Ojo con `app`, `cen`, `hmo` y `nog`: son **cuentas de hosting aparte**
(cada una con su `cpanel.`, `webmail.`, `whm.`, `webdisk.`, `www.`). No
confundir `hmo` con `hermosillo` — son cosas distintas y las dos deben quedar.

### CNAME — 5

| Nombre | Valor | TTL |
|---|---|---|
| `www` | `ontimecocinas.com.` | 3600 |
| `mail` | `ontimecocinas.com.` | 3600 |
| **`hermosillo`** | **`saas.cotiza.cloud.`** | **300** |
| **`obregon`** | **`saas.cotiza.cloud.`** | **300** |
| **`nogales`** | **`saas.cotiza.cloud.`** | **300** |

⚠️ **Nunca apuntar las sucursales a la IP del VPS (`212.28.186.247`).** Eso saca
a Cloudflare del camino y regresa la pérdida de paquetes de Telmex del 2-sep. El
destino correcto es `saas.cotiza.cloud`.

### MX — 1

`ontimecocinas.com.` → `0 ontimecocinas.com.` · TTL 3600

### TXT — 8

| Nombre | Valor |
|---|---|
| `@` | `v=spf1 a mx ip4:162.244.93.4 include:spf.mxyeet.net ~all` |
| `_dmarc` | `v=DMARC1; p=none` |
| `_cpanel-dcv-test-record` | `_cpanel-dcv-test-record=8bKJkrO6wAl5HN5sfB97nbu7CWX6PPOWwZoecfNfp1A26FbqRAKqH43K0n1NEtUW` |
| `_acme-challenge.hermosillo` | `JBS9z2ix5WKaE0qXeCgWajjanGwTwVNh7JWY7dkUKtw` |
| `_acme-challenge.hermosillo` | `tDBTG_uNnBzHyUkVdvyQ4URjX3xi46DIOGO5JFI8I9M` |
| `_acme-challenge.nogales` | `RmftMKOYCzsVlJVf2UJUrDJRSA8O5owudTvkxIFMMQc` |
| `_acme-challenge.nogales` | `qrAEZzWHPg6eHEBgV-ME8QrAPs16asqrOixtpolC1EA` |
| `_acme-challenge.obregon` | `_Nrw4RqWDejyizL99jtNcU2Xb8HtIm9N7dbttm6lwPs` |

**Los `_acme-challenge.<sucursal>` son la validación de los certificados de las
tres sucursales.** Si se pierden, los certificados dejan de renovarse y las tres
sucursales se caen con error de SSL en semanas — el fallo más silencioso de toda
la lista. GoDaddy sí acepta dos TXT con el mismo nombre; hay que capturar los
dos donde hay dos.

⚠️ **Estos tokens ROTAN.** Cuando quien emite el certificado los cambie, hay que
volver a capturarlos en GoDaddy. Es mantenimiento nuevo que hoy no existe. Ver
"Antes de decidir" abajo.

### Lo que NO existe y no hay que inventar

- **No hay DKIM.** El export completo no tiene ni un `_domainkey` — lo confirma
  también un barrido de 21 selectores desde afuera. El correo de OnTime va
  firmado solo con SPF. *(Aparte: eso le pega a su entregabilidad y vale la pena
  decírselo a Limitless, pero es un tema distinto y no bloquea esta migración.)*
- **No hay DNSSEC.** No existe registro DS en `.com` — la sección de DirectAdmin
  está ahí pero apagada. Nada que migrar.
- **No hay CAA.**

## Los pasos

1. **En GoDaddy → DNS, borrar lo que trae de fábrica**: el `A @` de su página de
   estacionamiento, el `CNAME www` a `@parkingpage` y cualquier
   `_domainconnect`. Si se quedan, tumban la página.
2. **Capturar los 52 registros.** Todavía no cambia nada para nadie: el panel
   está dormido hasta el paso 4. Riesgo cero.
3. **Revisar la lista dos veces** contra el export de DirectAdmin. Este es el
   paso que decide si el correo sigue llegando.
4. **Cambiar los nameservers** en GoDaddy a los suyos. Único momento en que algo
   cambia de verdad.
5. **NO borrar nada en Limitless por 48 horas.** El NS en `.com` tiene TTL de
   172,800 s = 2 días; en ese lapso hay resolvedores que siguen preguntándole a
   Limitless. Como las dos zonas dicen lo mismo, nadie lo nota — **siempre que
   la de Limitless siga viva**.
6. **Verificar** a las 2-3 h y otra vez a las 48:
   - `https://ontimecocinas.com` → su página.
   - `https://hermosillo.ontimecocinas.com/c/<slug>` → la cotización, **con
     candado** (si sale error de SSL, faltó un `_acme-challenge`).
   - Mandar un correo **hacia** una cuenta de OnTime y otro **desde** una cuenta
     de OnTime a Gmail; en el segundo abrir *"Mostrar original"* y confirmar
     **SPF y DMARC en PASS**. (DKIM saldrá `none` — así está hoy también.)
   - `https://www.whatsmydns.net/#NS/ontimecocinas.com` → NS de GoDaddy en todo
     el mundo.

## Si algo sale mal

Volver a poner los nameservers de Limitless en GoDaddy. Dos campos, se revierte
igual de rápido, y mientras la zona de Limitless siga intacta (paso 5) la vuelta
atrás es completa.

---

## Antes de decidir A o B — preguntarle esto a Limitless

1. **¿Tienen nameservers que no sean `.cyou`?** → decide A vs B.
2. **¿Su AutoSSL valida por HTTP o por DNS?** El registro
   `_cpanel-dcv-test-record` en la zona es la prueba de que cPanel *comprueba*
   si puede validar por DNS. Si valida por DNS, al mover el DNS a GoDaddy cPanel
   ya no puede escribir esos registros y **el certificado de su página deja de
   renovarse en ~60 días**, sin que nadie lo relacione con esto. Si valida por
   HTTP, no pasa nada.
3. **¿Quién emite los certificados de `hermosillo/obregon/nogales` y puede
   cambiar la validación de TXT a HTTP?** Si se puede, los cinco
   `_acme-challenge` desaparecen y con ellos el mantenimiento que la Opción B
   agrega.

**La Opción A no tiene ninguno de esos tres problemas.** Por eso va primero.

---

## Lo que esto NO resuelve

La segunda captura de la clienta —`hermosillo.cotiza.cloud` en el navegador
embebido de WhatsApp— **no la explica el bloqueo de `.cyou`**: ese nombre no toca
`.cyou` por ningún lado y `.cloud` no está en esa lista. Ver `CLAUDE.md`,
sesión 8-9 sep 2026.
