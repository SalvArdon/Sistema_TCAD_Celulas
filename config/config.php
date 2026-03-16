<?php
/**
 * CONFIGURACIÓN GENERAL DEL SISTEMA
 * Sistema TCAD Células
 */

session_start();

// INFORMACIÓN DEL SISTEMA
define('SISTEMA_NOMBRE', 'Sistema TCAD Células');
define('SISTEMA_VERSION', '1.0.0');
define('SISTEMA_AUTOR', 'Desarrollo Iglesia');

// RUTAS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', "{$protocol}://{$host}/portafolio/Sistema_TCAD_Celulas/");
define('BASE_PATH', __DIR__ . '/../');

// CONFIGURACIÓN DE SEGURIDAD
define('ENCRIPTACION_ALGORITMO', 'argon2id');
define('ENCRIPTACION_OPCIONES', ['memory_cost' => 1024 * 64,  'time_cost' => 4, 'threads' => 3]);
define('SESSION_TIMEOUT', 3600); // 1 hora

// VALIDAR SESIÓN - Verificar timeout y existencia
if (isset($_SESSION['usuario_id'])) {
    if (!isset($_SESSION['ultimo_acceso'])) {
        $_SESSION['ultimo_acceso'] = time();
    } elseif ((time() - $_SESSION['ultimo_acceso']) > SESSION_TIMEOUT) {
        // Sesión expirada - destruir
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    } else {
        // Actualizar último acceso
        $_SESSION['ultimo_acceso'] = time();
    }
}

define('MAX_LOGIN_INTENTOS', 5);
define('BLOQUEO_MINUTOS', 15);

// CONFIGURACIÓN DE ARCHIVOS
define('MAX_ARCHIVO_SIZE', 10 * 1024 * 1024); // 10MB
define('TIPOS_ARCHIVO_PERMITIDOS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm']);
define('RUTA_UPLOADS', BASE_PATH . 'uploads/');

// CONFIGURACIÓN DE PAGINACIÓN
define('REGISTROS_POR_PAGINA', 15);

// PARÁMETROS DE NEGOCIO
define('DIAS_ALERTA_SIN_REPORTE', 14); // Alertar si célula no reporta en 2 semanas
define('MONEDA', 'USD');

// ZONAS HORARIAS
date_default_timezone_set('America/El_Salvador');

// AREAS DE SERVICIO POR DEFECTO
$AREAS_SERVICIO_PREDETERMINADAS = [
    'Jóvenes',
    'Multimedia',
    'Matrimonios',
    'Mujeres',
    'Tráfico',
    'Protocolo',
    'Hombres',
    'Células Familiares'
];

// ROLES DEL SISTEMA
$ROLES_SISTEMA = [
    'pastor' => 'Pastor',
    'lider_area' => 'Líder de Área',
    'lider_celula' => 'Líder de Célula',
    'tesorero' => 'Tesorero',
    'servidor' => 'Servidor'
];

// ESTADOS DE OFRENDA
$ESTADOS_OFRENDA = [
    'reportada' => 'Reportada por líder',
    'recibida' => 'Recibida en iglesia',
    'conciliada' => 'Conciliada'
];

// ESTADOS DE CÉLULA
$ESTADOS_CELULA = [
    'activa' => 'Activa',
    'inactiva' => 'Inactiva',
    'pausada' => 'Pausada'
];

// INCLUIR BASE DE DATOS
require_once 'database.php';

// FUNCIÓN PARA REDIRECCIONAR A LOGIN SI NO AUTENTICADO
function validarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    
    // Validar timeout de sesión
    if (isset($_SESSION['ultimo_acceso'])) {
        if ((time() - $_SESSION['ultimo_acceso']) > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: ' . BASE_URL . 'login.php?sesion=expirada');
            exit();
        }
    }
    
    $_SESSION['ultimo_acceso'] = time();
}

// FUNCIÓN PARA VALIDAR ROL
function validarRol($roles_permitidos = []) {
    validarSesion();
    
    // Preferir nombre de rol (slug) almacenado en sesión
    $rol_usuario = $_SESSION['rol_nombre'] ?? ($_SESSION['rol'] ?? null);
    
    if (!in_array($rol_usuario, $roles_permitidos)) {
        http_response_code(403);
        die('Acceso Denegado: No tienes permiso para esta acción.');
    }
}

?>
