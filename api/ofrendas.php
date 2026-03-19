<?php
/**
 * API - OFRENDAS
 * Endpoints: cambiar estado, obtener informe, reconciliar
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

validarSesion();

require_once __DIR__ . '/../controllers/OfrendaController.php';

$ofrendaController = new OfrendaController($conexion);
$accion = $_GET['accion'] ?? null;
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch($accion) {
        case 'listar':
            validarRol(['tesorero','pastor','lider_area','lider_celula']);
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : REGISTROS_POR_PAGINA;
            $filtros = [
                'estado' => $_GET['estado'] ?? null,
                'celula_id' => $_GET['celula_id'] ?? null,
                'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_GET['fecha_fin'] ?? null,
            ];
            $res = $ofrendaController->listar($filtros, $pagina, $limite);
            echo json_encode(array_merge(['exito'=>true], $res));
            break;

        case 'registrar':
            validarRol(['pastor','tesorero','lider_area','lider_celula']);
            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido']);
                break;
            }
            $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $resultado = $ofrendaController->registrar($datos, $_SESSION['usuario_id']);
            echo json_encode($resultado);
            break;

        case 'obtener':
            validarRol(['tesorero','pastor','lider_area','lider_celula']);
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'mensaje'=>'ID requerido']);
                break;
            }
            echo json_encode($ofrendaController->obtener($id));
            break;

        case 'cambiar-estado':
            validarRol(['tesorero', 'pastor','lider_area','lider_celula']);
            
            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido']);
                break;
            }

            $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $ofrenda_id = $datos['ofrenda_id'] ?? null;
            $nuevo_estado = $datos['nuevo_estado'] ?? null;

            if (!$ofrenda_id || !$nuevo_estado) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'mensaje'=>'Datos incompletos']);
                break;
            }

            $resultado = $ofrendaController->cambiarEstado(
                $ofrenda_id,
                $nuevo_estado,
                $_SESSION['usuario_id']
            );
            
            echo json_encode($resultado);
            break;

        case 'historial':
            validarRol(['tesorero','pastor','lider_area','lider_celula']);
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'mensaje'=>'ID requerido']);
                break;
            }
            echo json_encode($ofrendaController->historialDetallado($id));
            break;

        case 'eliminar':
            validarRol(['tesorero','pastor']);
            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido']);
                break;
            }
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'mensaje'=>'ID requerido']);
                break;
            }
            $ok = $ofrendaController->eliminar((int)$id);
            echo json_encode(['exito'=>$ok ? true : false, 'mensaje'=>$ok ? 'Eliminada' : 'No se pudo eliminar']);
            break;
            
        case 'informe-pendientes':
            validarRol(['tesorero', 'pastor','lider_area','lider_celula']);
            
            $resultado = $ofrendaController->obtenerInformeOfrendasPendientes();
            echo json_encode(['exito' => true, 'data' => $resultado]);
            break;
            
        case 'reporte-periodo':
            validarRol(['tesorero', 'pastor']);
            
            $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
            
            $resultado = $ofrendaController->obtenerReportePeriodo($fecha_inicio, $fecha_fin);
            echo json_encode($resultado);
            break;
            
        case 'dashboard-tesorero':
            validarRol(['tesorero', 'pastor']);
            
            $resultado = $ofrendaController->obtenerDashboardTesorero();
            echo json_encode(['exito' => true, 'data' => $resultado]);
            break;
            
        case 'reconciliar':
            validarRol(['tesorero', 'pastor']);
            
            if ($metodo == 'POST') {
                $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                
                $resultado = $ofrendaController->reconciliar(
                    $datos['ofrenda_id'],
                    $datos['monto_recibido'],
                    $_SESSION['usuario_id'],
                    $datos['notas'] ?? ''
                );
                
                echo json_encode($resultado);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no especificada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'error' => $e->getMessage()]);
}
?>
