<?php
/**
 * MODELO - USUARIO
 * Gestiona usuarios, autenticación y membresía
 */

require_once 'Model.php';

class Usuario extends Model {
    protected $tabla = 'usuarios';
    
    /**
     * Obtener usuario por correo
     */
    public function obtenerPorCorreo($correo) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.correo = :correo";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Validar contraseña
     */
    public function validarPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Encriptar contraseña
     */
    public function encriptarPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, ENCRIPTACION_OPCIONES);
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function registrar($datos) {
        $datos['password_hash'] = $this->encriptarPassword($datos['password']);
        unset($datos['password']);
        
        // Generar codigo de membresia
        $datos['codigo_membresia'] = $this->generarCodigoMembresia();
        $datos['fecha_ingreso'] = date('Y-m-d');
        
        return $this->insertar($datos);
    }
    
    /**
     * Generar codigo unico de membresia
     */
    private function generarCodigoMembresia() {
        do {
            $codigo = 'MEM' . strtoupper(bin2hex(random_bytes(3)));
            
            // Verificar que no exista
            $sql = "SELECT id FROM usuarios WHERE codigo_membresia = :codigo";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            
        } while ($stmt->rowCount() > 0);
        
        return $codigo;
    }
    
    /**
     * Registrar intento de acceso (auditoría)
     */
    public function registrarIntentoAcceso($correo, $ip, $exitoso, $razon_fallo = null) {
        $sql = "INSERT INTO log_acceso (correo, ip_direccion, exitoso, razon_fallo, user_agent, fecha_hora) 
                VALUES (:correo, :ip, :exitoso, :razon, :user_agent, NOW())";
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':exitoso', $exitoso, PDO::PARAM_BOOL);
        $stmt->bindParam(':razon', $razon_fallo);
        $stmt->bindParam(':user_agent', $user_agent);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener servidores por área
     */
    public function obtenerServidoresPorArea($area_id) {
        $sql = "SELECT u.id, u.nombre_completo, u.correo, u.telefono, u.codigo_membresia
                FROM usuarios u
                JOIN servidores s ON u.id = s.usuario_id
                WHERE s.area_servicio_id = :area_id AND s.activo = TRUE
                ORDER BY u.nombre_completo";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar intentos fallidos del usuario
     */
    public function contarIntentosFallidos($correo, $horas = 1) {
        $sql = "SELECT COUNT(*) as total FROM log_acceso 
                WHERE correo = :correo AND exitoso = FALSE 
                AND fecha_hora > DATE_SUB(NOW(), INTERVAL :horas HOUR)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'];
    }
    
    /**
     * Bloquear usuario temporalmente
     */
    public function bloquearTemporalmente($usuario_id, $minutos = 15) {
        $fecha_bloqueo = date('Y-m-d H:i:s', strtotime("+{$minutos} minutes"));
        
        $sql = "UPDATE usuarios SET bloqueado_hasta = :fecha_bloqueo WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':fecha_bloqueo', $fecha_bloqueo);
        $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
?>
