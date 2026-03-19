<?php
/**
 * MODELO - OFRENDA
 * Gestiona control financiero de ofrendas
 */

require_once 'Model.php';

class Ofrenda extends Model {
    protected $tabla = 'ofrendas';
    
    /**
     * Cambiar estado de ofrenda
     */
    public function cambiarEstado($ofrenda_id, $nuevo_estado, $usuario_id) {
        try {
            $this->conexion->beginTransaction();
            
            // Obtener ofrenda actual
            $ofrenda = $this->obtenerPorId($ofrenda_id);
            if (!$ofrenda) {
                throw new Exception('Ofrenda no encontrada');
            }
            
            // Preparar datos de actualización según estado
            $datos_actualizacion = ['estado' => $nuevo_estado];
            
            if ($nuevo_estado == 'recibida') {
                $datos_actualizacion['usuario_recibe_id'] = $usuario_id;
                $datos_actualizacion['fecha_recepcion'] = date('Y-m-d H:i:s');
            } elseif ($nuevo_estado == 'conciliada') {
                $datos_actualizacion['usuario_concilia_id'] = $usuario_id;
                $datos_actualizacion['fecha_conciliacion'] = date('Y-m-d H:i:s');
            }
            
            // Actualizar ofrenda
            $this->actualizar($ofrenda_id, $datos_actualizacion);
            
            // Registrar en auditoría
            $this->registrarAuditoria('actualizar', $usuario_id, 'ofrendas', $ofrenda_id, $ofrenda, $datos_actualizacion);
            
            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtener ofrendas pendientes (no conciliadas)
     */
    public function obtenerPendientes() {
        $sql = "SELECT 
                    o.id, o.reunion_id, o.monto, o.estado,
                    r.fecha_reunion, c.nombre as celula,
                    u.nombre_completo as lider, u.telefono,
                    DATEDIFF(NOW(), o.fecha_reporte) as dias_pendiente
                FROM ofrendas o
                JOIN reuniones r ON o.reunion_id = r.id
                JOIN celulas c ON r.celula_id = c.id
                JOIN usuarios u ON o.lider_reporta_id = u.id
                WHERE o.estado != 'conciliada'
                ORDER BY o.fecha_reporte ASC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener ofrendas por período
     */
    public function obtenerPorPeriodo($fecha_inicio, $fecha_fin, $estado = null) {
        $sql = "SELECT 
                    o.*, c.nombre as celula, u.nombre_completo as lider,
                    SUM(o.monto) as total
                FROM ofrendas o
                JOIN reuniones r ON o.reunion_id = r.id
                JOIN celulas c ON r.celula_id = c.id
                JOIN usuarios u ON o.lider_reporta_id = u.id
                WHERE o.fecha_reporte BETWEEN :fecha_inicio AND :fecha_fin";
        
        if ($estado) {
            $sql .= " AND o.estado = :estado";
        }
        
        $sql .= " GROUP BY DATE(o.fecha_reporte), c.id
                  ORDER BY o.fecha_reporte DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        
        if ($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener total de ofrendas por área
     */
    public function obtenerTotalPorArea($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    a.id, a.nombre as area,
                    SUM(o.monto) as total_ofrendas,
                    COUNT(o.id) as cantidad_reportes,
                    AVG(o.monto) as promedio
                FROM areas_servicio a
                LEFT JOIN celulas c ON a.id = c.area_servicio_id
                LEFT JOIN reuniones r ON c.id = r.celula_id
                LEFT JOIN ofrendas o ON r.id = o.reunion_id";
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " WHERE o.fecha_reporte BETWEEN :fecha_inicio AND :fecha_fin";
        }
        
        $sql .= " GROUP BY a.id, a.nombre
                  ORDER BY total_ofrendas DESC";
        
        $stmt = $this->conexion->prepare($sql);
        
        if ($fecha_inicio && $fecha_fin) {
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Registrar auditoría de cambios
     */
    private function registrarAuditoria($accion, $usuario_id, $tabla, $registro_id, $valor_anterior, $valor_nuevo) {
        $sql = "INSERT INTO auditoria (usuario_id, accion, tabla_afectada, registro_id, valor_anterior, valor_nuevo, ip_usuario, fecha_hora)
                VALUES (:usuario_id, :accion, :tabla, :registro_id, :valor_anterior, :valor_nuevo, :ip, NOW())";
        
        $stmt = $this->conexion->prepare($sql);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $valorAnteriorJson = json_encode($valor_anterior);
        $valorNuevoJson = json_encode($valor_nuevo);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
        $stmt->bindParam(':registro_id', $registro_id, PDO::PARAM_INT);
        $stmt->bindParam(':valor_anterior', $valorAnteriorJson, PDO::PARAM_STR);
        $stmt->bindParam(':valor_nuevo', $valorNuevoJson, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Obtener detalle de una ofrenda con joins
     */
    public function obtenerDetalle($id) {
        $sql = "SELECT o.*, 
                       r.fecha_reunion,
                       c.id AS celula_id, c.nombre AS celula_nombre,
                       u.nombre_completo AS lider_nombre
                FROM ofrendas o
                JOIN reuniones r ON o.reunion_id = r.id
                JOIN celulas c ON r.celula_id = c.id
                LEFT JOIN usuarios u ON r.lider_reporta_id = u.id
                WHERE o.id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Listado paginado con filtros
     */
    public function listar($filtros = [], $pagina = 1, $limite = 20) {
        $offset = ($pagina - 1) * $limite;
        $where = [];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = "o.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['celula_id'])) {
            $where[] = "c.id = :celula_id";
            $params[':celula_id'] = $filtros['celula_id'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $where[] = "DATE(o.fecha_reporte) >= :fini";
            $params[':fini'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $where[] = "DATE(o.fecha_reporte) <= :ffin";
            $params[':ffin'] = $filtros['fecha_fin'];
        }

        $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT o.id, o.monto, o.estado, o.fecha_reporte, o.reunion_id,
                       c.id AS celula_id, c.nombre AS celula_nombre,
                       u.nombre_completo AS lider_nombre
                FROM ofrendas o
                JOIN reuniones r ON o.reunion_id = r.id
                JOIN celulas c ON r.celula_id = c.id
                LEFT JOIN usuarios u ON r.lider_reporta_id = u.id
                $whereSql
                ORDER BY o.fecha_reporte DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->conexion->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        // total
        $sqlTotal = "SELECT COUNT(*) FROM ofrendas o
                     JOIN reuniones r ON o.reunion_id = r.id
                     JOIN celulas c ON r.celula_id = c.id
                     $whereSql";
        $stmtT = $this->conexion->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtT->bindValue($k, $v);
        }
        $stmtT->execute();
        $total = (int)$stmtT->fetchColumn();

        return ['data' => $data, 'total' => $total, 'limite' => $limite, 'pagina' => $pagina];
    }

    /**
     * Registrar nueva ofrenda (estado inicial: reportada)
     */
    public function registrar($datos) {
        $datos['estado'] = $datos['estado'] ?? 'reportada';
        $datos['fecha_reporte'] = $datos['fecha_reporte'] ?? date('Y-m-d H:i:s');
        if (!empty($datos['reunion_id'])) {
            $stmt = $this->conexion->prepare("SELECT id FROM ofrendas WHERE reunion_id = :rid LIMIT 1");
            $stmt->bindParam(':rid', $datos['reunion_id'], PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch()) {
                throw new Exception('Ya existe una ofrenda para esta reunión');
            }
        }
        return $this->insertar($datos);
    }

    /**
     * Historial de cambios desde auditoría
     */
    public function obtenerHistorial($id) {
        $sql = "SELECT fecha_hora, usuario_id, valor_anterior, valor_nuevo
                FROM auditoria
                WHERE tabla_afectada = 'ofrendas' AND registro_id = :id
                ORDER BY fecha_hora DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Historial con detalle (usuario y estados antes/después)
     */
    public function obtenerHistorialDetallado($id) {
        $sql = "SELECT a.fecha_hora, a.usuario_id, u.nombre_completo,
                       a.valor_anterior, a.valor_nuevo
                  FROM auditoria a
                  LEFT JOIN usuarios u ON a.usuario_id = u.id
                 WHERE a.tabla_afectada = 'ofrendas' AND a.registro_id = :id
                 ORDER BY a.fecha_hora DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        // Decodificar estados
        foreach ($rows as &$row) {
            $prev = json_decode($row['valor_anterior'] ?? '{}', true);
            $next = json_decode($row['valor_nuevo'] ?? '{}', true);
            $row['estado_anterior'] = $prev['estado'] ?? null;
            $row['estado_nuevo'] = $next['estado'] ?? null;
        }
        return $rows;
    }

    /**
     * Resumen por estado
     */
    public function resumenEstados() {
        $sql = "SELECT estado, COUNT(*) as cantidad, SUM(monto) as total
                  FROM ofrendas
                 GROUP BY estado";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Totales por mes (últimos N meses)
     */
    public function totalesPorMes($meses = 6) {
        $sql = "SELECT DATE_FORMAT(fecha_reporte,'%Y-%m') as mes,
                       SUM(monto) as total,
                       COUNT(*) as cantidad
                  FROM ofrendas
                 WHERE fecha_reporte >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL :meses MONTH),'%Y-%m-01')
                 GROUP BY mes
                 ORDER BY mes";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
