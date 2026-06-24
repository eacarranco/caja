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

function mostrarNotificacion(tipo, titulo, mensaje, autoClose) {
    var overlay = document.getElementById('notificacionOverlay');
    if (!overlay) return;
    var iconMap = {
        'success': ['bi-check-circle-fill', 'text-success'],
        'error':   ['bi-x-circle-fill', 'text-danger'],
        'warning': ['bi-exclamation-circle-fill', 'text-warning'],
        'info':    ['bi-info-circle-fill', 'text-primary'],
    };
    var cls = iconMap[tipo] || iconMap['info'];
    var icon = overlay.querySelector('#notifModalIcon i');
    icon.className = 'bi ' + cls[0] + ' ' + cls[1];
    document.getElementById('notifModalTitle').textContent = titulo;
    document.getElementById('notifModalMessage').textContent = mensaje;
    overlay.classList.add('show');
    if (autoClose !== false) {
        setTimeout(cerrarModalNotificacion, 4000);
    }
}
function cerrarModalNotificacion() {
    var overlay = document.getElementById('notificacionOverlay');
    if (overlay) overlay.classList.remove('show');
}

function showToast(message, type) {
    type = type || 'success';
    var container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-' + type + ' border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    container.appendChild(toast);
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        var bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', function() { toast.remove(); });
    } else {
        toast.classList.add('show');
        setTimeout(function() { toast.remove(); }, 5000);
    }
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
    actualizarBuzonesBadge && actualizarBuzonesBadge();
}

function actualizarBandejaBadge() {
    fetch(BASE_URL + '/dashboard/contarPendientes')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var total = (d.creditos || 0) + (d.inversiones || 0);
            var badges = { 'Total': total, 'Creditos': d.creditos || 0, 'Inversiones': d.inversiones || 0 };
            Object.keys(badges).forEach(function(t) {
                var el = document.getElementById('bandejaBadge' + t);
                if (el) {
                    var count = badges[t];
                    if (count > 0) {
                        el.textContent = Math.min(count, 99);
                        el.classList.remove('d-none');
                    } else {
                        el.classList.add('d-none');
                    }
                }
            });
        }).catch(function() {});
}

document.addEventListener('DOMContentLoaded', actualizarBandejaBadge);

if (typeof Pusher !== 'undefined' && typeof PUSHER_KEY !== 'undefined' && PUSHER_KEY) {
    var pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER || 'us2' });
    var channel = pusher.subscribe('canal-general');
    channel.bind('notificacion', function(data) {
        if (typeof data === 'string') { try { data = JSON.parse(data); } catch(e) { return; } }
        actualizarNotifBadge();
        actualizarBandejaBadge();
        // Check if the notification is for this user
        var paraMi = false;
        if (!data.id_socio && !data.id_usuario) {
            paraMi = true; // General notification for everyone
        } else if (data.id_socio && typeof SOCIO_ID !== 'undefined' && SOCIO_ID && data.id_socio === SOCIO_ID) {
            paraMi = true; // Notification for this socio
        } else if (data.id_usuario && typeof USUARIO_ID !== 'undefined' && USUARIO_ID && data.id_usuario === USUARIO_ID) {
            paraMi = true; // Notification for this admin user
        }
        if (paraMi && data.titulo) {
            showToast(data.titulo + ': ' + (data.mensaje || ''), 'info');
        }
    });
    channel.bind('actualizar-portal', function(data) {
        if (typeof data === 'string') { try { data = JSON.parse(data); } catch(e) { return; } }
        // Only update if this event is for the current user
        if (typeof SOCIO_ID !== 'undefined' && SOCIO_ID && data.id_socio && data.id_socio !== SOCIO_ID) {
            return;
        }
        // Update ahorro card
        var ahorroCard = document.querySelector('[data-portal="ahorro"]');
        if (ahorroCard) {
            ahorroCard.textContent = '$ ' + (data.ahorro_total || 0).toFixed(2);
            ahorroCard.style.transition = 'background 0.3s';
            ahorroCard.style.background = '#d4edda';
            setTimeout(function() { ahorroCard.style.background = ''; }, 1500);
        }
        // Update capital inversion card
        var capInvCard = document.querySelector('[data-portal="capital_inversion"]');
        if (capInvCard) {
            capInvCard.textContent = '$ ' + (data.capital_inversion || 0).toFixed(2);
            capInvCard.style.transition = 'background 0.3s';
            capInvCard.style.background = '#d4edda';
            setTimeout(function() { capInvCard.style.background = ''; }, 1500);
        }
        // Update valores a pagar card
        var pagarCard = document.querySelector('[data-portal="valores_pagar"]');
        if (pagarCard) {
            pagarCard.textContent = '$ ' + (data.valores_pagar || 0).toFixed(2);
            pagarCard.style.transition = 'background 0.3s';
            pagarCard.style.background = '#f8d7da';
            setTimeout(function() { pagarCard.style.background = ''; }, 1500);
        }
    });
} else {
    setInterval(actualizarNotifBadge, 30000);
    setInterval(actualizarBandejaBadge, 30000);
}
