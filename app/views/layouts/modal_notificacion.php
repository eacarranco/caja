<style>
#notificacionOverlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 100000;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}
#notificacionOverlay.show {
    display: flex;
}
#notifModalBox {
    background: #fff;
    border-radius: 12px;
    padding: 2rem 1.5rem;
    max-width: 340px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: notifFadeIn 0.2s ease-out;
}
@keyframes notifFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
</style>
<div id="notificacionOverlay">
    <div id="notifModalBox">
        <div id="notifModalIcon" class="mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
        </div>
        <h5 id="notifModalTitle" class="mb-2"></h5>
        <p id="notifModalMessage" class="text-muted mb-3 small"></p>
        <button type="button" class="btn btn-primary px-4" onclick="cerrarModalNotificacion()">Aceptar</button>
    </div>
</div>
