<?php
/**
 * CONTROLADOR - REUNIÓN
 * Gestiona registro de reuniones desde móvil
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Reunion.php';
require_once __DIR__ . '/../models/Celula.php';

class ReunionController {
    private $reunionModel;
    private $celulaModel;
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->reunionModel = new Reunion($conexion);
        $this->celulaModel = new Celula($conexion);
    }
    
    /**
     * Registrar nueva reunión (MOBILE FIRST - Optimizado para teléfono)
     */
    public function registrarReunion($celula_id, $lider_reporta_id, $datos) {
        try {
            if (empty($celula_id)) throw new Exception('Célula requerida');
            if (empty($datos['fecha_reunion'])) throw new Exception('Fecha requerida');

            $this->conexion->beginTransaction();
            
            // Validar célula
            $celula = $this->celulaModel->obtenerPorId($celula_id);
            if (!$celula) {
                throw new Exception('Célula no encontrada');
            }
            
            // Preparar datos de reunión
            $datos_reunion = [
                'celula_id' => $celula_id,
                'fecha_reunion' => $datos['fecha_reunion'] ?? date('Y-m-d'),
                'realizada' => isset($datos['realizada']) ? $datos['realizada'] : TRUE,
                'motivo_cancelacion' => $datos['motivo_cancelacion'] ?? null,
                'cantidad_asistentes' => (int)($datos['cantidad_asistentes'] ?? 0),
                'cantidad_nuevos' => (int)($datos['cantidad_nuevos'] ?? 0),
                'lider_reporta_id' => $lider_reporta_id,
                'fecha_reporte' => date('Y-m-d H:i:s'),
                'ip_reporte' => $_SERVER['REMOTE_ADDR'] ?? '',
                'comentarios' => $datos['comentarios'] ?? null
            ];
            
            // Preparar datos de ofrenda
            $datos_ofrenda = null;
            if (isset($datos['monto_ofrenda']) && $datos['monto_ofrenda'] > 0) {
                $datos_ofrenda = [
                    'monto' => (float)$datos['monto_ofrenda'],
                    'estado' => 'reportada',
                    'lider_reporta_id' => $lider_reporta_id
                ];
            }
            
            // Registrar reunión y ofrenda
            $reunion_id = $this->reunionModel->registrarReunion($datos_reunion, $datos_ofrenda);
            
            // Actualizar promedio de asistentes en célula
            $estadisticas = $this->celulaModel->obtenerEstadisticas($celula_id);
            $this->celulaModel->actualizar($celula_id, [
                'cantidad_promedio_asistentes' => (int)$estadisticas['promedio_asistentes']
            ]);
            
            // Crear notificación para líder de área
            $this->crearNotificacionLiderArea($celula['lider_area_id'], 'Nuevo reporte de célula', 
                "Se registró un reporte de la célula {$celula['nombre']}");
            
            $this->conexion->commit();
            
            return [
                'exito' => true,
                'mensaje' => 'Reunión registrada exitosamente',
                'reunion_id' => $reunion_id
            ];
            
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener reuniones de líder (para reportes pendientes)
     */
    public function obtenerReunionesLider($lider_id, $estado = 'pendiente') {
        $sql = "SELECT c.*, 
                       COALESCE((SELECT MAX(fecha_reunion) FROM reuniones WHERE celula_id = c.id), NULL) as ultima_reunion
                FROM celulas c
                WHERE c.lider_id = :lider_id
                AND c.estado = 'activa'";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':lider_id', $lider_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $celulas = $stmt->fetchAll();
        
        // Filtrar por estado
        $resultado = [];
        foreach ($celulas as $celula) {
            if ($estado == 'pendiente') {
                // Si no hay reunión hoy
                if (!$celula['ultima_reunion'] || date('Y-m-d', strtotime($celula['ultima_reunion'])) != date('Y-m-d')) {
                    $resultado[] = $celula;
                }
            } else if ($estado == 'reportadas') {
                // Si hay reunión hoy
                if ($celula['ultima_reunion'] && date('Y-m-d', strtotime($celula['ultima_reunion'])) == date('Y-m-d')) {
                    $resultado[] = $celula;
                }
            } else {
                $resultado[] = $celula;
            }
        }
        
        return $resultado;
    }
    
    /**
     * Detectar dispositivo del usuario
     */
    private function detectarDispositivo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $user_agent)) {
            return 'Móvil';
        } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
            return 'Tablet';
        }
        return 'Computadora';
    }
    
    /**
     * Crear notificación para líder de área
     */
    private function crearNotificacionLiderArea($lider_area_id, $titulo, $mensaje) {
        if (!$lider_area_id) return false;
        
        $sql = "INSERT INTO notificaciones (usuario_destino_id, titulo, mensaje, tipo, fecha_creacion)
                VALUES (:usuario_id, :titulo, :mensaje, 'alerta_reporte', NOW())";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $lider_area_id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':mensaje', $mensaje);
        
        return $stmt->execute();
    }

    /**
     * Listar reuniones con filtros y paginación
     */
    public function listar($filtros = [], $pagina = 1, $limite = REGISTROS_POR_PAGINA, $orden = 'DESC') {
        $offset = ($pagina - 1) * $limite;
        $data = $this->reunionModel->listar($filtros, $limite, $offset, $orden);
        $total = $this->reunionModel->contar($filtros);
        return [
            'data' => $data,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }

    public function toggleRealizada($id, $realizada) {
        $ok = $this->reunionModel->toggleRealizada($id, $realizada);
        return [
            'exito' => (bool)$ok,
            'mensaje' => $ok ? 'Estado actualizado' : 'No se pudo actualizar'
        ];
    }
}
?>
