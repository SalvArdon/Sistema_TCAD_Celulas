<?php
/**
 * MODELO - CÉLULA
 * Gestiona células, reuniones e información
 */

require_once 'Model.php';

class Celula extends Model {
    protected $tabla = 'celulas';
    
    public function __construct($conexion = null) {
        if ($conexion) {
            parent::__construct($conexion);
        }
    }
    
    /**
     * Obtener todas las células con información completa
     */
    public function listar($limite = 50, $pagina = 1, $filtros = []) {
        $offset = ($pagina - 1) * $limite;
        
        try {
            $sql = "
                SELECT 
                    c.*,
                    u_lider.nombre_completo AS lider,
                    u_area.nombre_completo AS lider_area,
                    u_anfitrion.nombre_completo AS anfitrion,
                    a.nombre AS area_servicio,
                    COALESCE(r.total_reuniones, 0) AS reuniones_mes
                FROM celulas c
                LEFT JOIN usuarios u_lider ON c.lider_id = u_lider.id
                LEFT JOIN usuarios u_area ON c.lider_area_id = u_area.id
                LEFT JOIN usuarios u_anfitrion ON c.anfitrion_id = u_anfitrion.id
                LEFT JOIN areas_servicio a ON c.area_servicio_id = a.id
                LEFT JOIN (
                    SELECT celula_id, COUNT(*) AS total_reuniones
                    FROM reuniones
                    WHERE YEAR(fecha_reunion) = YEAR(NOW()) 
                      AND MONTH(fecha_reunion) = MONTH(NOW())
                    GROUP BY celula_id
                ) r ON c.id = r.celula_id
                WHERE 1 = 1
            ";
            
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND c.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['area_servicio_id'])) {
                $sql .= " AND c.area_servicio_id = :area_servicio_id";
                $params[':area_servicio_id'] = (int)$filtros['area_servicio_id'];
            }
            
            if (!empty($filtros['lider_id'])) {
                $sql .= " AND c.lider_id = :lider_id";
                $params[':lider_id'] = (int)$filtros['lider_id'];
            }
            
            if (!empty($filtros['termino'])) {
                $sql .= " AND c.nombre LIKE :termino";
                $params[':termino'] = '%' . $filtros['termino'] . '%';
            }
            
            $sql .= " ORDER BY c.nombre LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Contar cÃ©lulas aplicando los mismos filtros de listado
     */
    public function contarFiltrado($filtros = []) {
        try {
            $sql = "
                SELECT COUNT(*) as total
                FROM celulas c
                LEFT JOIN usuarios u_lider ON c.lider_id = u_lider.id
                WHERE 1 = 1
            ";
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND c.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['area_servicio_id'])) {
                $sql .= " AND c.area_servicio_id = :area_servicio_id";
                $params[':area_servicio_id'] = (int)$filtros['area_servicio_id'];
            }
            
            if (!empty($filtros['lider_id'])) {
                $sql .= " AND c.lider_id = :lider_id";
                $params[':lider_id'] = (int)$filtros['lider_id'];
            }
            
            if (!empty($filtros['termino'])) {
                $sql .= " AND (c.nombre LIKE :termino OR c.zona LIKE :termino OR u_lider.nombre_completo LIKE :termino)";
                $params[':termino'] = '%' . $filtros['termino'] . '%';
            }
            
            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtener célula por ID con detalles
     */
    public function obtenerConDetalles($id = null) {
        try {
            $sql = "
                SELECT 
                    c.*,
                    u_lider.nombre_completo AS lider_nombre,
                    u_lider.telefono AS lider_telefono,
                    u_area.nombre_completo AS lider_area_nombre,
                    u_anfitrion.nombre_completo AS anfitrion_nombre,
                    a.nombre AS area_servicio,
                    COALESCE(r.total_reuniones, 0) AS reuniones_mes
                FROM celulas c
                LEFT JOIN usuarios u_lider ON c.lider_id = u_lider.id
                LEFT JOIN usuarios u_area ON c.lider_area_id = u_area.id
                LEFT JOIN usuarios u_anfitrion ON c.anfitrion_id = u_anfitrion.id
                LEFT JOIN areas_servicio a ON c.area_servicio_id = a.id
                LEFT JOIN (
                    SELECT celula_id, COUNT(*) AS total_reuniones
                    FROM reuniones
                    WHERE YEAR(fecha_reunion) = YEAR(NOW()) 
                      AND MONTH(fecha_reunion) = MONTH(NOW())
                    GROUP BY celula_id
                ) r ON c.id = r.celula_id
            ";
            
            if ($id !== null) {
                $sql .= " WHERE c.id = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            $sql .= " ORDER BY c.nombre";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return $id === null ? [] : null;
        }
    }
    
    /**
     * Crear nueva célula
     */
    public function crear($nombre, $lider_id, $lider_area_id, $anfitrion_id, $area_servicio_id, 
                         $direccion, $zona, $dia_semana, $hora_inicio, $estado = 'activa', 
                         $cantidad_promedio = 10) {
        try {
            $stmt = $this->conexion->prepare("
                INSERT INTO celulas 
                (nombre, lider_id, lider_area_id, anfitrion_id, area_servicio_id, 
                 direccion, zona, dia_semana, hora_inicio, estado, cantidad_promedio_asistentes, 
                 fecha_creacion, fecha_modificacion)
                VALUES 
                (:nombre, :lider_id, :lider_area_id, :anfitrion_id, :area_servicio_id,
                 :direccion, :zona, :dia_semana, :hora_inicio, :estado, :cantidad_promedio,
                 NOW(), NOW())
            ");
            
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':lider_id', $lider_id, PDO::PARAM_INT);
            $stmt->bindParam(':lider_area_id', $lider_area_id, PDO::PARAM_INT);
            $stmt->bindParam(':anfitrion_id', $anfitrion_id, PDO::PARAM_INT);
            $stmt->bindParam(':area_servicio_id', $area_servicio_id, PDO::PARAM_INT);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':zona', $zona);
            $stmt->bindParam(':dia_semana', $dia_semana);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':cantidad_promedio', $cantidad_promedio, PDO::PARAM_INT);
            
            $stmt->execute();
            return $this->conexion->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Eliminar célula (soft delete)
     */
    public function eliminar($id) {
        try {
            $stmt = $this->conexion->prepare("
                UPDATE celulas SET 
                    estado = 'inactiva',
                    fecha_modificacion = NOW(),
                    fecha_cierre = NOW()
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Buscar células por nombre o zona
     */
    public function buscar($termino) {
        try {
            $termino = "%$termino%";
            $stmt = $this->conexion->prepare("
                SELECT 
                    c.id,
                    c.nombre,
                    c.area_servicio_id,
                    a.nombre as area_servicio,
                    c.dia_semana,
                    c.hora_inicio,
                    c.zona,
                    c.estado,
                    c.cantidad_promedio_asistentes,
                    u.nombre_completo as lider,
                    COUNT(r.id) as reuniones_mes
                FROM celulas c
                LEFT JOIN usuarios u ON c.lider_id = u.id
                LEFT JOIN areas_servicio a ON c.area_servicio_id = a.id
                LEFT JOIN reuniones r ON c.id = r.celula_id 
                    AND YEAR(r.fecha_reunion) = YEAR(NOW()) 
                    AND MONTH(r.fecha_reunion) = MONTH(NOW())
                WHERE c.nombre LIKE :termino
                GROUP BY c.id
                ORDER BY c.nombre
            ");
            $stmt->bindParam(':termino', $termino);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener células por área de servicio
     */
    public function obtenerPorArea($area_id) {
        $sql = "SELECT * FROM celulas WHERE area_servicio_id = :area_id AND estado = 'activa'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener células por líder
     */
    public function obtenerPorLider($lider_id) {
        $sql = "SELECT * FROM celulas WHERE lider_id = :lider_id ORDER BY nombre";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':lider_id', $lider_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar células sin reporte (mayor a N días)
     */
    public function obtenerCelulasSinReporte($dias = 14) {
        $sql = "SELECT c.id, c.nombre, c.lider_id, u.nombre_completo, u.telefono, u.correo
                FROM celulas c
                LEFT JOIN usuarios u ON c.lider_id = u.id
                LEFT JOIN (
                    SELECT celula_id, MAX(fecha_reunion) as ultima_reunion
                    FROM reuniones
                    GROUP BY celula_id
                ) r ON c.id = r.celula_id
                WHERE c.estado = 'activa'
                AND (
                    r.ultima_reunion IS NULL 
                    OR DATEDIFF(NOW(), r.ultima_reunion) > :dias
                )";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de célula
     */
    public function obtenerEstadisticas($celula_id) {
        $sql = "SELECT 
                    COUNT(DISTINCT r.id) as total_reuniones,
                    COUNT(DISTINCT a.usuario_id) as total_miembros_unicos,
                    AVG(r.cantidad_asistentes) as promedio_asistentes,
                    SUM(o.monto) as total_ofrendas,
                    COUNT(DISTINCT o.id) as total_ofrendas_registradas
                FROM celulas c
                LEFT JOIN reuniones r ON c.id = r.celula_id
                LEFT JOIN asistencias a ON r.id = a.reunion_id
                LEFT JOIN ofrendas o ON r.id = o.reunion_id
                WHERE c.id = :celula_id";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener historial de reuniones de una célula
     */
    public function obtenerHistorialReuniones($celula_id, $limite = 20) {
        $sql = "SELECT 
                    r.id,
                    r.fecha_reunion,
                    r.realizada,
                    r.cantidad_asistentes,
                    r.cantidad_nuevos,
                    r.comentarios,
                    o.monto AS ofrenda_monto
                FROM reuniones r
                LEFT JOIN ofrendas o ON o.reunion_id = r.id
                WHERE r.celula_id = :celula_id
                ORDER BY r.fecha_reunion DESC, r.id DESC
                LIMIT :limite";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener líderes disponibles
     */
    public function obtenerLideresDisponibles() {
        try {
            $stmt = $this->conexion->query("
                SELECT u.id, u.nombre_completo, COUNT(c.id) as celulas_asignadas
                FROM usuarios u
                LEFT JOIN celulas c ON u.id = c.lider_id AND c.estado = 'activa'
                WHERE u.rol_id IN (2, 3) AND u.activo = 1
                GROUP BY u.id
                ORDER BY celulas_asignadas ASC, u.nombre_completo
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener áreas de servicio
     */
    public function obtenerAreas() {
        try {
            $stmt = $this->conexion->query("SELECT * FROM areas_servicio ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtener usuarios activos para usar como anfitriones/líder de área
     */
    public function obtenerUsuariosActivos() {
        try {
            $stmt = $this->conexion->query("
                SELECT id, nombre_completo 
                FROM usuarios 
                WHERE activo = 1 
                ORDER BY nombre_completo
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Verificar si ya existe una célula con el mismo nombre
     */
    public function existeNombre($nombre, $excluir_id = null) {
        $sql = "SELECT id FROM celulas WHERE nombre = :nombre";
        if ($excluir_id) {
            $sql .= " AND id != :excluir";
        }
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        if ($excluir_id) {
            $stmt->bindParam(':excluir', $excluir_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool)$stmt->fetch();
    }
}
?>
