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

### Completado (código listo, falta configurar Apple)
- Plugin `@capacitor/push-notifications@8.0.2` instalado y sincronizado
- Tablas BD: migración en `migrations/add_push_notifications.sql` (pendiente ejecutar en servidor)
  - `dispositivos_push` — tokens de dispositivos
  - `notificaciones_push` — log de notificaciones enviadas
- Servicio PHP APNs: `core/PushNotification.php` — envío via HTTP/2 con JWT ES256
- API endpoints:
  - `POST /api/push/register` — registra token del dispositivo (requiere login)
  - `POST /api/push/unregister` — desactiva token
- Hooks en `api/quote_action.php` — dispara push al aceptar/rechazar cotización
- JS cliente: `assets/js/push.js` — pide permisos, registra token, muestra banner en foreground
- Config APNs: constantes en `config.php` (vacías, pendiente llenar)
- Plugin configurado en `capacitor.config.ts` con `presentationOptions: ['badge', 'sound', 'alert']`

### Pendiente (requiere cuenta Apple Developer activa)
1. **Ejecutar migración SQL** en servidor: `migrations/add_push_notifications.sql`
2. **Crear APNs Key** en Apple Developer Portal:
   - Ir a Certificates, Identifiers & Profiles > Keys > "+"
   - Habilitar "Apple Push Notifications service (APNs)"
   - Descargar el archivo `.p8` y subirlo al servidor
3. **Configurar en `config.php`** o variables de entorno:
   - `APNS_KEY_PATH` → ruta al archivo .p8 en el servidor
   - `APNS_KEY_ID` → Key ID que da Apple al crear la key
   - `APNS_TEAM_ID` → Team ID de la cuenta Apple Developer
4. **Habilitar Push Notifications capability** en Xcode:
   - Abrir proyecto > Target App > Signing & Capabilities > "+ Capability" > Push Notifications
5. **Compilar y probar** en dispositivo real (push no funciona en simulador)

### Cuenta Apple Developer
- **Estado**: Pagada, esperando activación (normalmente 24-48 horas)
- Se necesita para: certificado APNs, publicar en App Store

## Próximos Pasos
1. Configurar push notifications cuando se active la cuenta Apple Developer
2. Probar la app completa (login, cotizaciones, navegación entre módulos)
3. Verificar íconos y splash screen en todos los tamaños
4. App Store Connect: crear ficha (screenshots, descripción, categoría)
5. Build de producción: archivar desde Xcode y subir a App Store
6. Android: carpeta `android/` ya existe, falta probar y publicar en Google Play

## Comandos Útiles
```bash
# Sincronizar cambios web con iOS
cd ~/cotizacloud && npx cap sync ios

# Abrir proyecto en Xcode
open ~/cotizacloud/ios/App/App.xcodeproj

# Sincronizar Android
npx cap sync android
```
