<?php
/**
 * API - REUNIONES
 * Endpoints: registrar, obtener, listar
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

validarSesion();

require_once __DIR__ . '/../controllers/ReunionController.php';

$reunionController = new ReunionController($conexion);
$accion = $_GET['accion'] ?? null;
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch($accion) {
        case 'registrar':
            validarRol(['lider_celula', 'lider_area', 'pastor']);
            
            if ($metodo == 'POST') {
                $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                if (empty($datos['celula_id']) || empty($datos['fecha_reunion'])) {
                    http_response_code(400);
                    echo json_encode(['exito'=>false,'error'=>'Célula y fecha son obligatorias']);
                    break;
                }
                
                $resultado = $reunionController->registrarReunion(
                    $datos['celula_id'],
                    $_SESSION['usuario_id'],
                    $datos
                );
                
                echo json_encode($resultado);
            }
            break;
            
        case 'obtener':
            $reunion_id = $_GET['id'] ?? null;
            if (!$reunion_id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de reunión requerido']);
                break;
            }
            
            $sql = "SELECT r.*, c.nombre as celula_nombre, u.nombre_completo as lider_nombre,
                           o.monto as ofrenda_monto, o.estado as ofrenda_estado
                    FROM reuniones r
                    JOIN celulas c ON r.celula_id = c.id
                    JOIN usuarios u ON r.lider_reporta_id = u.id
                    LEFT JOIN ofrendas o ON o.reunion_id = r.id
                    WHERE r.id = :id";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $reunion_id);
            $stmt->execute();
            $reunion = $stmt->fetch();
            
            if ($reunion) {
                echo json_encode(['exito' => true, 'data' => $reunion]);
            } else {
                http_response_code(404);
                echo json_encode(['exito' => false, 'error' => 'Reunión no encontrada']);
            }
            break;
            
        case 'listar':
            validarRol(['pastor','lider_area','lider_celula','tesorero']);
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : REGISTROS_POR_PAGINA;
            $orden = strtoupper($_GET['orden'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

              $filtros = [
                  'celula_id' => $_GET['celula_id'] ?? null,
                  'area_id' => $_GET['area_id'] ?? null,
                  'estado' => $_GET['estado'] ?? null, // pendiente | hoy | realizada | todos/null
                  'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                  'fecha_fin' => $_GET['fecha_fin'] ?? null
              ];

            $respuesta = $reunionController->listar($filtros, $pagina, $limite, $orden);
            echo json_encode(array_merge(['exito' => true], $respuesta));
            break;

        case 'buscar':
            validarRol(['pastor','lider_area','lider_celula','tesorero']);
            $q = $_GET['q'] ?? '';
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
            $limite = max(1, min($limite, 100));
            $sql = "SELECT r.id, r.fecha_reunion, c.nombre AS celula_nombre
                    FROM reuniones r
                    JOIN celulas c ON r.celula_id = c.id
                    WHERE c.nombre LIKE :q1 OR r.fecha_reunion LIKE :q2
                    ORDER BY r.fecha_reunion DESC
                    LIMIT $limite";
            $stmt = $conexion->prepare($sql);
            $like = "%$q%";
            $stmt->bindParam(':q1', $like);
            $stmt->bindParam(':q2', $like);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            echo json_encode(['exito'=>true,'data'=>$rows]);
            break;

        case 'toggle':
            validarRol(['pastor','lider_area','lider_celula']);
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'error'=>'ID requerido']);
                break;
            }
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $realizada = isset($body['realizada']) ? (bool)$body['realizada'] : true;
            $resultado = $reunionController->toggleRealizada($id, $realizada);
            echo json_encode($resultado);
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
