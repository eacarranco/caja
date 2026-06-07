document.addEventListener('DOMContentLoaded', function() {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        window.csrfToken = csrfMeta.getAttribute('content');
    }

    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            alert.classList.add('fade');
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
});

function showToast(message, type) {
    type = type || 'success';
    var container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-' + type + ' border-0 show';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    container.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 5000);
}

function actualizarNotifBadge() {
    fetch(BASE_URL + '/notificacion/contar')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var badge = document.getElementById('notifBadge');
            if (badge) {
                if (d.pendientes > 0) {
                    badge.textContent = Math.min(d.pendientes, 99);
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            }
        }).catch(function() {});
}

if (typeof Pusher !== 'undefined' && typeof PUSHER_KEY !== 'undefined' && PUSHER_KEY) {
    var pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER || 'us2' });
    var channel = pusher.subscribe('canal-general');
    channel.bind('notificacion', function(data) {
        actualizarNotifBadge();
        if (data.titulo) {
            showToast(data.titulo + ': ' + (data.mensaje || ''), 'info');
        }
    });
} else {
    setInterval(actualizarNotifBadge, 30000);
}
