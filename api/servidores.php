<?php
/**
 * API - SERVIDORES
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
validarSesion();

require_once __DIR__ . '/../controllers/ServidoresController.php';

$controller = new ServidoresController($conexion);
$accion = $_GET['accion'] ?? null;
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($accion) {
        case 'listar':
            validarRol(['pastor','lider_area']);
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : REGISTROS_POR_PAGINA;
            $filtros = [
                'q' => $_GET['q'] ?? null,
                'area_servicio_id' => $_GET['area_servicio_id'] ?? null
            ];
            $res = $controller->listar($filtros, $pagina, $limite);
            echo json_encode(array_merge(['exito'=>true], $res));
            break;

        case 'obtener':
            validarRol(['pastor','lider_area']);
            $id = $_GET['id'] ?? null;
            if (!$id) { http_response_code(400); echo json_encode(['exito'=>false,'mensaje'=>'ID requerido']); break; }
            echo json_encode($controller->obtener($id));
            break;

        case 'crear':
            validarRol(['pastor','lider_area']);
            if ($metodo !== 'POST') { http_response_code(405); echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido']); break; }
            $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            echo json_encode($controller->crear($datos));
            break;

        case 'actualizar':
            validarRol(['pastor','lider_area']);
            if (!in_array($metodo, ['POST','PUT','PATCH'])) { http_response_code(405); echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido']); break; }
            $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id = $_GET['id'] ?? ($datos['id'] ?? null);
            if (!$id) { http_response_code(400); echo json_encode(['exito'=>false,'mensaje'=>'ID requerido']); break; }
            echo json_encode($controller->actualizar($id, $datos));
            break;

        case 'eliminar':
            validarRol(['pastor','lider_area']);
            if ($metodo !== 'DELETE' && $metodo !== 'POST') { http_response_code(405); echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido']); break; }
            $id = $_GET['id'] ?? ($_POST['id'] ?? null);
            if (!$id) { http_response_code(400); echo json_encode(['exito'=>false,'mensaje'=>'ID requerido']); break; }
            echo json_encode($controller->eliminar($id));
            break;

        default:
            http_response_code(400);
            echo json_encode(['exito'=>false,'error'=>'Acción no especificada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito'=>false,'error'=>$e->getMessage()]);
}
