<?php
/**
 * CONTROLADOR - ÁREAS / MINISTERIOS
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Area.php';

class AreasController {
    private $model;

    public function __construct($conexion) {
        $this->model = new Area($conexion);
    }

    public function listar($filtros=[], $pagina=1, $limite=REGISTROS_POR_PAGINA) {
        $offset = ($pagina-1)*$limite;
        $res = $this->model->listar($filtros,$limite,$offset);
        return ['data'=>$res['data'],'total'=>$res['total'],'pagina'=>$pagina,'limite'=>$limite];
    }

    public function obtener($id) {
        $row = $this->model->obtener($id);
        if (!$row) return ['exito'=>false,'mensaje'=>'Área no encontrada'];
        return ['exito'=>true,'data'=>$row];
    }

    public function crear($datos) {
        $val = $this->validar($datos);
        if ($val !== true) return $val;
        $datos['activa'] = isset($datos['activa']) ? (int)$datos['activa'] : 1;
        $id = $this->model->crear($datos);
        return $id ? ['exito'=>true,'id'=>$id] : ['exito'=>false,'mensaje'=>'No se pudo crear'];
    }

    public function actualizar($id,$datos) {
        $val = $this->validar($datos,$id);
        if ($val !== true) return $val;
        $datos['activa'] = isset($datos['activa']) ? (int)$datos['activa'] : 1;
        $ok = $this->model->actualizarArea($id,$datos);
        return ['exito'=>$ok,'mensaje'=>$ok?'Actualizado':'No se pudo actualizar'];
    }

    public function eliminar($id) {
        $ok = $this->model->eliminarLogico($id);
        return ['exito'=>$ok,'mensaje'=>$ok?'Desactivada':'No se pudo desactivar'];
    }

    private function validar($datos,$id=null) {
        if (empty($datos['nombre'])) return ['exito'=>false,'mensaje'=>'Nombre requerido'];
        if (!empty($datos['lider_id']) && !is_numeric($datos['lider_id'])) {
            return ['exito'=>false,'mensaje'=>'Líder inválido'];
        }
        return true;
    }
}
?>
