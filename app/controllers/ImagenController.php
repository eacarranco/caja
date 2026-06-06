<?php
class ImagenController extends BaseController {

    public function index() {
        $this->requirePermission('param.imagen');
        $stmt = $this->db->query("SELECT * FROM parámetros WHERE código LIKE 'img.%' OR código LIKE 'color.%' ORDER BY código");
        $params = $stmt->fetchAll();
        $this->render('parametros/imagen', [
            'titulo' => 'Imagen corporativa',
            'params' => $params,
        ]);
    }

    public function subirLogo() {
        $this->requirePermission('param.imagen');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['png', 'jpg', 'jpeg', 'svg'])) {
                    $this->json(['error' => 'Formato no permitido. Use PNG, JPG o SVG'], 400);
                }
                $dest = dirname(__DIR__, 2) . '/public/assets/img/logo.' . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], $dest);

                $stmt = $this->db->prepare("UPDATE parámetros SET valor = ? WHERE código = 'img.logo'");
                $stmt->execute(['assets/img/logo.' . $ext]);
                $this->json(['mensaje' => 'Logo actualizado']);
            } else {
                $this->json(['error' => 'No se recibió el archivo'], 400);
            }
        }
    }

    public function guardarColor() {
        $this->requirePermission('param.imagen');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $codigo = $_POST['codigo'] ?? '';
            $valor = $_POST['valor'] ?? '';
            $stmt = $this->db->prepare("UPDATE parámetros SET valor = ? WHERE código = ?");
            $stmt->execute([$valor, $codigo]);
            $this->json(['mensaje' => 'Color actualizado']);
        }
    }
}
