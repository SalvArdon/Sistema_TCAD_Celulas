<?php
/**
 * API - USUARIOS
 * Endpoints: listar, crear, editar, obtener
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

validarSesion();

require_once __DIR__ . '/../models/Usuario.php';

$usuarioModel = new Usuario($conexion);
$accion = $_GET['accion'] ?? null;
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch($accion) {
        case 'crear':
            validarRol(['pastor']);
            
            if ($metodo == 'POST') {
                $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                
                // Validar que no exista
                $usuario_existente = $usuarioModel->obtenerPorCorreo($datos['correo']);
                if ($usuario_existente) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'error' => 'Correo ya existe']);
                    break;
                }
                
                $datos_usuario = [
                    'nombre_completo' => $datos['nombre_completo'],
                    'correo' => $datos['correo'],
                    'telefono' => $datos['telefono'] ?? null,
                    'password' => $datos['password'],
                    'rol_id' => $datos['rol_id'],
                    'activo' => true
                ];
                
                $usuario_id = $usuarioModel->registrar($datos_usuario);
                
                if ($usuario_id) {
                    // Si es servidor, crear registro en tabla servidores
                    if ($datos['rol_id'] == 5) { // rol servidor
                        $sql_servidor = "INSERT INTO servidores (usuario_id, area_servicio_id, activo)
                                        VALUES (:usuario_id, :area_servicio_id, TRUE)";
                        
                        $stmt = $conexion->prepare($sql_servidor);
                        $stmt->bindParam(':usuario_id', $usuario_id);
                        $stmt->bindParam(':area_servicio_id', $datos['area_servicio_id'] ?? 8);
                        $stmt->execute();
                    }
                    
                    echo json_encode(['exito' => true, 'mensaje' => 'Usuario creado', 'id' => $usuario_id]);
                } else {
                    echo json_encode(['exito' => false, 'error' => 'Error al crear usuario']);
                }
            }
            break;
            
        case 'listar':
            $rol_id = $_GET['rol_id'] ?? null;
            $area_id = $_GET['area_id'] ?? null;
            
            if ($area_id) {
                $usuarios = $usuarioModel->obtenerServidoresPorArea($area_id);
            } else {
                $usuarios = $usuarioModel->obtenerTodos();
            }
            
            echo json_encode(['exito' => true, 'data' => $usuarios]);
            break;
            
        case 'obtener':
            validarRol(['pastor', 'lider_area']);
            
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
                break;
            }
            
            $usuario = $usuarioModel->obtenerPorId($id);
            
            if ($usuario) {
                unset($usuario['password_hash']);
                echo json_encode(['exito' => true, 'data' => $usuario]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
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
