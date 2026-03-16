<?php
/**
 * CONTROLADOR - OFRENDA
 * Gestiona control de ofrendas y estados
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Ofrenda.php';
require_once __DIR__ . '/../models/Reunion.php';

class OfrendaController {
    private $ofrendaModel;
    private $reunionModel;
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->ofrendaModel = new Ofrenda($conexion);
        $this->reunionModel = new Reunion($conexion);
    }

    public function listar($filtros = [], $pagina = 1, $limite = 20) {
        return $this->ofrendaModel->listar($filtros, $pagina, $limite);
    }

    public function obtener($id) {
        $dato = $this->ofrendaModel->obtenerDetalle($id);
        if (!$dato) return ['exito'=>false,'mensaje'=>'Ofrenda no encontrada'];
        return ['exito'=>true,'data'=>$dato];
    }

    public function registrar($datos, $usuario_id) {
        if (empty($datos['celula_id']) || empty($datos['monto'])) {
            return ['exito'=>false,'mensaje'=>'Célula y monto son obligatorios'];
        }
        if (empty($datos['reunion_id'])) {
            return ['exito'=>false,'mensaje'=>'Debes indicar la reunión asociada'];
        }
        $insert = [
            'reunion_id' => $datos['reunion_id'],
            'monto' => $datos['monto'],
            'estado' => 'reportada',
            'fecha_reporte' => $datos['fecha_reporte'] ?? date('Y-m-d'),
            'lider_reporta_id' => $usuario_id,
            'notas' => $datos['notas'] ?? null
        ];
        $id = $this->ofrendaModel->registrar($insert);
        if (!$id) return ['exito'=>false,'mensaje'=>'No se pudo registrar la ofrenda'];
        return ['exito'=>true,'id'=>$id];
    }

    public function historial($id) {
        $hist = $this->ofrendaModel->obtenerHistorial($id);
        return ['exito'=>true,'data'=>$hist];
    }

    public function eliminar($id) {
        return $this->ofrendaModel->eliminar($id);
    }
    
    /**
     * Cambiar estado de ofrenda (Reportada -> Recibida -> Conciliada)
     */
    public function cambiarEstado($ofrenda_id, $nuevo_estado, $usuario_id) {
        try {
            // Validar estado
            $estados_validos = ['reportada', 'recibida', 'conciliada'];
            if (!in_array($nuevo_estado, $estados_validos)) {
                return ['exito' => false, 'mensaje' => 'Estado inválido'];
            }
            if (!$ofrenda_id) {
                return ['exito' => false, 'mensaje' => 'ID requerido'];
            }
            
            // Cambiar estado y registrar auditoría
            $this->ofrendaModel->cambiarEstado($ofrenda_id, $nuevo_estado, $usuario_id);
            
            return [
                'exito' => true,
                'mensaje' => 'Estado actualizado correctamente',
                'nuevo_estado' => $nuevo_estado
            ];
            
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener informe de ofrendas sin confirmar
     */
    public function obtenerInformeOfrendasPendientes() {
        $ofrendas_pendientes = $this->ofrendaModel->obtenerPendientes();
        
        $resumen = [
            'total_pendientes' => count($ofrendas_pendientes),
            'monto_total_pendiente' => 0,
            'reportadas' => 0,
            'recibidas' => 0,
            'por_dias' => [
                'menos_3_dias' => 0,
                'mas_3_menos_7_dias' => 0,
                'mas_7_dias' => 0
            ]
        ];
        
        foreach ($ofrendas_pendientes as $ofrenda) {
            $resumen['monto_total_pendiente'] += $ofrenda['monto'];
            
            if ($ofrenda['estado'] == 'reportada') {
                $resumen['reportadas']++;
            } elseif ($ofrenda['estado'] == 'recibida') {
                $resumen['recibidas']++;
            }
            
            if ($ofrenda['dias_pendiente'] <= 3) {
                $resumen['por_dias']['menos_3_dias']++;
            } elseif ($ofrenda['dias_pendiente'] <= 7) {
                $resumen['por_dias']['mas_3_menos_7_dias']++;
            } else {
                $resumen['por_dias']['mas_7_dias']++;
            }
        }
        
        return [
            'resumen' => $resumen,
            'detalles' => $ofrendas_pendientes
        ];
    }
    
    /**
     * Obtener reporte de ofrendas por período
     */
    public function obtenerReportePeriodo($fecha_inicio, $fecha_fin) {
        try {
            // Validar fechas
            if (!$this->validarFecha($fecha_inicio) || !$this->validarFecha($fecha_fin)) {
                return ['exito' => false, 'mensaje' => 'Formato de fecha inválido'];
            }
            
            $ofrendas = $this->ofrendaModel->obtenerPorPeriodo($fecha_inicio, $fecha_fin);
            
            // Calcular totales
            $total_monto = 0;
            $estados_conteo = ['reportada' => 0, 'recibida' => 0, 'conciliada' => 0];
            
            foreach ($ofrendas as $ofrenda) {
                $total_monto += $ofrenda['monto'];
                if (isset($ofrenda['estado'])) {
                    $estados_conteo[$ofrenda['estado']]++;
                }
            }
            
            return [
                'exito' => true,
                'periodo' => "$fecha_inicio a $fecha_fin",
                'total_monto' => $total_monto,
                'moneda' => MONEDA,
                'total_movimientos' => count($ofrendas),
                'estados_conteo' => $estados_conteo,
                'detalles' => $ofrendas
            ];
            
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener dashboard de tesorero
     */
    public function obtenerDashboardTesorero() {
        return [
            'ofrendas_pendientes' => $this->obtenerInformeOfrendasPendientes(),
            'ofrendas_por_area_mes' => $this->ofrendaModel->obtenerTotalPorArea(
                date('Y-m-01'),
                date('Y-m-t')
            ),
            'ofrendas_por_area_mes_anterior' => $this->ofrendaModel->obtenerTotalPorArea(
                date('Y-m-01', strtotime('last month')),
                date('Y-m-t', strtotime('last month'))
            )
        ];
    }
    
    /**
     * Validar formato de fecha
     */
    private function validarFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    
    /**
     * Generar reconciliación de ofrenda
     */
    public function reconciliar($ofrenda_id, $monto_recibido, $usuario_id, $notas = '') {
        try {
            // Obtener ofrenda
            $sql = "SELECT * FROM ofrendas WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $ofrenda_id);
            $stmt->execute();
            $ofrenda = $stmt->fetch();
            
            if (!$ofrenda) {
                return ['exito' => false, 'mensaje' => 'Ofrenda no encontrada'];
            }
            
            // Calcular discrepancia
            $discrepancia = $ofrenda['monto'] - $monto_recibido;
            
            // Actualizar ofrenda
            $datos_actualizacion = [
                'estado' => 'conciliada',
                'usuario_concilia_id' => $usuario_id,
                'fecha_conciliacion' => date('Y-m-d H:i:s'),
                'descrepancia' => $discrepancia != 0 ? $discrepancia : null,
                'notas' => $notas
            ];
            
            $this->ofrendaModel->actualizar($ofrenda_id, $datos_actualizacion);
            
            // Si hay discrepancia, crear alerta
            if ($discrepancia != 0) {
                $sql_alerta = "INSERT INTO notificaciones (usuario_destino_id, titulo, mensaje, tipo, fecha_creacion)
                              SELECT u.id, 'Discrepancia en ofrenda', 
                                     CONCAT('Discrepancia de ', ABS(:descrepancia), ' ', '" . MONEDA . "' , ' en ofrenda'),
                                     'otro', NOW()
                              FROM usuarios u
                              WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'pastor')";
                
                $stmt_alerta = $this->conexion->prepare($sql_alerta);
                $stmt_alerta->bindParam(':descrepancia', $discrepancia);
                $stmt_alerta->execute();
            }
            
            return [
                'exito' => true,
                'mensaje' => 'Ofrenda conciliada',
                'discrepancia' => $discrepancia,
                'monto_reportado' => $ofrenda['monto'],
                'monto_recibido' => $monto_recibido
            ];
            
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }
}
?>
