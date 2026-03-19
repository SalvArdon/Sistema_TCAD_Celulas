<?php
/**
 * CONTROLADOR - SERVIDORES
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Servidor.php';
require_once __DIR__ . '/../models/Usuario.php';

class ServidoresController {
    private $conexion;
    private $servidorModel;
    private $usuarioModel;

    public function __construct($conexion) {
        $this->conexion      = $conexion;
        $this->servidorModel = new Servidor($conexion);
        $this->usuarioModel  = new Usuario($conexion);
    }

    public function listar($filtros = [], $pagina = 1, $limite = REGISTROS_POR_PAGINA) {
        $offset = ($pagina - 1) * $limite;
        $res = $this->servidorModel->listar($filtros, $limite, $offset);
        return [
            'data'   => $res['data'],
            'total'  => $res['total'],
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function obtener($id) {
        $row = $this->servidorModel->obtener($id);
        if (!$row) return ['exito'=>false,'mensaje'=>'Servidor no encontrado'];
        return ['exito'=>true,'data'=>$row];
    }

    public function crear($datos) {
        $val = $this->validar($datos);
        if ($val !== true) return $val;

        // localizar o crear usuario base
        $usuario = $this->usuarioModel->obtenerPorCorreo($datos['email']);
        if (!$usuario) {
            $usuarioId = $this->crearUsuarioDesdeServidor($datos);
        } else {
            $usuarioId = $usuario['id'];
            // refrescar datos de contacto
            $this->usuarioModel->actualizar($usuarioId, [
                'nombre_completo' => $datos['nombre_completo'],
                'telefono'        => $datos['telefono'] ?? null
            ]);
        }
        if (!$usuarioId) return ['exito'=>false,'mensaje'=>'No se pudo crear usuario'];

        $areas = [];
        if (!empty($datos['areas']) && is_array($datos['areas'])) {
            $areas = $datos['areas'];
        } elseif (!empty($datos['area_servicio_id'])) {
            $areas = [$datos['area_servicio_id']];
        }
        if (empty($areas)) return ['exito'=>false,'mensaje'=>'Selecciona al menos un área'];

        $fecha = !empty($datos['fecha_ingreso']) ? $datos['fecha_ingreso'] : date('Y-m-d');
        $extra = $this->extraCampos($datos);
        $this->servidorModel->syncAreas($usuarioId, $areas, $fecha, $extra);
        $this->servidorModel->actualizarCamposPorUsuario($usuarioId, array_merge($extra, ['fecha_ingreso'=>$fecha]));

        return ['exito'=>true,'usuario_id'=>$usuarioId];
    }

    public function actualizar($id, $datos) {
        $val = $this->validar($datos, $id);
        if ($val !== true) return $val;

        $row = $this->servidorModel->obtener($id);
        if (!$row) return ['exito'=>false,'mensaje'=>'Servidor no encontrado'];

        // actualizar usuario relacionado
        if (!empty($row['usuario_id'])) {
            $this->usuarioModel->actualizar($row['usuario_id'], [
                'nombre_completo' => $datos['nombre_completo'],
                'correo'          => $datos['email'],
                'telefono'        => $datos['telefono'] ?? null
            ]);
        }

        $areas = [];
        if (!empty($datos['areas']) && is_array($datos['areas'])) {
            $areas = $datos['areas'];
        } elseif (!empty($datos['area_servicio_id'])) {
            $areas = [$datos['area_servicio_id']];
        }
        if (empty($areas)) return ['exito'=>false,'mensaje'=>'Selecciona al menos un área'];

        $fecha = !empty($datos['fecha_ingreso']) ? $datos['fecha_ingreso'] : $row['fecha_ingreso'];
        $extra = $this->extraCampos($datos);
        $this->servidorModel->syncAreas($row['usuario_id'], $areas, $fecha, $extra);
        $this->servidorModel->actualizarCamposPorUsuario($row['usuario_id'], array_merge($extra, ['fecha_ingreso'=>$fecha]));

        return ['exito'=>true,'mensaje'=>'Actualizado'];
    }

    public function eliminar($id) {
        $ok = $this->servidorModel->eliminarServidor($id);
        return ['exito'=>$ok ? true : false,'mensaje'=>$ok?'Eliminado':'No se pudo eliminar'];
    }

    private function validar($datos, $id = null) {
        if (empty($datos['nombre_completo']) || empty($datos['email'])) {
            return ['exito'=>false,'mensaje'=>'Nombre y email son obligatorios'];
        }
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            return ['exito'=>false,'mensaje'=>'Email inválido'];
        }
        if (!empty($datos['telefono']) && !preg_match('/^[0-9+() -]{7,20}$/', $datos['telefono'])) {
            return ['exito'=>false,'mensaje'=>'Teléfono inválido'];
        }
        if (empty($datos['areas']) && empty($datos['area_servicio_id'])) {
            return ['exito'=>false,'mensaje'=>'Selecciona al menos un área'];
        }
        return true;
    }

    private function extraCampos($datos) {
        $dui = isset($datos['dui']) ? trim($datos['dui']) : null;
        if ($dui === '') { $dui = null; }
        return [
            // El campo en BD es dui
            'dui' => $dui,
            'genero' => $datos['genero'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'bautizado' => isset($datos['bautizado']) ? (int)$datos['bautizado'] : 0,
            'fecha_bautizo' => $datos['fecha_bautizo'] ?? null
        ];
    }

    private function crearUsuarioDesdeServidor($datos) {
        $codigo = $this->generarCodigoMembresia();
        $passwordPlano = bin2hex(random_bytes(4));
        $passwordHash = $this->usuarioModel->encriptarPassword($passwordPlano);

        return $this->usuarioModel->insertar([
            'nombre_completo'  => $datos['nombre_completo'],
            'correo'           => $datos['email'],
            'telefono'         => $datos['telefono'] ?? null,
            'password_hash'    => $passwordHash,
            'rol_id'           => 5, // servidor
            'codigo_membresia' => $codigo,
            'fecha_ingreso'    => !empty($datos['fecha_ingreso']) ? $datos['fecha_ingreso'] : date('Y-m-d'),
            'activo'           => 1,
            'ip_registro'      => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    private function generarCodigoMembresia() {
        do {
            $codigo = 'MEM' . strtoupper(bin2hex(random_bytes(3)));
            $stmt = $this->conexion->prepare("SELECT id FROM usuarios WHERE codigo_membresia = :c");
            $stmt->bindValue(':c', $codigo);
            $stmt->execute();
        } while ($stmt->rowCount() > 0);
        return $codigo;
    }
}
?>
