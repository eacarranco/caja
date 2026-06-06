<?php
class ParametroController extends BaseController {

    public function listar() {
        $this->requirePermission('param.financiero');
        $stmt = $this->db->query("SELECT * FROM parámetros ORDER BY módulo, código");
        $params = $stmt->fetchAll();

        $this->render('parametros/listar', [
            'titulo' => 'Parámetros del sistema',
            'params' => $params,
        ]);
    }

    public function editar($id) {
        $this->requirePermission('param.financiero');
        $stmt = $this->db->prepare("SELECT * FROM parámetros WHERE id_parámetro = ?");
        $stmt->execute([$id]);
        $param = $stmt->fetch();
        if (!$param) $this->redirect('/parametro/listar');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            if (!$param['editable']) {
                $this->render('parametros/editar', [
                    'titulo' => 'Editar parámetro',
                    'param' => $param,
                    'error' => 'Este parámetro no es editable',
                ]);
                return;
            }
            $valor = $_POST['valor'] ?? '';
            $stmt = $this->db->prepare("UPDATE parámetros SET valor = ? WHERE id_parámetro = ?");
            $stmt->execute([$valor, $id]);
            $this->redirect('/parametro/listar');
        }

        $this->render('parametros/editar', [
            'titulo' => 'Editar parámetro',
            'param' => $param,
        ]);
    }

    public function modulo($modulo) {
        $this->requirePermission('param.financiero');
        $stmt = $this->db->prepare("SELECT * FROM parámetros WHERE módulo = ? ORDER BY código");
        $stmt->execute([$modulo]);
        $params = $stmt->fetchAll();

        $this->render('parametros/listar', [
            'titulo' => "Parámetros - $modulo",
            'params' => $params,
        ]);
    }
}
