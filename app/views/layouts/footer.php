    <?php if (isset($_SESSION['usuario_id']) && isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified']): ?>
        </div>
    </div>
    <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/components/dark.js"></script>
    <script src="<?= $baseUrl ?>/public/assets/js/app.js"></script>
</body>
</html>
