self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/favicon.ico',
            data: msg.data,
            vibrate: [200, 100, 200, 100, 200, 100, 200] // Biar HP getar
        }));
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data && event.notification.data.url) {
        clients.openWindow(event.notification.data.url);
    }
});