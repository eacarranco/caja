<?php
class Validator {
    private $errors = [];

    public function required($field, $label, $value) {
        if (empty(trim($value))) {
            $this->errors[$field] = "$label es obligatorio";
        }
        return $this;
    }

    public function email($field, $label, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label no tiene un formato válido";
        }
        return $this;
    }

    public function cedula($field, $label, $value) {
        if (!empty($value) && !CedulaEcuador::validar($value)) {
            $this->errors[$field] = "$label no es válida";
        }
        return $this;
    }

    public function minLength($field, $label, $value, $min) {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = "$label debe tener al menos $min caracteres";
        }
        return $this;
    }

    public function maxLength($field, $label, $value, $max) {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = "$label no debe exceder $max caracteres";
        }
        return $this;
    }

    public function numeric($field, $label, $value) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "$label debe ser un valor numérico";
        }
        return $this;
    }

    public function minValue($field, $label, $value, $min) {
        if (!empty($value) && is_numeric($value) && $value < $min) {
            $this->errors[$field] = "$label debe ser mínimo $min";
        }
        return $this;
    }

    public function maxValue($field, $label, $value, $max) {
        if (!empty($value) && is_numeric($value) && $value > $max) {
            $this->errors[$field] = "$label no debe exceder $max";
        }
        return $this;
    }

    public function inList($field, $label, $value, $list) {
        if (!empty($value) && !in_array($value, $list)) {
            $this->errors[$field] = "$label contiene un valor no permitido";
        }
        return $this;
    }

    public function date($field, $label, $value) {
        if (empty($value)) return $this;
        $parts = explode('-', $value);
        if (count($parts) !== 3 || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $this->errors[$field] = "$label no es una fecha válida";
        } elseif ((int)$parts[0] < 1900 || (int)$parts[0] > date('Y')) {
            $this->errors[$field] = "$label tiene un año fuera de rango";
        }
        return $this;
    }

    public function unique($field, $label, $value, $table, $column, $excludeId = null) {
        if (empty($value)) return $this;
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $params = [$value];
        if ($excludeId !== null) {
            $sql .= " AND id_$table != ?";
            $params[] = $excludeId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $this->errors[$field] = "$label ya existe en el sistema";
        }
        return $this;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return reset($this->errors);
    }

    public function clear() {
        $this->errors = [];
        return $this;
    }
}
