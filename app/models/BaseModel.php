<?php
class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey;

    public function __construct($table, $primaryKey = null) {
        $this->db = Database::getInstance();
        $this->table = $table;
        $this->primaryKey = $primaryKey ?: 'id_' . $table;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll($orderBy = 'fecha_creación DESC') {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function getWhere($where, $params = [], $orderBy = 'fecha_creación DESC') {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy}");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getOneWhere($where, $params = []) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} LIMIT 1");
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        return $stmt->execute(array_values($data));
    }

    public function update($id, $data) {
        $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?");
        $values = array_values($data);
        $values[] = $id;
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function count($where = '1=1', $params = []) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function exists($where, $params = []) {
        return $this->count($where, $params) > 0;
    }

    public function paginate($page = 1, $perPage = 20, $where = '1=1', $params = [], $orderBy = 'fecha_creación DESC') {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($where, $params);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT ? OFFSET ?");
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
        ];
    }

    public function raw($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
