    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?? '' ?>/public/assets/js/app.js"></script>
    <?php if (!empty(PUSHER_APP_KEY)): ?>
    <script>
        window.pusherKey = '<?= PUSHER_APP_KEY ?>';
        window.pusherCluster = '<?= PUSHER_APP_CLUSTER ?>';
        window.pusherChannel = 'privado-socio-<?= $_SESSION['usuario_id'] ?? '' ?>';
    </script>
    <?php endif; ?>
</body>
</html>
