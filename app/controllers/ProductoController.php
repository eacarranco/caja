<?php
class ProductoController extends BaseController {

    private $tipos = ['crédito' => 'Crédito', 'inversión' => 'Inversión'];
    private $metodos = ['simple' => 'Simple', 'francés' => 'Francés', 'alemán' => 'Alemán'];

    public function listar() {
        $this->requirePermission('param.financiero');
        $stmt = $this->db->query("SELECT * FROM productos_financieros ORDER BY tipo, nombre");
        $productos = $stmt->fetchAll();
        $this->render('productos/listar', [
            'titulo' => 'Productos financieros',
            'productos' => $productos,
            'tipos' => $this->tipos,
            'metodos' => $this->metodos,
        ]);
    }

    public function registrar() {
        $this->requirePermission('producto.crear');
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $data = $this->sanitizar($_POST);
            $errors = $this->validar($data);

            if (empty($errors)) {
                $id = UUIDGenerator::generar();
                $stmt = $this->db->prepare("INSERT INTO productos_financieros
                    (id_producto, nombre, tipo, tasa_interés_anual, método_interés,
                     plazo_mín_meses, plazo_máx_meses, monto_mín, monto_máx,
                     requiere_garante, penalidad_retiro_anticipado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id, $data['nombre'], $data['tipo'], $data['tasa_interés_anual'],
                    $data['método_interés'], $data['plazo_mín_meses'], $data['plazo_máx_meses'],
                    $data['monto_mín'], $data['monto_máx'],
                    !empty($data['requiere_garante']) ? 1 : 0,
                    $data['penalidad_retiro_anticipado'] ?? 0
                ]);
                $this->redirect('/producto/listar');
            }
        }

        $this->render('productos/form', [
            'titulo' => 'Nuevo producto',
            'errors' => $errors,
            'data' => $_POST,
            'editando' => false,
            'tipos' => $this->tipos,
            'metodos' => $this->metodos,
        ]);
    }

    public function editar($id) {
        $this->requirePermission('producto.editar');
        $stmt = $this->db->prepare("SELECT * FROM productos_financieros WHERE id_producto = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        if (!$producto) $this->redirect('/producto/listar');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $data = $this->sanitizar($_POST);
            $errors = $this->validar($data, $id);

            if (empty($errors)) {
                $stmt = $this->db->prepare("UPDATE productos_financieros SET
                    nombre = ?, tipo = ?, tasa_interés_anual = ?, método_interés = ?,
                    plazo_mín_meses = ?, plazo_máx_meses = ?, monto_mín = ?, monto_máx = ?,
                    requiere_garante = ?, penalidad_retiro_anticipado = ?
                    WHERE id_producto = ?");
                $stmt->execute([
                    $data['nombre'], $data['tipo'], $data['tasa_interés_anual'],
                    $data['método_interés'], $data['plazo_mín_meses'], $data['plazo_máx_meses'],
                    $data['monto_mín'], $data['monto_máx'],
                    !empty($data['requiere_garante']) ? 1 : 0,
                    $data['penalidad_retiro_anticipado'] ?? 0, $id
                ]);
                $this->redirect('/producto/listar');
            }
        }

        $this->render('productos/form', [
            'titulo' => 'Editar producto: ' . $producto['nombre'],
            'errors' => $errors,
            'data' => $producto,
            'editando' => true,
            'tipos' => $this->tipos,
            'metodos' => $this->metodos,
        ]);
    }

    public function toggleEstado($id) {
        $this->requirePermission('producto.activar');
        $stmt = $this->db->prepare("SELECT activo FROM productos_financieros WHERE id_producto = ?");
        $stmt->execute([$id]);
        $actual = $stmt->fetchColumn();
        if ($actual === false) $this->json(['error' => 'No encontrado'], 404);
        $nuevo = $actual ? 0 : 1;
        $this->db->prepare("UPDATE productos_financieros SET activo = ? WHERE id_producto = ?")->execute([$nuevo, $id]);
        $this->json(['mensaje' => 'Estado actualizado', 'activo' => $nuevo]);
    }

    private function sanitizar($post) {
        return [
            'nombre' => trim($post['nombre'] ?? ''),
            'tipo' => $post['tipo'] ?? '',
            'tasa_interés_anual' => str_replace(',', '.', $post['tasa_interés_anual'] ?? '0'),
            'método_interés' => $post['método_interés'] ?? 'simple',
            'plazo_mín_meses' => intval($post['plazo_mín_meses'] ?? 1),
            'plazo_máx_meses' => intval($post['plazo_máx_meses'] ?? 12),
            'monto_mín' => str_replace(',', '.', $post['monto_mín'] ?? '0'),
            'monto_máx' => str_replace(',', '.', $post['monto_máx'] ?? '0'),
            'requiere_garante' => $post['requiere_garante'] ?? '',
            'penalidad_retiro_anticipado' => str_replace(',', '.', $post['penalidad_retiro_anticipado'] ?? '0'),
        ];
    }

    private function validar($d, $id = null) {
        $errors = [];
        if (empty($d['nombre'])) $errors['nombre'] = 'El nombre es obligatorio';
        if (!isset($this->tipos[$d['tipo']])) $errors['tipo'] = 'Tipo inválido';
        if (!is_numeric($d['tasa_interés_anual']) || $d['tasa_interés_anual'] < 0 || $d['tasa_interés_anual'] > 100)
            $errors['tasa_interés_anual'] = 'Tasa inválida (0-100%)';
        if (!isset($this->metodos[$d['método_interés']])) $errors['método_interés'] = 'Método inválido';
        if ($d['plazo_mín_meses'] < 1) $errors['plazo_mín_meses'] = 'Plazo mínimo debe ser ≥ 1';
        if ($d['plazo_máx_meses'] < $d['plazo_mín_meses']) $errors['plazo_máx_meses'] = 'Plazo máximo debe ser ≥ mínimo';
        if (!is_numeric($d['monto_mín']) || $d['monto_mín'] < 0) $errors['monto_mín'] = 'Monto mínimo inválido';
        if (!is_numeric($d['monto_máx']) || $d['monto_máx'] <= $d['monto_mín']) $errors['monto_máx'] = 'Monto máximo debe ser > mínimo';
        return $errors;
    }
}
