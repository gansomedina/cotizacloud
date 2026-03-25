// ============================================================
//  CotizaCloud — Web Push (solo navegador, NO app nativa)
//  Registra Service Worker y subscription para notificaciones
// ============================================================

(function () {
    'use strict';

    // ── Guard 1: NO ejecutar en app nativa Capacitor ──
    if (window.Capacitor && window.Capacitor.isNativePlatform()) return;

    // ── Guard 2: el navegador soporta Push y Service Workers ──
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    // ── Guard 3: debe haber un usuario logueado ──
    // (la variable WEBPUSH_KEY se inyecta desde layout.php solo si hay sesión)
    if (typeof WEBPUSH_KEY === 'undefined' || !WEBPUSH_KEY) return;

    // ── Convertir base64url a Uint8Array (para applicationServerKey) ──
    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; i++) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // ── Registrar SW y subscription ──
    navigator.serviceWorker.register('/sw.js').then(function (reg) {

        // Esperar a que el SW esté activo
        return navigator.serviceWorker.ready;

    }).then(function (reg) {

        // Verificar si ya hay una subscription activa
        return reg.pushManager.getSubscription().then(function (sub) {
            if (sub) {
                // Ya registrado — enviar al servidor por si cambió de usuario
                enviarSubscription(sub);
                return;
            }

            // Verificar permiso actual
            if (Notification.permission === 'denied') return;

            // Solo pedir permiso si el usuario interactúa (no automático en carga)
            // Mostrar botón flotante sutil para activar
            mostrarBotonActivar(reg);
        });

    }).catch(function (err) {
        console.warn('[WebPush] SW registration error:', err);
    });

    // ── Enviar subscription al servidor ──
    function enviarSubscription(sub) {
        var json = sub.toJSON();
        fetch('/api/push/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                token: JSON.stringify(json),
                plataforma: 'web'
            })
        }).then(function (res) { return res.json(); })
          .then(function (data) {
              if (data.ok) localStorage.setItem('wp_registered', '1');
          })
          .catch(function () {});
    }

    // ── Solicitar permiso y suscribir ──
    function suscribir(reg) {
        var appServerKey = urlBase64ToUint8Array(WEBPUSH_KEY);
        return reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: appServerKey
        }).then(function (sub) {
            enviarSubscription(sub);
            return sub;
        });
    }

    // ── Botón flotante para activar notificaciones ──
    function mostrarBotonActivar(reg) {
        // No mostrar si ya se registró antes o si el usuario lo cerró
        if (localStorage.getItem('wp_registered') === '1') return;
        if (localStorage.getItem('wp_dismissed') === '1') return;

        var banner = document.createElement('div');
        banner.id = 'wp-banner';
        banner.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);' +
            'z-index:9999;background:#1a5c38;color:#fff;padding:12px 20px;border-radius:12px;' +
            'font:500 13px -apple-system,BlinkMacSystemFont,sans-serif;box-shadow:0 4px 20px rgba(0,0,0,.2);' +
            'display:flex;align-items:center;gap:12px;max-width:400px;width:calc(100% - 40px);' +
            'animation:wpSlideUp .4s ease-out';
        banner.innerHTML =
            '<div style="flex:1">' +
                '<div style="font-weight:700;margin-bottom:2px">Activar notificaciones</div>' +
                '<div style="font-size:11px;opacity:.8">Recibe avisos de cotizaciones y pagos</div>' +
            '</div>' +
            '<button id="wp-accept" style="background:#fff;color:#1a5c38;border:none;padding:8px 16px;' +
                'border-radius:8px;font:700 12px inherit;cursor:pointer;white-space:nowrap">Activar</button>' +
            '<button id="wp-dismiss" style="background:none;border:none;color:#fff;cursor:pointer;' +
                'font-size:18px;opacity:.6;padding:4px" title="Cerrar">\u2715</button>';

        // CSS animación
        if (!document.getElementById('wp-styles')) {
            var style = document.createElement('style');
            style.id = 'wp-styles';
            style.textContent = '@keyframes wpSlideUp{from{transform:translateX(-50%) translateY(100px);opacity:0}to{transform:translateX(-50%) translateY(0);opacity:1}}';
            document.head.appendChild(style);
        }

        document.body.appendChild(banner);

        document.getElementById('wp-accept').onclick = function () {
            banner.remove();
            suscribir(reg).catch(function (err) {
                console.warn('[WebPush] Subscribe error:', err);
            });
        };

        document.getElementById('wp-dismiss').onclick = function () {
            banner.remove();
            localStorage.setItem('wp_dismissed', '1');
        };
    }

})();
