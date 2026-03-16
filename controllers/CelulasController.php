<?php
/**
 * CONTROLADOR - CÉLULAS
 * CRUD y utilidades para gestión de células
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Celula.php';

class CelulasController {
    private $celulaModel;
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->celulaModel = new Celula($conexion);
    }
    
    /**
     * Listar con paginación y filtros
     */
    public function listar($filtros = [], $pagina = 1, $limite = REGISTROS_POR_PAGINA) {
        $data = $this->celulaModel->listar($limite, $pagina, $filtros);
        $total = $this->celulaModel->contarFiltrado($filtros);
        
        return [
            'data' => $data,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite
        ];
    }
    
    public function obtener($id) {
        return $this->celulaModel->obtenerConDetalles($id);
    }
    
    public function crear($datos, $usuario_id) {
        $validacion = $this->validarDatos($datos);
        if (!$validacion['exito']) {
            return $validacion;
        }
        
        $payload = $this->mapearDatos($datos, true);
        $payload['fecha_creacion'] = date('Y-m-d H:i:s');
        $payload['fecha_modificacion'] = date('Y-m-d H:i:s');
        
        try {
            $id = $this->celulaModel->insertar($payload);
            if ($id) {
                return ['exito' => true, 'id' => $id, 'mensaje' => 'Célula creada'];
            }
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
        
        return ['exito' => false, 'mensaje' => 'No se pudo crear la célula'];
    }
    
    public function actualizar($id, $datos) {
        $datos['id'] = $id;
        $validacion = $this->validarDatos($datos, false);
        if (!$validacion['exito']) {
            return $validacion;
        }
        
        $payload = $this->mapearDatos($datos, false);
        $payload['fecha_modificacion'] = date('Y-m-d H:i:s');
        
        $exito = $this->celulaModel->actualizar($id, $payload);
        return [
            'exito' => (bool)$exito,
            'mensaje' => $exito ? 'Célula actualizada' : 'No se pudo actualizar la célula'
        ];
    }
    
    public function eliminar($id) {
        // Baja lógica: se marca como inactiva para conservar historial y relaciones
        $payload = [
            'estado' => 'inactiva',
            'fecha_modificacion' => date('Y-m-d H:i:s')
        ];
        $exito = $this->celulaModel->actualizar($id, $payload);
        return [
            'exito' => (bool)$exito,
            'mensaje' => $exito ? 'Célula desactivada' : 'No se pudo desactivar la célula'
        ];
    }
    
    public function obtenerOpcionesFormulario() {
        return [
            'lideres' => $this->celulaModel->obtenerLideresDisponibles(),
            'areas' => $this->celulaModel->obtenerAreas(),
            'usuarios' => $this->celulaModel->obtenerUsuariosActivos(),
            'estados' => array_keys($GLOBALS['ESTADOS_CELULA'] ?? ['activa' => 'Activa', 'pausada' => 'Pausada', 'inactiva' => 'Inactiva']),
            'dias' => ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo']
        ];
    }
    
    /**
     * Validar datos mínimos requeridos
     */
    private function validarDatos($datos, $es_creacion = true) {
        $requeridos = ['nombre', 'lider_id', 'area_servicio_id', 'direccion', 'dia_semana', 'hora_inicio'];
        
        foreach ($requeridos as $campo) {
            if (empty($datos[$campo])) {
                return ['exito' => false, 'mensaje' => "El campo {$campo} es obligatorio"];
            }
        }

        // Validar formato de día
        $dias_validos = ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'];
        if (!in_array($datos['dia_semana'], $dias_validos)) {
            return ['exito' => false, 'mensaje' => 'Día de la semana inválido'];
        }

        // Validar formato de hora HH:MM
        if (!preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $datos['hora_inicio'])) {
            return ['exito' => false, 'mensaje' => 'Hora inválida, use formato HH:MM'];
        }

        // Validar unicidad de nombre
        if ($this->celulaModel->existeNombre($datos['nombre'], $es_creacion ? null : ($datos['id'] ?? null))) {
            return ['exito' => false, 'mensaje' => 'Ya existe una célula con ese nombre'];
        }

        // Validar líder responsable (activo y rol permitido)
        if (!$this->validarLiderResponsable($datos['lider_id'])) {
            return ['exito' => false, 'mensaje' => 'Líder no válido o inactivo'];
        }

        // Validar anfitrión si se envía (activo)
        if (!empty($datos['anfitrion_id']) && !$this->validarAnfitrion($datos['anfitrion_id'])) {
            return ['exito' => false, 'mensaje' => 'Anfitrión no válido o inactivo'];
        }

        // No permitir líder igual a anfitrión
        if (!empty($datos['anfitrion_id']) && (int)$datos['anfitrion_id'] === (int)$datos['lider_id']) {
            return ['exito' => false, 'mensaje' => 'El líder y el anfitrión deben ser diferentes'];
        }
        
        return ['exito' => true];
    }
    
    /**
     * Mapea y normaliza datos del request a columnas de BD
     */
    private function mapearDatos($datos, $es_creacion) {
        return [
            'nombre' => trim($datos['nombre']),
            'lider_id' => (int)$datos['lider_id'],
            'lider_area_id' => !empty($datos['lider_area_id']) ? (int)$datos['lider_area_id'] : null,
            'anfitrion_id' => !empty($datos['anfitrion_id']) ? (int)$datos['anfitrion_id'] : null,
            'area_servicio_id' => (int)$datos['area_servicio_id'],
            'direccion' => trim($datos['direccion']),
            'zona' => $datos['zona'] ?? null,
            'dia_semana' => $datos['dia_semana'],
            'hora_inicio' => $datos['hora_inicio'],
            'estado' => $datos['estado'] ?? 'activa',
            'cantidad_promedio_asistentes' => isset($datos['cantidad_promedio_asistentes'])
                ? (int)$datos['cantidad_promedio_asistentes']
                : (isset($datos['cantidad_promedio']) ? (int)$datos['cantidad_promedio'] : 0)
        ];
    }

    private function validarLiderResponsable($usuario_id) {
        $sql = "SELECT id FROM usuarios WHERE id = :id AND activo = 1 AND rol_id IN (2,3)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetch();
    }

    private function validarAnfitrion($usuario_id) {
        $sql = "SELECT id FROM usuarios WHERE id = :id AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetch();
    }
}
?>
