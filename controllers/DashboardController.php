<?php
/**
 * CONTROLADOR - DASHBOARD
 * Obtiene datos y estadísticas para el panel principal
 */

require_once __DIR__ . '/../config/config.php';

class DashboardController {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener estadísticas generales del dashboard
     */
    public function obtenerEstadisticas() {
        try {
            $stats = [];
            
            // 1. Células activas
            $stmt = $this->conexion->query("SELECT COUNT(*) as total FROM celulas WHERE estado = 'activa'");
            $stats['celulas_activas'] = (int)($stmt->fetch()['total'] ?? 0);
            
            // 2. Total de asistentes (último mes)
            $stmt = $this->conexion->query("
                SELECT COALESCE(SUM(cantidad_asistentes), 0) as total 
                FROM reuniones 
                WHERE YEAR(fecha_reunion) = YEAR(NOW()) AND MONTH(fecha_reunion) = MONTH(NOW())
            ");
            $stats['asistentes_mes'] = (int)($stmt->fetch()['total'] ?? 0);
            
            // 3. Total de ofrendas del mes
            $stmt = $this->conexion->query("
                SELECT COALESCE(SUM(monto), 0) as total 
                FROM ofrendas 
                WHERE YEAR(fecha_reporte) = YEAR(NOW()) AND MONTH(fecha_reporte) = MONTH(NOW())
            ");
            $stats['ofrendas_mes'] = (float)($stmt->fetch()['total'] ?? 0);
            
            // 4. Total de servidores
            $stmt = $this->conexion->query("SELECT COUNT(*) as total FROM servidores WHERE activo = 1");
            $stats['servidores_activos'] = (int)($stmt->fetch()['total'] ?? 0);
            
            // 5. Total de usuarios
            $stmt = $this->conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
            $stats['usuarios_activos'] = (int)($stmt->fetch()['total'] ?? 0);
            
            return $stats;
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener datos para gráfica de células activas por estado
     */
    public function obtenerCelulasEstado() {
        try {
            $stmt = $this->conexion->query("
                SELECT estado, COUNT(*) as cantidad
                FROM celulas
                GROUP BY estado
            ");
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
            
            // Convertir a números
            return array_map(function($row) {
                return [
                    'estado' => $row['estado'],
                    'cantidad' => (int)$row['cantidad']
                ];
            }, $datos);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener datos para gráfica de asistencia por mes
     */
    public function obtenerAsistenciaMeses() {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    MONTH(fecha_reunion) as mes,
                    DATE_FORMAT(fecha_reunion, '%b') as nombre_mes,
                    SUM(cantidad_asistentes) as total_asistentes,
                    COUNT(*) as reuniones
                FROM reuniones
                WHERE YEAR(fecha_reunion) = YEAR(NOW()) AND realizada = 1
                GROUP BY MONTH(fecha_reunion)
                ORDER BY MONTH(fecha_reunion)
            ");
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
            
            // Convertir a números
            return array_map(function($row) {
                return [
                    'mes' => (int)$row['mes'],
                    'nombre_mes' => $row['nombre_mes'],
                    'total_asistentes' => (int)($row['total_asistentes'] ?? 0),
                    'reuniones' => (int)$row['reuniones']
                ];
            }, $datos);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener datos para gráfica de ofrendas por mes
     */
    public function obtenerOfrendasMeses() {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    MONTH(o.fecha_reporte) as mes,
                    DATE_FORMAT(o.fecha_reporte, '%b') as nombre_mes,
                    SUM(o.monto) as total_ofrendas,
                    COUNT(*) as num_ofrendas
                FROM ofrendas o
                WHERE YEAR(o.fecha_reporte) = YEAR(NOW())
                GROUP BY MONTH(o.fecha_reporte)
                ORDER BY MONTH(o.fecha_reporte)
            ");
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
            
            // Convertir a números
            return array_map(function($row) {
                return [
                    'mes' => (int)$row['mes'],
                    'nombre_mes' => $row['nombre_mes'],
                    'total_ofrendas' => (float)($row['total_ofrendas'] ?? 0),
                    'num_ofrendas' => (int)$row['num_ofrendas']
                ];
            }, $datos);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener servidores por área
     */
    public function obtenerServidoresPorArea() {
        try {
            $stmt = $this->conexion->query("
                SELECT 
                    a.nombre as area,
                    COUNT(s.id) as cantidad
                FROM areas_servicio a
                LEFT JOIN servidores s ON a.id = s.area_servicio_id AND s.activo = 1
                GROUP BY a.id, a.nombre
                ORDER BY cantidad DESC
            ");
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
            
            // Convertir a números
            return array_map(function($row) {
                return [
                    'area' => $row['area'],
                    'cantidad' => (int)$row['cantidad']
                ];
            }, $datos);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener últimas reuniones registradas
     */
    public function obtenerUltimasReuniones($limite = 5) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    r.id,
                    c.nombre as celula,
                    r.fecha_reunion as fecha,
                    r.cantidad_asistentes as numero_asistentes,
                    COALESCE(o.monto, 0) as ofrenda_monto
                FROM reuniones r
                JOIN celulas c ON r.celula_id = c.id
                LEFT JOIN ofrendas o ON r.id = o.reunion_id
                WHERE r.realizada = 1
                ORDER BY r.fecha_reunion DESC
                LIMIT :limite
            ");
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
            
            // Convertir a números
            return array_map(function($row) {
                return [
                    'id' => (int)$row['id'],
                    'celula' => $row['celula'],
                    'fecha' => $row['fecha'],
                    'numero_asistentes' => (int)$row['numero_asistentes'],
                    'ofrenda_monto' => (float)$row['ofrenda_monto']
                ];
            }, $datos);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
