<?php
/**
 * API - DASHBOARD
 * Retorna datos para el dashboard con gráficas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../controllers/DashboardController.php';
    
    // Validar sesión
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }
    
    $dashboard = new DashboardController($conexion);
    
    // Tipo de dato solicitado
    $tipo = $_GET['tipo'] ?? 'todo';
    
    $respuesta = [];
    
    switch($tipo) {
        case 'estadisticas':
            $respuesta = $dashboard->obtenerEstadisticas();
            break;
            
        case 'celulas-estado':
            $respuesta = ['datos' => $dashboard->obtenerCelulasEstado()];
            break;
            
        case 'asistencia-meses':
            $respuesta = ['datos' => $dashboard->obtenerAsistenciaMeses()];
            break;
            
        case 'ofrendas-meses':
            $respuesta = ['datos' => $dashboard->obtenerOfrendasMeses()];
            break;
            
        case 'servidores-area':
            $respuesta = ['datos' => $dashboard->obtenerServidoresPorArea()];
            break;
            
        case 'ultimas-reuniones':
            $respuesta = ['datos' => $dashboard->obtenerUltimasReuniones(10)];
            break;
            
        case 'todo':
        default:
            $respuesta = [
                'estadisticas' => $dashboard->obtenerEstadisticas(),
                'celulas_estado' => $dashboard->obtenerCelulasEstado(),
                'asistencia_meses' => $dashboard->obtenerAsistenciaMeses(),
                'ofrendas_meses' => $dashboard->obtenerOfrendasMeses(),
                'servidores_area' => $dashboard->obtenerServidoresPorArea(),
                'ultimas_reuniones' => $dashboard->obtenerUltimasReuniones(5)
            ];
            break;
    }
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'mensaje' => $e->getMessage()
    ]);
}
?>
