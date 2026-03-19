// ============================================================
//  CotizaCloud — Push Notifications (Capacitor)
//  Se ejecuta solo dentro de la app nativa (iOS/Android)
// ============================================================

(function () {
    // Solo ejecutar si estamos en Capacitor (app nativa)
    console.log('[Push] Capacitor:', !!window.Capacitor, 'isNative:', window.Capacitor && window.Capacitor.isNativePlatform());
    if (!window.Capacitor || !window.Capacitor.isNativePlatform()) return;

    var PushNotifications = window.Capacitor.Plugins.PushNotifications;
    console.log('[Push] Plugin PushNotifications:', !!PushNotifications);
    if (!PushNotifications) return;

    // Pedir permisos y registrar
    function initPush() {
        console.log('[Push] initPush called');
        PushNotifications.checkPermissions().then(function (result) {
            console.log('[Push] checkPermissions result:', JSON.stringify(result));
            if (result.receive === 'prompt') {
                PushNotifications.requestPermissions().then(function (perm) {
                    console.log('[Push] requestPermissions result:', JSON.stringify(perm));
                    if (perm.receive === 'granted') {
                        PushNotifications.register();
                    }
                });
            } else if (result.receive === 'granted') {
                PushNotifications.register();
            }
        }).catch(function(err) {
            console.error('[Push] checkPermissions error:', err);
        });
    }

    // Cuando se obtiene el token, enviarlo al servidor
    PushNotifications.addListener('registration', function (token) {
        console.log('[Push] Token obtenido:', token.value.substring(0, 20) + '...');
        var plataforma = window.Capacitor.getPlatform(); // 'ios' o 'android'

        fetch('/api/push/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                token: token.value,
                plataforma: plataforma
            })
        }).then(function (res) {
            console.log('[Push] Registro HTTP status:', res.status);
            return res.json();
        }).then(function (data) {
            console.log('[Push] Registro respuesta:', JSON.stringify(data));
        }).catch(function (err) {
            console.warn('[Push] Registro error:', err);
        });
    });

    // Error de registro
    PushNotifications.addListener('registrationError', function (error) {
        console.warn('Push registration error:', error);
    });

    // Notificación recibida con app en primer plano
    PushNotifications.addListener('pushNotificationReceived', function (notification) {
        // Mostrar un banner simple en la app
        var banner = document.createElement('div');
        banner.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;' +
            'background:#1a5c38;color:#fff;padding:16px 20px;font-size:14px;' +
            'font-family:inherit;box-shadow:0 4px 16px rgba(0,0,0,.15);' +
            'cursor:pointer;padding-top:calc(16px + env(safe-area-inset-top,0px));' +
            'animation:pushSlide .3s ease-out';
        banner.innerHTML = '<strong>' + (notification.title || 'CotizaCloud') + '</strong><br>' +
            (notification.body || '');

        // Click navega a la URL si viene en datos
        banner.onclick = function () {
            banner.remove();
            if (notification.data && notification.data.url) {
                window.location.href = notification.data.url;
            }
        };

        document.body.appendChild(banner);

        // Auto-ocultar después de 5s
        setTimeout(function () {
            if (banner.parentNode) banner.remove();
        }, 5000);
    });

    // Push tapped (app estaba en background)
    PushNotifications.addListener('pushNotificationActionPerformed', function (action) {
        var data = action.notification.data;
        if (data && data.url) {
            window.location.href = data.url;
        }
    });

    // Iniciar
    initPush();
})();
