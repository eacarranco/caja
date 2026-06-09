<?php
class ProductoController extends BaseController {

    private $tipos = ['credito' => 'Crédito', 'inversion' => 'Inversión'];
    private $metodos = ['simple' => 'Simple', 'frances' => 'Francés', 'aleman' => 'Alemán'];

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
                    (id_producto, nombre, tipo, activo, tasa_interes_anual, metodo_interes,
                     plazo_min_meses, plazo_max_meses, monto_min, monto_max,
                     requiere_garante, penalidad_retiro_anticipado,
                     condiciones_html, min_permanencia_meses, min_ahorro, min_ahorro_unidad,
                     es_emergente, monto_max_emergente, requiere_documento_firmado,
                     min_destino_caracteres, min_permanencia_valor, min_permanencia_unidad)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id, $data['nombre'], $data['tipo'], $data['activo'],
                    $data['tasa_interes_anual'], $data['metodo_interes'],
                    $data['plazo_min_meses'], $data['plazo_max_meses'],
                    $data['monto_min'], $data['monto_max'],
                    $data['requiere_garante'], $data['penalidad_retiro_anticipado'],
                    $data['condiciones_html'], $data['min_permanencia_meses'],
                    $data['min_ahorro'], $data['min_ahorro_unidad'],
                    $data['es_emergente'], $data['monto_max_emergente'], $data['requiere_documento_firmado'],
                    $data['min_destino_caracteres'], $data['min_permanencia_valor'],
                    $data['min_permanencia_unidad'],
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
            $errors = $this->validar($data);

            if (empty($errors)) {
                $stmt = $this->db->prepare("UPDATE productos_financieros SET
                    nombre = ?, tipo = ?, activo = ?, tasa_interes_anual = ?, metodo_interes = ?,
                    plazo_min_meses = ?, plazo_max_meses = ?, monto_min = ?, monto_max = ?,
                    requiere_garante = ?, penalidad_retiro_anticipado = ?,
                    condiciones_html = ?, min_permanencia_meses = ?, min_ahorro = ?, min_ahorro_unidad = ?,
                    es_emergente = ?, monto_max_emergente = ?, requiere_documento_firmado = ?,
                    min_destino_caracteres = ?, min_permanencia_valor = ?, min_permanencia_unidad = ?
                    WHERE id_producto = ?");
                $stmt->execute([
                    $data['nombre'], $data['tipo'], $data['activo'],
                    $data['tasa_interes_anual'], $data['metodo_interes'],
                    $data['plazo_min_meses'], $data['plazo_max_meses'],
                    $data['monto_min'], $data['monto_max'],
                    $data['requiere_garante'], $data['penalidad_retiro_anticipado'],
                    $data['condiciones_html'], $data['min_permanencia_meses'],
                    $data['min_ahorro'], $data['min_ahorro_unidad'],
                    $data['es_emergente'], $data['monto_max_emergente'], $data['requiere_documento_firmado'],
                    $data['min_destino_caracteres'], $data['min_permanencia_valor'],
                    $data['min_permanencia_unidad'],
                    $id,
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
        $tipo = $post['tipo'] ?? 'credito';
        $esCredito = $tipo === 'credito';

        $minDestCar = !empty($post['usa_min_destino_caracteres']) ? intval($post['min_destino_caracteres'] ?? 0) : 0;
        $minPermVal = !empty($post['usa_min_permanencia']) ? intval($post['min_permanencia_valor'] ?? 0) : 0;
        $minPermUnidad = $post['min_permanencia_unidad'] ?? 'meses';
        $usaAhorro = !empty($post['usa_min_ahorro']);
        $minAhorro = $usaAhorro ? str_replace(',', '.', $post['min_ahorro'] ?? '0') : 0;
        $minAhorroUnidad = $usaAhorro ? ($post['min_ahorro_unidad'] ?? 'dolares') : 'dolares';

        return [
            'nombre' => trim($post['nombre'] ?? ''),
            'tipo' => $tipo,
            'activo' => !empty($post['activo']) ? 1 : 0,
            'tasa_interes_anual' => str_replace(',', '.', $post['tasa_interes_anual'] ?? '0'),
            'metodo_interes' => $esCredito ? ($post['metodo_interes'] ?? 'simple') : 'simple',
            'plazo_min_meses' => intval($post['plazo_min_meses'] ?? 1),
            'plazo_max_meses' => intval($post['plazo_max_meses'] ?? 12),
            'monto_min' => str_replace(',', '.', $post['monto_min'] ?? '0'),
            'monto_max' => str_replace(',', '.', $post['monto_max'] ?? '0'),
            'requiere_garante' => $esCredito ? (!empty($post['requiere_garante']) ? 1 : 0) : 0,
            'penalidad_retiro_anticipado' => $esCredito ? 0 : str_replace(',', '.', $post['penalidad_retiro_anticipado'] ?? '0'),
            'condiciones_html' => $post['condiciones_html'] ?? '',
            'min_permanencia_meses' => $esCredito ? 0 : intval($post['min_permanencia_meses'] ?? 0),
            'min_ahorro' => $esCredito ? floatval($minAhorro) : str_replace(',', '.', $post['min_ahorro_inv'] ?? '0'),
            'min_ahorro_unidad' => $esCredito ? $minAhorroUnidad : 'dolares',
            'es_emergente' => $esCredito ? (!empty($post['es_emergente']) ? 1 : 0) : 0,
            'monto_max_emergente' => $esCredito ? str_replace(',', '.', $post['monto_max_emergente'] ?? '0') : 0,
            'requiere_documento_firmado' => $esCredito ? (!empty($post['requiere_documento_firmado']) ? 1 : 0) : 1,
            'min_destino_caracteres' => $esCredito ? $minDestCar : 0,
            'min_permanencia_valor' => $esCredito ? $minPermVal : 0,
            'min_permanencia_unidad' => $esCredito ? $minPermUnidad : 'meses',
        ];
    }

    private function validar($d) {
        $errors = [];
        $esCredito = $d['tipo'] === 'credito';

        if (empty($d['nombre'])) $errors['nombre'] = 'El nombre es obligatorio';
        if (!isset($this->tipos[$d['tipo']])) $errors['tipo'] = 'Tipo inválido';
        if (!is_numeric($d['tasa_interes_anual']) || $d['tasa_interes_anual'] < 0 || $d['tasa_interes_anual'] > 100)
            $errors['tasa_interes_anual'] = 'Tasa inválida (0-100%)';
        if ($esCredito && !isset($this->metodos[$d['metodo_interes']]))
            $errors['metodo_interes'] = 'Método inválido';
        if ($d['plazo_min_meses'] < 1) $errors['plazo_min_meses'] = 'Plazo mínimo debe ser ≥ 1';
        if ($d['plazo_max_meses'] < $d['plazo_min_meses']) $errors['plazo_max_meses'] = 'Plazo máximo debe ser ≥ mínimo';
        if (!is_numeric($d['monto_min']) || $d['monto_min'] < 0) $errors['monto_min'] = 'Monto mínimo inválido';
        if (!is_numeric($d['monto_max']) || $d['monto_max'] <= $d['monto_min']) $errors['monto_max'] = 'Monto máximo debe ser > mínimo';
        if ($d['min_destino_caracteres'] < 0) $errors['min_destino_caracteres'] = 'No puede ser negativo';
        if ($d['min_permanencia_valor'] < 0) $errors['min_permanencia_valor'] = 'No puede ser negativo';
        if ($d['min_ahorro'] < 0) $errors['min_ahorro'] = 'No puede ser negativo';
        return $errors;
    }
}
