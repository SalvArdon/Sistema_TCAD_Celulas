<?php
/**
 * API - CÉLULAS
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

validarSesion();

require_once __DIR__ . '/../models/Celula.php';
require_once __DIR__ . '/../controllers/CelulasController.php';

$celulaModel = new Celula($conexion);
$celulasController = new CelulasController($conexion);

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? null);
$metodo = $_SERVER['REQUEST_METHOD'];

try {

    switch ($accion) {

        case 'crear':
            validarRol(['pastor','lider_area']);

            if ($metodo !== 'POST') {
                http_response_code(405);
                echo json_encode(['exito'=>false,'error'=>'Método no permitido']);
                exit;
            }

            $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $datos['lider_id'] = $datos['lider_id'] ?? $_SESSION['usuario_id'];

            $resultado = $celulasController->crear($datos, $_SESSION['usuario_id']);
            echo json_encode($resultado);
            break;


        case 'actualizar':

            validarRol(['pastor','lider_area']);

            $id = $_GET['id'] ?? null;
            $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $id = $id ?? ($datos['id'] ?? null);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'error'=>'ID requerido']);
                break;
            }

            $resultado = $celulasController->actualizar($id, $datos);
            echo json_encode($resultado);

            break;


        case 'eliminar':

            validarRol(['pastor','lider_area']);

            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['exito'=>false,'error'=>'ID requerido']);
                break;
            }

            // Baja lógica (no borrar): delega al controlador
            $resultado = $celulasController->eliminar($id);

            echo json_encode($resultado);

            break;



        case 'obtener':

            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error'=>'ID requerido']);
                break;
            }

            $celula = $celulasController->obtener($id);

            if ($celula) {
                echo json_encode([
                    'exito'=>true,
                    'data'=>$celula
                ]);
            } else {

                http_response_code(404);

                echo json_encode([
                    'error'=>'Célula no encontrada'
                ]);
            }

            break;



        case 'listar':

            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : REGISTROS_POR_PAGINA;

            $estado = $_GET['estado'] ?? null;

            /**
             * Si viene vacío o no se envía
             * se muestran todos
             */
            if ($estado === '' || $estado === null) {
                $estado = null;
            }

            $filtros = [
                'area_servicio_id' => $_GET['area_id'] ?? null,
                'lider_id' => $_GET['lider_id'] ?? null,
                'estado' => $estado,
                'termino' => $_GET['q'] ?? null
            ];

            $respuesta = $celulasController->listar($filtros,$pagina,$limite);

            echo json_encode(array_merge([
                'exito'=>true
            ], $respuesta));

            break;



        case 'buscar':

            $termino = $_GET['q'] ?? '';

            $celulas = $celulaModel->buscar($termino);

            echo json_encode([
                'exito'=>true,
                'data'=>$celulas
            ]);

            break;



        case 'sin-reporte':

            $celulas = $celulaModel->obtenerCelulasSinReporte(DIAS_ALERTA_SIN_REPORTE);

            echo json_encode([
                'exito'=>true,
                'data'=>$celulas
            ]);

            break;



        case 'estadisticas':

            validarRol(['pastor','lider_area']);

            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error'=>'ID requerido']);
                break;
            }

            $estadisticas = $celulaModel->obtenerEstadisticas($id);

            echo json_encode([
                'exito'=>true,
                'data'=>$estadisticas
            ]);

            break;



        case 'historial':

            validarRol(['pastor','lider_area','lider_celula']);

            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error'=>'ID requerido']);
                break;
            }

            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;

            $historial = $celulaModel->obtenerHistorialReuniones($id,$limite);

            echo json_encode([
                'exito'=>true,
                'data'=>$historial
            ]);

            break;



        case 'opciones':

            $opciones = $celulasController->obtenerOpcionesFormulario();

            echo json_encode([
                'exito'=>true,
                'data'=>$opciones
            ]);

            break;



        default:

            http_response_code(400);

            echo json_encode([
                'error'=>'Acción no especificada'
            ]);

    }

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'exito'=>false,
        'error'=>$e->getMessage()
    ]);

}
