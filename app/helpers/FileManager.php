<?php
class FileManager {

    const MAX_FILE_SIZE = 20971520;
    const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/plain' => 'txt',
        'text/csv'  => 'csv',
        'application/zip' => 'zip',
    ];

    private static function getBasePath() {
        return dirname(__DIR__, 2) . '/storage/archivos/';
    }

    public static function upload($file, $entidadTipo = null, $entidadId = null, $subdirectorio = 'general', $idUsuarioSubio = null) {
        $error = self::validateFile($file);
        if ($error) return ['success' => false, 'error' => $error];

        $db = Database::getInstance();
        $idArchivo = UUIDGenerator::generate();
        $ext = self::ALLOWED_TYPES[$file['type']];
        $nombreArchivo = $idArchivo . '.' . $ext;
        $relativePath = trim($subdirectorio, '/') . '/' . $nombreArchivo;
        $fullDir = self::getBasePath() . trim($subdirectorio, '/');
        $fullPath = $fullDir . '/' . $nombreArchivo;

        if (!is_dir($fullDir)) mkdir($fullDir, 0755, true);

        $hash = hash_file('sha256', $file['tmp_name']);

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['success' => false, 'error' => 'Error al mover el archivo al almacenamiento'];
        }

        $stmt = $db->prepare("INSERT INTO archivos (id_archivo, nombre_original, nombre_archivo, mime_type, tamaño, extensión, ruta, hash_sha256, entidad_tipo, entidad_id, subdirectorio, id_usuario_subio, fecha_subida) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $idArchivo,
            $file['name'],
            $nombreArchivo,
            $file['type'],
            $file['size'],
            $ext,
            $relativePath,
            $hash,
            $entidadTipo,
            $entidadId,
            $subdirectorio,
            $idUsuarioSubio
        ]);

        return [
            'success' => true,
            'id_archivo' => $idArchivo,
            'nombre_original' => $file['name'],
            'tamaño' => $file['size'],
            'extensión' => $ext,
        ];
    }

    public static function delete($idArchivo) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM archivos WHERE id_archivo = ?");
        $stmt->execute([$idArchivo]);
        $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$archivo) return ['success' => false, 'error' => 'Archivo no encontrado'];

        $fullPath = self::getBasePath() . $archivo['ruta'];
        if (file_exists($fullPath)) unlink($fullPath);

        $stmt = $db->prepare("DELETE FROM archivos WHERE id_archivo = ?");
        $stmt->execute([$idArchivo]);
        return ['success' => true];
    }

    public static function get($idArchivo) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM archivos WHERE id_archivo = ?");
        $stmt->execute([$idArchivo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByEntity($entidadTipo, $entidadId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM archivos WHERE entidad_tipo = ? AND entidad_id = ? ORDER BY fecha_subida DESC");
        $stmt->execute([$entidadTipo, $entidadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function serve($idArchivo, $disposition = 'inline') {
        $archivo = self::get($idArchivo);
        if (!$archivo) {
            http_response_code(404);
            echo 'Archivo no encontrado';
            exit;
        }

        $fullPath = self::getBasePath() . $archivo['ruta'];
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'Archivo no encontrado en disco';
            exit;
        }

        header('Content-Type: ' . $archivo['mime_type']);
        header('Content-Length: ' . $archivo['tamaño']);
        header('Content-Disposition: ' . $disposition . '; filename="' . $archivo['nombre_original'] . '"');
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public static function validateFile($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo fue subido parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'No se encuentra el directorio temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            ];
            $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            return $errors[$code] ?? 'Error desconocido al subir archivo';
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return 'El archivo excede el tamaño máximo de ' . (self::MAX_FILE_SIZE / 1048576) . ' MB';
        }

        if (!isset(self::ALLOWED_TYPES[$file['type']])) {
            return 'Tipo de archivo no permitido: ' . $file['type'];
        }

        return null;
    }

    public static function getEntityTypes() {
        return [
            'socio'      => 'Socios',
            'credito'    => 'Créditos',
            'inversion'  => 'Inversiones',
            'multa'      => 'Multas',
            'sesion'     => 'Sesiones',
            'general'    => 'General',
        ];
    }
}
