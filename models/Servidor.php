<?php
/**
 * MODELO - SERVIDOR
 */

require_once 'Model.php';

class Servidor extends Model {
    protected $tabla = 'servidores';

    public function listar($filtros = [], $limite = 20, $offset = 0) {
        $sql = "SELECT 
                    u.id AS usuario_id,
                    MIN(s.id) AS id,
                    u.nombre_completo,
                    u.correo AS email,
                    u.telefono,
                    u.codigo_membresia,
                    GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,
                    GROUP_CONCAT(DISTINCT a.id ORDER BY a.id SEPARATOR ',') AS area_ids,
                    MIN(s.fecha_ingreso) AS fecha_ingreso,
                    MIN(s.fecha_modificacion) AS fecha_modificacion,
                    MIN(s.dui) AS dui,
                    MIN(s.genero) AS genero,
                    MIN(s.fecha_nacimiento) AS fecha_nacimiento,
                    MIN(s.bautizado) AS bautizado,
                    MIN(s.fecha_bautizo) AS fecha_bautizo
                FROM servidores s
                LEFT JOIN areas_servicio a ON s.area_servicio_id = a.id
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                WHERE 1 = 1";
        $params = [];
        if (!empty($filtros['area_servicio_id'])) {
            $sql .= " AND s.area_servicio_id = :area";
            $params[':area'] = (int)$filtros['area_servicio_id'];
        }
        if (!empty($filtros['q'])) {
            $sql .= " AND (u.nombre_completo LIKE :q1 OR u.correo LIKE :q2 OR u.telefono LIKE :q3)";
            $like = '%'.$filtros['q'].'%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        $sql .= " GROUP BY u.id, u.nombre_completo, u.correo, u.telefono, u.codigo_membresia
                  ORDER BY u.nombre_completo ASC
                  LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':lim', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $data = $stmt->fetchAll();

        $countSql = "SELECT COUNT(DISTINCT u.id) FROM servidores s
                     LEFT JOIN usuarios u ON s.usuario_id = u.id
                     WHERE 1=1";
        if (!empty($filtros['area_servicio_id'])) {
            $countSql .= " AND s.area_servicio_id = :area";
        }
        if (!empty($filtros['q'])) {
            $countSql .= " AND (u.nombre_completo LIKE :q1 OR u.correo LIKE :q2 OR u.telefono LIKE :q3)";
        }
        $stmtC = $this->conexion->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtC->bindValue($k, $v);
        }
        $stmtC->execute();
        $total = (int)$stmtC->fetchColumn();

        return ['data'=>$data,'total'=>$total,'limite'=>$limite];
    }

    public function obtener($id) {
        // buscar usuario asociado a este registro; si no existe, tratar id como usuario_id directo
        $sql = "SELECT u.id AS usuario_id
                FROM servidores s
                JOIN usuarios u ON s.usuario_id = u.id
                WHERE s.id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $uid = $row ? $row['usuario_id'] : $id;

        $sql2 = "SELECT 
                    u.id AS usuario_id,
                    MIN(s.id) AS id,
                    u.nombre_completo,
                    u.correo AS email,
                    u.telefono,
                    u.codigo_membresia,
                    GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,
                    GROUP_CONCAT(DISTINCT a.id ORDER BY a.id SEPARATOR ',') AS area_ids,
                    MIN(s.fecha_ingreso) AS fecha_ingreso,
                    MIN(s.fecha_modificacion) AS fecha_modificacion,
                    MIN(s.dui) AS dui,
                    MIN(s.genero) AS genero,
                    MIN(s.fecha_nacimiento) AS fecha_nacimiento,
                    MIN(s.bautizado) AS bautizado,
                    MIN(s.fecha_bautizo) AS fecha_bautizo
                FROM servidores s
                LEFT JOIN areas_servicio a ON s.area_servicio_id = a.id
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                WHERE u.id = :uid
                GROUP BY u.id, u.nombre_completo, u.correo, u.telefono, u.codigo_membresia";
        $stmt2 = $this->conexion->prepare($sql2);
        $stmt2->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt2->execute();
        return $stmt2->fetch();
    }

    public function crear($datos) {
        return $this->insertar($datos);
    }

    public function actualizarServidor($id, $datos) {
        return parent::actualizar($id, $datos);
    }

    public function eliminarServidor($id) {
        return $this->eliminar($id);
    }

    public function syncAreas($usuario_id, $areas = [], $fecha_ingreso = null, $extraCampos = []) {
        $areas = array_values(array_unique(array_map('intval', $areas)));
        $fecha = $fecha_ingreso ?: date('Y-m-d');

        // Reinserción completa para evitar inconsistencias
        $del = $this->conexion->prepare("DELETE FROM servidores WHERE usuario_id = :uid");
        $del->bindValue(':uid', (int)$usuario_id, PDO::PARAM_INT);
        $del->execute();

        if (empty($areas)) return;

        // Columnas fijas para evitar desajuste de parámetros en el INSERT.
        $cols = ['usuario_id','area_servicio_id','fecha_ingreso','activo'];
        // Campos extra persistidos por usuario (DUI se almacena en columna dui en BD).
        $extraKeys = ['dui','genero','fecha_nacimiento','bautizado','fecha_bautizo'];
        $cols = array_merge($cols, $extraKeys);
        $extra = array_intersect_key($extraCampos, array_flip($extraKeys));

        $placeholders = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = "INSERT INTO servidores (".implode(',', $cols).") VALUES ".$placeholders;

        $ins = $this->conexion->prepare($sql);
        $first = true;
        foreach ($areas as $areaId) {
            $i=1;
            $ins->bindValue($i++, (int)$usuario_id, PDO::PARAM_INT);
            $ins->bindValue($i++, (int)$areaId, PDO::PARAM_INT);
            $ins->bindValue($i++, $fecha);
            $ins->bindValue($i++, 1, PDO::PARAM_INT);
            // Para evitar duplicado en campos únicos (ej. cedula), sólo se llena en la primera fila.
            foreach ($extraKeys as $k) {
                $v = ($first && array_key_exists($k,$extra)) ? $extra[$k] : null;
                $ins->bindValue($i++, $v);
            }
            $ins->execute();
            $first = false;
        }
    }

    public function actualizarCamposPorUsuario($usuario_id, $campos) {
        if (empty($campos)) return false;
        $set = [];
        foreach ($campos as $k=>$v) { $set[] = "$k = :$k"; }
        $sql = "UPDATE servidores SET ".implode(',',$set)." WHERE usuario_id = :uid";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':uid', (int)$usuario_id, PDO::PARAM_INT);
        foreach ($campos as $k=>$v) {
            $stmt->bindValue(":$k",$v);
        }
        return $stmt->execute();
    }
}
?>
