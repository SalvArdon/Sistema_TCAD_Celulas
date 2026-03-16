<?php
/**
 * ARCHIVO PRINCIPAL - INDEX.PHP
 * Router del sistema - Orquestra las solicitudes
 */

require_once __DIR__ . '/config/config.php';

// Clase Router simple
class Router {
    private $ruta;
    private $metodo;
    private $parametros = [];
    
    public function __construct() {
        // Obtener la ruta desde REQUEST_URI
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover la ruta base del proyecto
        $base = '/portafolio/Sistema_TCAD_Celulas';
        if (strpos($request_uri, $base) === 0) {
            $this->ruta = substr($request_uri, strlen($base));
        } else {
            $this->ruta = $request_uri;
        }
        
        // Limpiar la ruta
        $this->ruta = trim($this->ruta, '/');
        $this->metodo = $_SERVER['REQUEST_METHOD'];
    }
    
    public function procesar() {
        // Si está vacío, ir a dashboard o login
        if (empty($this->ruta) || $this->ruta == 'index.php') {
            if (!isset($_SESSION['usuario_id'])) {
                include __DIR__ . '/views/login.php';
            } else {
                include __DIR__ . '/views/dashboard.php';
            }
            return;
        }
        
        // Permitir acceso a login.php sin sesión
        if ($this->ruta == 'login.php' || $this->ruta == 'login') {
            // Si ya está logueado, redirige al dashboard
            if (isset($_SESSION['usuario_id'])) {
                header('Location: ' . BASE_URL . 'dashboard');
                exit;
            }
            include __DIR__ . '/views/login.php';
            return;
        }
        
        // TODAS las demás páginas requieren autenticación
        if (!isset($_SESSION['usuario_id'])) {
            // Redirige al login sin permitir acceso directo
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Dividir la ruta en partes
        $partes = explode('/', $this->ruta);
        $pagina = $partes[0] ?? '';
        
        // Validar que sea un archivo PHP seguro
        $archivo_pagina = __DIR__ . '/views/' . $pagina . '.php';
        
        // Si la página existe, cargarla; si no, mostrar construcción
        if (file_exists($archivo_pagina)) {
            include $archivo_pagina;
        } else {
            // Mostrar página de construcción para nuevos módulos
            // Pasar variables para construccion.php
            include __DIR__ . '/views/construccion.php';
        }
    }
}

// API REST endpoints
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    header('Content-Type: application/json');
    
    // Extraer recurso y acción
    $uri_partes = explode('/', trim($_GET['ruta'] ?? '', '/'));
    $recurso = $uri_partes[0] ?? '';
    $accion = $uri_partes[1] ?? '';
    
    switch($recurso) {
        case 'auth':
            include __DIR__ . '/api/auth.php';
            break;
        case 'reuniones':
            include __DIR__ . '/api/reuniones.php';
            break;
        case 'ofrendas':
            include __DIR__ . '/api/ofrendas.php';
            break;
        case 'celulas':
            include __DIR__ . '/api/celulas.php';
            break;
        case 'usuarios':
            include __DIR__ . '/api/usuarios.php';
            break;
        case 'dashboard':
            include __DIR__ . '/api/dashboard.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Recurso no encontrado']);
    }
} else {
    // Ruteo de página web
    $router = new Router();
    $router->procesar();
}
?>
