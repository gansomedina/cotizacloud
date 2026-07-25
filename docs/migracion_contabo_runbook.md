# Runbook de migración a Contabo — guía maestra paso a paso

> **Para el chat de ejecución:** este documento es la secuencia completa. El
> usuario NO sabe configurar servidores. Ir **un paso a la vez**, explicando qué
> hace cada comando y por qué, esperando que el usuario pegue el output antes de
> continuar. Claude guía; el usuario ejecuta en la terminal SSH de su VPS y pega
> resultados. Marcar cada paso como hecho antes del siguiente.
>
> Decisiones ya tomadas (ver CLAUDE.md → "Migración a Contabo"):
> Contabo VPS 6 Performance (NVMe, EE.UU.) · Ubuntu · DirectAdmin ($5/mes,
> importa cPanel) · Cloudflare Full(strict) + wildcard *.cotiza.cloud · Let's
> Encrypt DNS-01 vía acme.sh (dns_cf) · correo por relay (Brevo/SES) · migración
> EN PARALELO (producción sigue viva hasta el corte).

---

## Estrategia global (no perder de vista)
Construir Contabo en paralelo → probar sin tocar DNS (vía `/etc/hosts`) → cortar
solo cuando todo pase → dejar el host viejo como rollback unos días. **Cero
downtime, con vuelta atrás.** Los datos que se muevan entre la copia y el corte
se sincronizan con un **dump+rsync FINAL** justo antes de cambiar el DNS.

---

## FASE 0 — Prerequisitos (tener a la mano antes de arrancar)
- [ ] VPS Contabo 6 Performance contratado. Anotar: **IP pública**, **usuario root**, **contraseña root** (llegan por correo de Contabo).
- [ ] Cliente SSH: en Mac/Linux la Terminal; en Windows, PowerShell o PuTTY.
- [ ] Cuenta **Cloudflare** (gratis).
- [ ] Acceso al **registrador** del dominio (donde se cambian nameservers).
- [ ] Acceso al **hosting actual** (cPanel/DirectAdmin de Limitless): para el dump de BD y bajar archivos. Confirmar si hay **acceso SSH** o solo panel.
- [ ] Licencia **DirectAdmin** (el correo con el license key / método de activación).
- [ ] Datos de `config.php` actual a la mano (DB name/user/pass, VAPID, tokens MP).
- [ ] Cuenta de **relay de correo** (Brevo o Amazon SES) — se puede crear en la fase de correo.

**Regla de oro:** no tocar el DNS de producción hasta la Fase de corte.

---

## FASE 1 — Primer acceso y seguridad básica del VPS
1. Conectar por SSH: `ssh root@IP_DEL_VPS` (pegar contraseña).
2. Actualizar el sistema: `apt update && apt upgrade -y`.
3. Zona horaria y hostname:
   - `timedatectl set-timezone America/Hermosillo` (o la que corresponda).
   - `hostnamectl set-hostname server.cotiza.cloud`.
4. **Seguridad mínima antes de exponer nada:**
   - Crear usuario no-root con sudo (opcional pero recomendado).
   - Firewall: DirectAdmin trae su propio manejo; de momento NO cerrar el 22 (SSH) ni 80/443. Se afina después.
   - Cambiar/asegurar la contraseña root; idealmente configurar **llave SSH** (Claude guía si el usuario quiere).
5. Verificar recursos: `free -h` (RAM), `df -h` (disco), `nproc` (vCPU). Confirmar que es lo contratado.

> ⚠️ DirectAdmin quiere instalarse en un **sistema limpio**. No instalar Apache/MySQL/PHP a mano antes — DirectAdmin los pone.

---

## FASE 2 — Instalar DirectAdmin
1. DirectAdmin se instala con un script + la licencia. Método actual (verificar con el correo de licencia de DA):
   ```
   cd /root
   wget -O setup.sh https://download.directadmin.com/setup.sh
   chmod 755 setup.sh
   ./setup.sh    # pedirá el license key / o auto-detecta por IP si la licencia está ligada
   ```
2. La instalación tarda (compila/descarga). Al final da la **URL del panel** (`https://IP:2222`), usuario `admin` y contraseña.
3. Entrar al panel, cambiar la contraseña de `admin`.
4. En DirectAdmin elegir versiones: **PHP 8.x** (la que usa CotizaCloud hoy — confirmar cuál) y **MariaDB** (no MySQL Oracle, para compatibilidad).

> Si el instalador falla o pide algo raro, pegar el error completo — se diagnostica.

---

## FASE 3 — DNS a Cloudflare apuntando al hosting VIEJO (de-risk, sin impacto)
Objetivo: sacar el DNS de las manos del host **ya**, sin mover el servidor. Cuando cortemos, solo cambiamos una IP en Cloudflare (rápido) en vez de nameservers.
1. En Cloudflare: **Add site** → `cotiza.cloud` → plan Free.
2. Cloudflare importa los registros existentes. **Revisar UNO POR UNO** contra el DNS actual y completar lo que falte:
   - `A cotiza.cloud → IP del hosting VIEJO` (naranja/proxy).
   - `A *.cotiza.cloud → IP del hosting VIEJO` (naranja/proxy).
   - `A/CNAME` de subdominios especiales.
   - **Correo (GRIS / DNS only):** `A mail.cotiza.cloud`, `MX`, `TXT` de SPF, DKIM, DMARC, `A server.cotiza.cloud`.
   - Registros de **dominios propios** de clientes — esos viven en SUS zonas, no en la nuestra (no se tocan aquí).
3. **Antes de cambiar nameservers:** bajar el **TTL** de los registros clave a 5 min.
4. Cambiar los **nameservers** en el registrador a los que da Cloudflare. Esperar propagación.
5. Verificar que TODO sigue funcionando en el host viejo (web, correo, slugs). SSL/TLS de Cloudflare en **Full** (aún no strict hasta que el origen tenga cert válido; en el host viejo ya tiene el suyo — dejar **Full** por ahora).

> Esta fase es opcional pero MUY recomendada: te blinda del fallo que ya tuviste (DNS del host) y hace el corte final trivial.

---

## FASE 4 — Preparar el dominio y subir el código en Contabo
1. En DirectAdmin crear la cuenta/dominio `cotiza.cloud` (o importar la cuenta cPanel si el host viejo da backup cPanel — DA lo convierte).
2. Ubicar el docroot en Contabo (DA suele usar `/home/USUARIO/domains/cotiza.cloud/public_html`).
3. Subir el **código** de CotizaCloud:
   - Opción A (si el host viejo tiene git/deploy): clonar desde el repo git.
   - Opción B: bajar `public_html/` del host viejo (zip por el panel o `scp`) y subirlo.
4. Ajustar permisos/ownership al usuario del dominio.

---

## FASE 5 — Migrar la base de datos (primera copia)
1. **Exportar del host viejo:**
   - Con SSH: `mysqldump -u USER -p NOMBRE_BD > cotizacloud.sql`
   - Sin SSH: exportar por **phpMyAdmin** (o "Backup" del cPanel).
2. Subir el `.sql` a Contabo (`scp` o el File Manager de DA).
3. En DirectAdmin crear la BD + usuario (mismos nombre/usuario que `config.php` para no cambiar credenciales, o nuevos y actualizar config).
4. **Importar:** `mysql -u USER -p NOMBRE_BD < cotizacloud.sql`
5. Verificar: entrar a la BD y contar tablas/filas clave (`empresas`, `cotizaciones`).

> Esta es la copia inicial. Habrá un **dump FINAL** en el corte para traer lo nuevo.

---

## FASE 6 — Migrar archivos de estado y secretos
1. **Uploads:** copiar `assets/uploads/{empresa_id}/` completo del host viejo a Contabo (imágenes, PDFs, fotos de propiedades). Con `rsync` si hay SSH, o zip por panel.
2. **`/home/key/`** del host viejo → equivalente en Contabo: el **`.p8` de APNs** y el **`vapid_private.pem`**. Ubicarlos y apuntar las rutas en `config.php`.
3. **`config.php`:** copiar y ajustar: DB creds, rutas VAPID/APNs, y **ROTAR los tokens de MercadoPago** (se compartieron en chat — generar nuevos en el panel de MP).
4. Revisar `.htaccess` / reglas del router y que `index.php` sirva bien.

---

## FASE 7 — SSL con Let's Encrypt wildcard (acme.sh + Cloudflare dns_cf)
1. En Cloudflare crear un **API Token restringido**: solo zona `cotiza.cloud`, permiso **Edit DNS**, sin Global Key.
2. DirectAdmin ya trae **acme.sh**. Configurar el plugin de Cloudflare (paso CLI único):
   ```
   export CF_Token="EL_TOKEN"
   export CF_Account_ID="TU_ACCOUNT_ID"   # si aplica en tu versión
   ```
3. Emitir el wildcard:
   ```
   /root/.acme.sh/acme.sh --issue --dns dns_cf -d cotiza.cloud -d '*.cotiza.cloud'
   ```
   (acme.sh crea/borra el TXT `_acme-challenge.cotiza.cloud` solo.)
4. Instalar el cert en DirectAdmin para el dominio y activar renovación por el cron de DA.
5. **Dominios propios de clientes:** cada uno lleva su cert por **HTTP-01** (no wildcard), que DA/acme.sh emite automático cuando el dominio apunta al VPS. (Se hace en el corte, cuando ya apunten a Contabo.)
6. Dejar Cloudflare SSL/TLS en **Full (strict)** una vez el origen tenga cert válido.

---

## FASE 8 — Correo por relay (NO desde el VPS)
1. Los transaccionales de CotizaCloud (cotización aceptada, abonos, avisos superadmin) por **relay**: crear cuenta Brevo o Amazon SES.
2. Configurar `Mailer.php`/SMTP para usar el relay (host/usuario/API key del relay).
3. Autenticar el dominio en el relay (SPF/DKIM que pide Brevo/SES) — agregar esos TXT en Cloudflare (GRIS/DNS only).
4. Probar envío real y revisar que **no caiga en spam**.

---

## FASE 9 — Cron y tuning de MySQL
1. **Cron diario 3am** (procesar suscripciones):
   ```
   0 3 * * * /usr/bin/php /home/USUARIO/domains/cotiza.cloud/public_html/cron/procesar_suscripciones.php
   ```
   (crear en la UI de cron de DirectAdmin o `crontab -e`).
2. **Tuning MySQL** (clave para que se sienta rápido): editar `my.cnf` →
   `innodb_buffer_pool_size` a ~50-60% de la RAM. Reiniciar MariaDB. Verificar.

---

## FASE 10 — Probar Contabo SIN tocar el DNS real (vía /etc/hosts)
1. En la **máquina del usuario** editar `/etc/hosts` (Mac/Linux) o `C:\Windows\System32\drivers\etc\hosts` (Windows):
   ```
   IP_DEL_VPS   cotiza.cloud
   IP_DEL_VPS   www.cotiza.cloud
   IP_DEL_VPS   nombreempresa.cotiza.cloud   # un subdominio de prueba real
   ```
2. Con eso, el navegador del usuario ve Contabo mientras el mundo sigue en el host viejo. **Checklist de prueba:**
   - [ ] Login (asesor y superadmin).
   - [ ] Dashboard, Radar, mesa, termómetro cargan con datos reales.
   - [ ] Slugs públicos `/c/`, `/v/`, `/r/`.
   - [ ] Un **dominio propio** de cliente (ontimecocinas) — probar por hosts también.
   - [ ] MercadoPago (crear preapproval de prueba).
   - [ ] Push notifications.
   - [ ] Correo transaccional (aceptar una cotización de prueba → llega el mail por relay).
   - [ ] SSL sin errores.
3. Corregir lo que falle **antes** del corte. Quitar las líneas de `/etc/hosts` al terminar de probar.

---

## FASE 11 — EL CORTE (cutover)
Hacer en ventana de bajo tráfico. Secuencia exacta:
1. Confirmar TTL bajo (5 min) desde la Fase 3.
2. **Dump FINAL** de la BD del host viejo → importar en Contabo (trae las cotizaciones/pagos/visitas de las últimas horas). Idealmente poner el sistema viejo en modo lectura o avisar ventana corta.
3. **rsync FINAL** de `assets/uploads/` (archivos nuevos desde la primera copia).
4. En **Cloudflare cambiar la IP** de `cotiza.cloud` y `*.cotiza.cloud` a la **IP de Contabo**. (Como el DNS ya está en Cloudflare desde la Fase 3, esto propaga en minutos — no se tocan nameservers.)
5. Poner Cloudflare SSL/TLS en **Full (strict)**.
6. Emitir certs **HTTP-01** de los dominios propios ahora que apuntan a Contabo (que los clientes cambien su A record / o se hace cuando corresponda).
7. Verificar en vivo: web, login, slugs, correo, MP, push.

---

## FASE 12 — Post-corte
- [ ] Dejar el **host viejo prendido 3-7 días** como rollback (no borrar nada).
- [ ] **Reactivar el webhook de MercadoPago con validación HMAC** (ya sin Imunify360 bloqueando). Actualizar la URL del webhook en el panel de MP si cambió.
- [ ] Monitorear logs (`error.log`), rendimiento del dashboard/Radar, entrega de correo.
- [ ] Confirmar el cron 3am corrió (revisar salida al día siguiente).
- [ ] Actualizar `CLAUDE.md`: nuevas rutas, que ya NO es cPanel, que el auto-deploy cambió (definir cómo se despliega ahora — git pull manual o hook de DA).

## ROLLBACK (si algo sale mal en el corte)
- En Cloudflare, **regresar la IP** de `cotiza.cloud` y `*.cotiza.cloud` a la del host viejo. Propaga en minutos (TTL bajo). El sistema vuelve al host viejo mientras se diagnostica.

---

## Riesgos/gotchas a recordar (de CLAUDE.md)
- **Drift de BD/uploads:** por eso el dump+rsync FINAL en el corte.
- **Enrutamiento por host:** `Auth.php` detecta empresa por subdominio y `dominio_propio` — por eso se prueba con `/etc/hosts` al dominio real, no a un hostname temporal.
- **Correo desde IP nueva = spam:** siempre relay para lo transaccional.
- **Tuning MySQL:** un Performance mal configurado rinde peor que un Core tuneado.
- **DirectAdmin en sistema limpio:** no instalar stack a mano antes.
- Comandos/versiones exactas de DirectAdmin y acme.sh: **verificar en la versión instalada** (evolucionan).
