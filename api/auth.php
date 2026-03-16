<?php
/**
 * API - AUTENTICACIÓN
 * Endpoints: login, logout, validar sesión
 */

// LEER JSON ANTES DE INCLUIR CONFIG (que llama a session_start())
$input = file_get_contents('php://input');
$datos = json_decode($input, true) ?? [];

// Si viene del POST tradicional
if (empty($datos)) {
    $datos = $_POST;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../controllers/AuthController.php';
    
    $authController = new AuthController($conexion);
    $metodo = $_SERVER['REQUEST_METHOD'];
    
    // Si es GET, usar parámetros de URL
    if ($metodo === 'GET') {
        $datos = $_GET;
    }
    
    $accion = $datos['accion'] ?? null;
    
    if (!$accion) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Accion no especificada']);
        exit;
    }
    
    switch($accion) {
        case 'login':
            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito' => false, 'mensaje' => 'Solo se permite POST']);
                exit;
            }
            
            $correo = trim($datos['correo'] ?? '');
            $password = $datos['password'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            if (empty($correo) || empty($password)) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'mensaje' => 'Correo y contraseña son requeridos']);
                exit;
            }
            
            $resultado = $authController->login($correo, $password, $ip);
            
            if ($resultado['exito']) {
                http_response_code(200);
            } else {
                http_response_code(401);
            }
            
            echo json_encode($resultado);
            break;
            
        case 'logout':
            if (!isset($_SESSION['usuario_id'])) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'mensaje' => 'No hay sesión activa']);
                exit;
            }
            
            $resultado = $authController->logout($_SESSION['usuario_id'] ?? 0);
            
            // Eliminar todas las variables de sesión
            $_SESSION = [];
            
            // Destruir la cookie de sesión
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Enviar respuesta
            $resultado['redirect'] = BASE_URL;
            echo json_encode($resultado);
            break;
            
        case 'validar':
            $valido = $authController->validarSesion();
            echo json_encode(['sesion_activa' => $valido]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Accion no reconocida']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error en el servidor',
        'detalle' => $e->getMessage()
    ]);
}
?>
