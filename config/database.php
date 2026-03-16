<?php
/**
 * CONFIGURACIÓN DE BASE DE DATOS
 * Sistema TCAD Células - Control de Reuniones Celulares
 */

// Credenciales de Base de Datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tcad_celulas');
define('DB_PORT', 3306);

// Configuración de conexión PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4');

// Opciones PDO por defecto
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Crear conexión
try {
    $conexion = new PDO(DB_DSN, DB_USER, DB_PASS, $pdoOptions);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo conectar a la base de datos. Contacte al administrador.']);
    }
    exit(1);
}

?>
