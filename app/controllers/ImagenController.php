<?php
require_once ROOT_PATH . '/app/helpers/FileManager.php';

class ImagenController extends BaseController {

    public function index() {
        $this->requirePermission('param.imagen');
        $stmt = $this->db->query("SELECT * FROM parámetros WHERE código LIKE 'color.%' OR código IN ('logo_sidebar', 'logo_sd') ORDER BY código");
        $params = $stmt->fetchAll();
        $this->render('parametros/imagen', [
            'titulo' => 'Imagen corporativa',
            'params' => $params,
        ]);
    }

    public function subirImagenParam() {
        $this->requirePermission('param.imagen');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }
        $this->validateCSRF();
        $codigo = $_POST['codigo'] ?? '';
        if (!in_array($codigo, ['logo_sidebar', 'logo_sd'])) {
            $this->json(['error' => 'Código de parámetro inválido'], 400);
        }
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'No se recibió el archivo'], 400);
        }
        $result = FileManager::upload($_FILES['archivo'], 'imagen', $codigo, 'imagen', $_SESSION['usuario_id'] ?? null);
        if (!$result['success']) {
            $this->json(['error' => $result['error']], 400);
        }
        $stmt = $this->db->prepare("UPDATE parámetros SET valor = ? WHERE código = ?");
        $stmt->execute([$result['id_archivo'], $codigo]);
        $this->json(['mensaje' => 'Imagen actualizada', 'id_archivo' => $result['id_archivo']]);
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
