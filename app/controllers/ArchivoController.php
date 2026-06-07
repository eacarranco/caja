<?php
class ArchivoController extends BaseController {

    public function ver($id) {
        $this->requireAuth();
        FileManager::serve($id, 'inline');
    }

    public function descargar($id) {
        $this->requireAuth();
        FileManager::serve($id, 'attachment');
    }

    public function listarPorEntidad($tipo, $id) {
        $this->requireAuth();
        $archivos = FileManager::getByEntity($tipo, $id);
        $this->json($archivos);
    }

    public function eliminar() {
        $this->requireAuth();
        $this->validateCSRF();
        $idArchivo = $_POST['id_archivo'] ?? '';
        if (empty($idArchivo)) {
            $_SESSION['flash_error'] = 'ID de archivo no proporcionado';
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
        $result = FileManager::delete($idArchivo);
        if ($result['success']) {
            $_SESSION['flash_success'] = 'Archivo eliminado correctamente';
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
