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
- **App enviada a revisión el 20 marzo 2026** — esperando respuesta de Apple (24-48 hrs)

### Pendiente
1. Esperar aprobación de Apple (o corregir si rechazan)
2. Agregar `ITSAppUsesNonExemptEncryption = NO` al Info.plist para evitar pregunta de compliance en futuros builds
3. Probar push notifications en dispositivo real
4. Cambiar `aps-environment` en `App.entitlements` de `development` a `production`
5. Android: carpeta `android/` ya existe, falta probar y publicar en Google Play

## Comandos Útiles
```bash
# Sincronizar cambios web con iOS
cd ~/cotizacloud && npx cap sync ios

# Abrir proyecto en Xcode
open ~/cotizacloud/ios/App/App.xcodeproj

# Sincronizar Android
npx cap sync android
```
