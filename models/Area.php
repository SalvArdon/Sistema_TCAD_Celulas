<?php
/**
 * MODELO - ÁREA / MINISTERIO
 */

require_once 'Model.php';

class Area extends Model {
    protected $tabla = 'areas_servicio';

    public function listar($filtros = [], $limite = 20, $offset = 0) {
        $sql = "SELECT a.*, u.nombre_completo AS lider_nombre
                FROM areas_servicio a
                LEFT JOIN usuarios u ON a.lider_id = u.id
                WHERE 1=1";
        $params = [];
        if (!empty($filtros['estado'])) {
            $sql .= " AND a.activa = :estado";
            $params[':estado'] = $filtros['estado'] === 'activa' ? 1 : 0;
        }
        if (!empty($filtros['q'])) {
            $sql .= " AND (a.nombre LIKE :q OR a.descripcion LIKE :q)";
            $params[':q'] = '%'.$filtros['q'].'%';
        }
        $sql .= " ORDER BY a.nombre ASC LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':lim', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $k=>$v) { $stmt->bindValue($k,$v); }
        $stmt->execute();
        $data = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) FROM areas_servicio a WHERE 1=1";
        if (!empty($filtros['estado'])) {
            $countSql .= " AND a.activa = :estado";
        }
        if (!empty($filtros['q'])) {
            $countSql .= " AND (a.nombre LIKE :q OR a.descripcion LIKE :q)";
        }
        $stmtC = $this->conexion->prepare($countSql);
        foreach ($params as $k=>$v) { $stmtC->bindValue($k,$v); }
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        return ['data'=>$data,'total'=>$total,'limite'=>$limite];
    }

    public function obtener($id) {
        $sql = "SELECT a.*, u.nombre_completo AS lider_nombre
                FROM areas_servicio a
                LEFT JOIN usuarios u ON a.lider_id = u.id
                WHERE a.id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id',$id,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear($datos) { return $this->insertar($datos); }
    public function actualizarArea($id,$datos) { return $this->actualizar($id,$datos); }
    public function eliminarLogico($id) {
        $sql = "UPDATE {$this->tabla} SET activa = 0 WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id',$id,PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
