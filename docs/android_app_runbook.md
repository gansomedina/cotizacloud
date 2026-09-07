# Runbook — App de Android, réplica de la de iOS

> Guía paso a paso para ejecutar. La **Parte A** es lo que ya quedó hecho.
> La **Parte B** es lo que falta, en el orden en que hay que hacerlo.
> La **Parte C** es la auditoría que originó todo esto — está aquí porque
> explica *por qué* varios pasos son como son, y porque encontró un bug vivo
> en la app de iOS que ya estaba publicada.

**Por qué existe este proyecto:** un cliente quiere comprar equipos **Android**
(teléfonos o tabletas, aún sin definir) para sus asesores. Antes de eso el
proyecto de Android era especulativo; ahora hay un cliente pagando.

**Dato que conviene no perder de vista:** la app de iOS tiene **cero adopción
real**. Verificado en `user_sessions`: fuera del Super Admin, la única persona
que la ha abierto es Manuel Estrada, y la última vez fue el **9 de abril**.
Esto no cancela el proyecto —el cliente lo pidió— pero sí dice que la app no se
vende sola: hay que pedirle a la gente que la instale.

**Un APK sirve para teléfono y tableta.** Lo único que cambia es el diseño en
pantalla (ver Parte C, punto 6).

---

# PARTE A — LO QUE YA QUEDÓ

| # | Qué | PR | Estado |
|---|---|---|---|
| 1 | `colors.xml` — el proyecto Android **no compilaba** | #1041 | ✅ |
| 2 | `test_android_build.php` — detecta recursos faltantes **sin el SDK** | #1041 | ✅ |
| 3 | `es_app_nativa()` — la detección de app nativa nunca funcionó | #1040 | ✅ |
| 4 | Los 9 puntos de compra gateados por esa única puerta | #1040 | ✅ |
| 5 | En la app se ven los planes sin precios, mencionando cotiza.cloud | #1040 | ✅ |
| 6 | `test_app_nativa.php` — 34 comprobaciones | #1040 | ✅ |

El 3 al 6 **no eran de Android**: eran un bug vivo en el iOS publicado que
Android iba a heredar. Ver Parte C.

---

# PARTE B — LO QUE FALTA, EN ORDEN

## Paso 1 — Firebase (lo hace el CEO, ~15 min)

Sin esto no se puede avanzar en el push, que es el bloque grande.

**1.1 Crear el proyecto y la app**
1. Entrar a `console.firebase.google.com` → **Agregar proyecto** (nombre:
   CotizaCloud).
2. Dentro del proyecto → **Agregar app** → **Android**.
3. Nombre del paquete, **exacto**: `com.cotizacloud.app`
   (debe coincidir con `applicationId` de `android/app/build.gradle`).
4. Descargar el **`google-services.json`** que genera.

**1.2 La credencial del servidor**
1. En el proyecto → ⚙️ **Configuración** → **Cuentas de servicio**.
2. **Generar nueva clave privada** → descarga un `.json`.
3. **Ese archivo es como el `.p8` de Apple: NO va al repositorio.**
   Subirlo al servidor junto al `.p8`:

```bash
# desde tu Mac
scp ~/Downloads/cotizacloud-firebase-adminsdk-XXXX.json \
    root@212.28.186.247:/var/www/cotizacloud-keys/fcm-service-account.json

# en el servidor
chown root:www-data /var/www/cotizacloud-keys/fcm-service-account.json
chmod 640            /var/www/cotizacloud-keys/fcm-service-account.json
```

**1.3 Agregar al `config.php` del servidor**

```php
// Firebase Cloud Messaging (push Android)
define('FCM_PROJECT_ID',   'cotizacloud-xxxxx');   // el del proyecto Firebase
define('FCM_SERVICE_JSON', '/var/www/cotizacloud-keys/fcm-service-account.json');
```

> **Verificación del paso 1:** `php -r 'require "/var/www/cotizacloud/config.php";
> echo FCM_PROJECT_ID, " ", is_readable(FCM_SERVICE_JSON) ? "OK" : "NO SE LEE";'`

---

## Paso 2 — `enviar_fcm()` en el servidor (lo hace Claude)

Es la mitad del trabajo del push y **no depende de que el proyecto Android
compile**, por eso va antes.

**Dónde:** `core/PushNotification.php`, donde hoy hay un comentario que dice
literalmente `// Android (FCM) se agregará después` (línea ~133).

**Qué hay que escribir:**
- OAuth2 con la cuenta de servicio: JWT **RS256** → intercambio por access
  token en `https://oauth2.googleapis.com/token`. La casa ya firma ES256 (APNs)
  y VAPID, así que el terreno es conocido.
- `POST https://fcm.googleapis.com/v1/projects/{FCM_PROJECT_ID}/messages:send`
- Cachear el access token (dura 1 hora) para no pedir uno por notificación.
- Tokens muertos: FCM responde `UNREGISTERED` / `INVALID_ARGUMENT` → reusar
  `desactivar_token()`, que ya existe.

**Lo que YA está listo y no hay que tocar:**
- `dispositivos_push.plataforma` acepta `'android'`.
- `api/push_register.php:35` ya valida `['ios','android','web']`.
- `assets/js/push.js:42` ya manda `getPlatform()` → `'android'`.

**Los tres puntos de envío** que hay que cubrir (`PushNotification.php` líneas
~80, ~128 y ~357) — hoy cada uno tiene su `if ios / elseif web`.

> **Verificación del paso 2:** las pruebas de texto de la suite. El envío real
> NO se puede probar sin dispositivo — ver "Lo que no se puede verificar".

---

## Paso 3 — El proyecto Android (Claude + CEO)

**3.1 Sincronizar los plugins** (Claude, o el CEO en su Mac)

`android/capacitor.settings.gradle` hoy solo incluye splash-screen y
status-bar. **Falta el de push**, que sí está en `package.json`.

```bash
cd ~/cotizacloud
npm install
npx cap sync android
```

Después de eso, `capacitor.settings.gradle` debe incluir
`:capacitor-push-notifications`. Si no aparece, el push no existe en el APK.

**3.2 Poner el `google-services.json`**

```
android/app/google-services.json    ← el del paso 1.1
```

> No es un secreto (va dentro del APK), pero el `.gitignore` de Android trae la
> línea comentada. Decidir si se commitea o no.

**3.3 El plugin de Gradle**

En `android/build.gradle` (el raíz), dentro de `dependencies` de `buildscript`:
```gradle
classpath 'com.google.gms:google-services:4.4.2'
```
Y al final de `android/app/build.gradle`:
```gradle
apply plugin: 'com.google.gms.google-services'
```

**3.4 Permiso de notificaciones (Android 13+)**

En `AndroidManifest.xml`, junto al de INTERNET:
```xml
<uses-permission android:name="android.permission.POST_NOTIFICATIONS" />
```
El plugin de Capacitor pide el permiso en tiempo de ejecución; el manifest solo
lo declara. **Sin esto, en Android 13 o superior no llega ninguna notificación.**

> **Verificación del paso 3:** `php tools/test_android_build.php` y que
> Android Studio compile sin errores.

---

## Paso 4 — Compilar y probar en el equipo real (CEO)

**Esto no lo puede hacer Claude** — no hay SDK de Android en el entorno, y el
push necesita un dispositivo físico.

1. Abrir el proyecto: `npx cap open android` (requiere Android Studio).
2. Conectar el equipo Android por USB, con depuración USB activada.
3. Correr la app.

**Lista de verificación en el equipo, en este orden:**

| # | Qué probar | Qué debe pasar |
|---|---|---|
| 1 | Abrir la app | Splash verde → login (no la landing) |
| 2 | Iniciar sesión | Entra al dashboard |
| 3 | **Sidebar y banners** | **NO** debe verse "Mejorar plan" ni "Activa tu plan" |
| 4 | Ayuda → Planes | Ve los 3 planes **sin precios**, mencionando cotiza.cloud |
| 5 | **Escudo Radar** | Aparece el banner; al tocar "Activar" **abre el navegador externo**, no se queda dentro |
| 6 | Notificaciones | Pide permiso; aceptar |
| 7 | Push real | Que alguien acepte una cotización → debe llegar la notificación |
| 8 | Abrir una cotización desde el navegador del equipo | La visita **no** debe contarse como cliente |

El punto 5 es el más importante: **el Escudo es lo que justifica la app.**
Debería funcionar — está garantizado por código, no por comportamiento
emergente (ver Parte C, punto 4) — pero hay que verlo.

---

## Paso 5 — Entregarle al cliente

**Probablemente NO necesitas Google Play.** Si el cliente compra los equipos
para su propio equipo, se le puede dar el APK directo o usar distribución
interna. Eso elimina:
- la cuenta de desarrollador (25 USD),
- la ficha y el formulario de Data Safety,
- y lo más caro en calendario: **las pruebas cerradas con 12 testers durante
  14 días**, que aplican a cuentas personales.

Para firmar el APK hace falta un **keystore**:
```bash
keytool -genkey -v -keystore cotizacloud.keystore -alias cotizacloud \
        -keyalg RSA -keysize 2048 -validity 10000
```
> ⚠️ **Guarda ese archivo y su contraseña como si fueran el `.p8` de Apple.**
> Si se pierde, no se pueden volver a publicar actualizaciones de esa app —
> nunca. Ni Google lo puede recuperar.

Play sigue teniendo sentido después, como presencia comercial. **Pero no
bloquea la entrega a este cliente.**

---

# PARTE C — LA AUDITORÍA QUE ORIGINÓ ESTO

## 1. La app no es una app

Es `server.url: 'https://cotiza.cloud'` en `capacitor.config.ts`. Todo
—cotizaciones, radar, ventas, reportes— es la web. **Android lo hereda gratis:
no hay que reescribir nada.**

Lo único que la app agrega sobre abrir Chrome son **dos cosas**: el push y el
Escudo. De ahí que el push no sea "el paso 2" sino la razón de ser del
proyecto.

## 2. La detección de app nativa NUNCA funcionó (bug vivo en iOS)

El commit `69bedb0` (1 abril) implementó la regla de Apple 3.1.1 con
`str_contains($_SERVER['HTTP_USER_AGENT'], 'CotizaCloud')` repetido en **seis
archivos**. Esa comprobación jamás fue verdadera: el WKWebView no pone el
nombre de la app en el User-Agent y **`appendUserAgent` nunca se configuró** —
buscado en toda la historia de `capacitor.config.ts`.

**Verificado contra producción:** cero sesiones con esa palabra en 30 días. La
firma real del WebView de iOS es:
```
Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 …) AppleWebKit/605.1.15 … Mobile/15E148
```
**sin `Version/` y sin `Safari/`** — Safari de verdad siempre trae las dos.

Como el efecto era **ocultar**, el fallo fue invisible durante meses: sin
error, sin log, sin que nadie lo notara. Y los tres puntos escritos *después*
(dashboard con otro nombre de variable, bienvenida, redirect del ticket) ni
siquiera intentaron comprobarlo.

**Por qué nadie lo vio:** todas las cuentas que usan la app son `business`, y
una cuenta Business no ve el "Mejorar plan" (`layout.php:455` la salta) ni los
banners de trial. **La única persona que iba a probar la app era la única que
no podía notarlo.**

**Exposición real: cero.** Las 9 sesiones de app que existen son todas
`business` — incluida la del revisor de Apple (1 abril), cuya cuenta se
configuró como business a propósito. La app pasó la revisión no porque el gate
funcionara, sino porque el revisor entró con una cuenta que no dispara nada de
lo que el gate escondía.

**Arreglado en #1040** con una sola puerta, `es_app_nativa()`, que lee:
1. Cookie `cz_app` puesta por el **servidor en el login** — el formulario de la
   app ya mandaba `is_app=1` desde abril (`login.php:353`), o sea que el dato
   siempre estuvo ahí y nadie lo usó para esto. Vale desde la primera pantalla,
   sin parpadeo.
2. La misma cookie repuesta por el JS, para sesiones abiertas antes del fix.
3. El User-Agent, por si algún día entra `appendUserAgent`.

**`appendUserAgent` NO hace falta.** Se verificó: la landing redirige desde la
línea 4 del `<head>` (antes de cualquier markup), y login/registro/bienvenida
no usan `layout.php` ni contienen precios. La cobertura está completa sin
recompilar.

## 3. La regla del negocio (decisión del CEO)

**Estilo Netflix: la app es para USAR; todo lo de dinero vive en la web.**

El asesor **sí** puede ver que existen planes superiores —para saber qué se
está perdiendo— pero **sin precios** y **siempre mencionando** que se contratan
en `cotiza.cloud` desde el navegador. Nada de checkout, tarifas ni botones de
compra dentro de la app.

Los 9 puntos que llevan a comprar están gateados y `test_app_nativa.php` falla
si aparece un décimo sin puerta.

## 4. El Escudo Radar SÍ funciona en Android (resuelto por código fuente)

No hacía falta un dispositivo para saberlo. `Bridge.launchIntent()` de
Capacitor 8 (líneas 413-425):

```java
Uri appUri = Uri.parse(appUrl);
if (!(appUri.getHost().equals(url.getHost()) && url.getScheme().equals(appUri.getScheme()))
    && !appAllowNavigationMask.matches(url.getHost())) {
    Intent openIntent = new Intent(Intent.ACTION_VIEW, url);
    getContext().startActivity(openIntent);   // ← navegador EXTERNO
    return true;
}
```

`appUrl` es `https://cotiza.cloud`; el Escudo va a
`https://<empresa>.cotiza.cloud/api/safari-bridge` — **otro host** → sale al
navegador externo. Y `capacitor.config.ts` no define `allowNavigation`, así que
la máscara está vacía y no lo rescata.

Además `setSupportMultipleWindows` nunca se activa y no hay `onCreateWindow`,
así que el `target="_blank"` se carga en el mismo WebView → dispara
`shouldOverrideUrlLoading` → cae en ese `launchIntent`.

**Es más sólido que en iOS**: allá depende de un comportamiento emergente del
WKWebView; en Android está en un `if` explícito.

> ⚠️ **No agregar `allowNavigation` al config sin pensarlo.** Si alguien mete
> `*.cotiza.cloud` ahí, el Escudo deja de funcionar en Android — el subdominio
> se quedaría dentro del WebView y las cookies nunca saldrían al navegador.

**Falla silenciosa arreglada de paso:** sin cookie `cz_vid`, `$escudo_url`
queda vacío y el botón "Activar" era un `<a href="">` que solo recargaba — el
asesor tocaba, no pasaba nada, y se quedaba sin Escudo sin saber por qué. Ahora
en ese caso el banner no se ofrece.

## 5. Lo que NO va a ser réplica exacta

| | iOS | Android |
|---|---|---|
| **Badge del ícono** | Número, manejado por `AppDelegate.swift` | **No existe igual.** El lanzador muestra un punto; el número depende del fabricante. `badge_count` y `reset_badge` quedan sin efecto visible — no rompen nada |
| **Splash** | Imagen completa | `AppTheme.NoActionBarLaunch` hereda de `Theme.SplashScreen` (API de Android 12+) pero pone la imagen con `android:background`. Esa API lo ignora: pinta ícono sobre color plano |
| **Autocompletar contraseña** | `webcredentials` en `App.entitlements` + AASA | Necesitaría `/.well-known/assetlinks.json` con el SHA-256 del certificado de firma. **No está hecho** |
| **Push silencioso** | No configurado (`Info.plist` sin `UIBackgroundModes`) | — |

## 6. El diseño en tableta

El sistema tiene **un solo punto de quiebre: 768 px** (`layout.php:327`).

| Equipo | Ancho CSS | Diseño que le toca |
|---|---|---|
| Teléfono Android | 360-430 px | Móvil (barra inferior) |
| iPad vertical | 768 px | Móvil |
| iPad horizontal | 1024 px | Escritorio (sidebar) |
| **Tableta Android 10" vertical** | **~800 px** | **Escritorio, en pantalla angosta** |

El último es el caso a mirar con el equipo enfrente. No digo que se rompa —hay
que verlo— pero es lo primero que revisaría antes de entregar.

## 7. Riesgo operativo: tabletas compartidas

Si varios asesores comparten una tableta, **el termómetro le cuenta la
actividad a quien haya dejado la sesión abierta**.

Para el Escudo no hay problema —una tableta de la empresa marcada como interna
es justo lo correcto—, pero para el score sí. Si es una tableta por asesor, no
aplica. Si son de mostrador compartidas, hay que decidir cómo se maneja el
cierre de sesión.

## 8. Lo que NO se puede verificar desde el entorno de Claude

Decirlo antes, no después:

- **No se puede compilar** — no hay SDK de Android.
- **No se puede probar el push real** — necesita dispositivo físico.
- **No se puede probar el Escudo en vivo** — aunque el código fuente dice que
  funciona (punto 4).
- Las **4 simulaciones contra MariaDB** dejaron de correr a media sesión: el
  motor ya no está instalado en el contenedor. Las pruebas de texto sí corren.

El código se va a dejar escrito y probado en lo que se puede probar sin red y
sin dispositivo. **La verificación final la hace el CEO con el equipo en la
mano** (Paso 4).

---

## Pruebas relacionadas

| Archivo | Qué cuida |
|---|---|
| `tools/test_android_build.php` | Que ningún `@color/` o `@string/` citado falte — detecta el error de compilación **sin el SDK** |
| `tools/test_app_nativa.php` | Que nadie vuelva a resolver la detección por su cuenta; que los 9 puntos de compra sigan gateados; que el Escudo no quede colgado de esa puerta |
