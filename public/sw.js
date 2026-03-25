// ============================================================
//  CotizaCloud — Service Worker para Web Push
//  Solo maneja notificaciones push del navegador
// ============================================================

self.addEventListener('push', function (event) {
    var data = { title: 'CotizaCloud', body: 'Tienes una nueva notificación', url: '/' };

    // Intentar parsear el payload si viene
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text() || data.body;
        }
    }

    var options = {
        body: data.body || '',
        icon: '/assets/img/icon-192.png',
        badge: '/assets/img/icon-72.png',
        tag: data.tag || 'cotizacloud-' + Date.now(),
        data: { url: data.url || '/' },
        requireInteraction: false,
        silent: false
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'CotizaCloud', options)
    );
});

// Click en la notificación: abrir la URL
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var url = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            // Si ya hay una pestaña abierta, enfocarla y navegar
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url.indexOf(self.location.origin) !== -1) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            // Si no hay pestaña abierta, abrir una nueva
            return clients.openWindow(url);
        })
    );
});
