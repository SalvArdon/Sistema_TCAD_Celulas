<?php
/**
 * MODELO - REUNIÓN
 * Gestiona reuniones y reportes de células
 */

require_once 'Model.php';

class Reunion extends Model {
    protected $tabla = 'reuniones';
    
    /**
     * Registrar nueva reunión y ofrendas
     */
    public function registrarReunion($datos_reunion, $datos_ofrenda = null) {
        // La transacción se maneja desde el controlador
        // Registrar reunión
        $reunion_id = $this->insertar($datos_reunion);
        
        // Registrar ofrenda si existe
        if ($datos_ofrenda && $datos_ofrenda['monto'] > 0) {
            $datos_ofrenda['reunion_id'] = $reunion_id;

            // Evitar duplicados por reunion_id
            $stmtCheck = $this->conexion->prepare("SELECT id FROM ofrendas WHERE reunion_id = :rid LIMIT 1");
            $stmtCheck->bindValue(':rid', $reunion_id, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) {
                throw new Exception('Ya existe una ofrenda para esta reunión');
            }

            $sql_ofrenda = "INSERT INTO ofrendas (reunion_id, monto, estado, lider_reporta_id, fecha_reporte) 
                           VALUES (:reunion_id, :monto, :estado, :lider_reporta_id, NOW())";
            
            $stmt = $this->conexion->prepare($sql_ofrenda);
            $stmt->bindValue(':reunion_id', $datos_ofrenda['reunion_id'], PDO::PARAM_INT);
            $stmt->bindValue(':monto', $datos_ofrenda['monto']);
            $stmt->bindValue(':estado', $datos_ofrenda['estado']);
            $stmt->bindValue(':lider_reporta_id', $datos_ofrenda['lider_reporta_id'], PDO::PARAM_INT);
            $stmt->execute();
        }
        
        return $reunion_id;
    }
    
    /**
     * Obtener reuniones de una célula
     */
    public function obtenerPorCelula($celula_id, $limite = 10, $offset = 0) {
        $sql = "SELECT 
                    r.*,
                    c.nombre as celula_nombre,
                    u.nombre_completo as lider_nombre,
                    o.monto as ofrenda_monto,
                    o.estado as ofrenda_estado
                FROM reuniones r
                LEFT JOIN celulas c ON r.celula_id = c.id
                LEFT JOIN usuarios u ON r.lider_reporta_id = u.id
                LEFT JOIN ofrendas o ON r.id = o.reunion_id
                WHERE r.celula_id = :celula_id
                ORDER BY r.fecha_reunion DESC
                LIMIT :limite OFFSET :offset";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener reportes recientes (últimos 7 días)
     */
    public function obtenerReportesRecientes($dias = 7) {
        $sql = "SELECT 
                    r.id, r.fecha_reunion, r.cantidad_asistentes,
                    c.nombre as celula_nombre,
                    u.nombre_completo as lider_nombre,
                    o.monto, o.estado
                FROM reuniones r
                JOIN celulas c ON r.celula_id = c.id
                JOIN usuarios u ON r.lider_reporta_id = u.id
                LEFT JOIN ofrendas o ON r.id = o.reunion_id
                WHERE r.fecha_reporte >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                ORDER BY r.fecha_reporte DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Registrar asistencia
     */
    public function registrarAsistencia($reunion_id, $usuario_id) {
        $sql = "INSERT INTO asistencias (reunion_id, usuario_id, asistio) 
                VALUES (:reunion_id, :usuario_id, TRUE)
                ON DUPLICATE KEY UPDATE asistio = TRUE";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':reunion_id', $reunion_id, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Obtener asistentes de una reunión
     */
    public function obtenerAsistentes($reunion_id) {
        $sql = "SELECT 
                    a.id, a.usuario_id, a.nombre_visitante,
                    u.nombre_completo, u.telefono, u.correo
                FROM asistencias a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.reunion_id = :reunion_id
                ORDER BY u.nombre_completo";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':reunion_id', $reunion_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Listar reuniones con filtros y paginación
     */
    public function listar($filtros = [], $limite = 20, $offset = 0, $orden = 'DESC') {
        $sql = "SELECT 
                    r.*,
                    c.nombre AS celula_nombre,
                    c.area_servicio_id,
                    u.nombre_completo AS lider_nombre,
                    o.monto AS ofrenda_monto,
                    o.estado AS ofrenda_estado
                FROM reuniones r
                LEFT JOIN celulas c ON r.celula_id = c.id
                LEFT JOIN usuarios u ON r.lider_reporta_id = u.id
                LEFT JOIN ofrendas o ON r.id = o.reunion_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['celula_id'])) {
            $sql .= " AND r.celula_id = :celula_id";
            $params[':celula_id'] = (int)$filtros['celula_id'];
        }

        if (!empty($filtros['area_id'])) {
            $sql .= " AND c.area_servicio_id = :area_id";
            $params[':area_id'] = (int)$filtros['area_id'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND r.fecha_reunion >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND r.fecha_reunion <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] === 'pendiente') {
                $sql .= " AND (r.realizada = 0 OR r.realizada IS NULL)";
            } elseif ($filtros['estado'] === 'hoy') {
                $sql .= " AND DATE(r.fecha_reunion) = CURDATE()";
            } elseif ($filtros['estado'] === 'realizada') {
                $sql .= " AND r.realizada = 1";
            }
        }

        $order = strtoupper($orden) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY r.fecha_reunion $order, r.id $order LIMIT :limite OFFSET :offset";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contar($filtros = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM reuniones r 
                LEFT JOIN celulas c ON r.celula_id = c.id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['celula_id'])) {
            $sql .= " AND r.celula_id = :celula_id";
            $params[':celula_id'] = (int)$filtros['celula_id'];
        }

        if (!empty($filtros['area_id'])) {
            $sql .= " AND c.area_servicio_id = :area_id";
            $params[':area_id'] = (int)$filtros['area_id'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND r.fecha_reunion >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND r.fecha_reunion <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] === 'pendiente') {
                $sql .= " AND (r.realizada = 0 OR r.realizada IS NULL)";
            } elseif ($filtros['estado'] === 'hoy') {
                $sql .= " AND DATE(r.fecha_reunion) = CURDATE()";
            } elseif ($filtros['estado'] === 'realizada') {
                $sql .= " AND r.realizada = 1";
            }
        }

        $stmt = $this->conexion->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function toggleRealizada($id, $realizada) {
        $sql = "UPDATE reuniones SET realizada = :realizada, fecha_reporte = NOW() WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':realizada', $realizada ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
