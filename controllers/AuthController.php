<?php
/**
 * CONTROLADOR - AUTENTICACIÓN
 * Gestiona login, logout y sesiones
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;
    
    public function __construct($conexion) {
        $this->usuarioModel = new Usuario($conexion);
    }
    
    /**
     * Procesar login
     */
    public function login($correo, $password, $ip) {
        try {
            // Validar campos
            if (empty($correo) || empty($password)) {
                return ['exito' => false, 'mensaje' => 'Correo y contraseña son requeridos'];
            }
            
            // Buscar usuario
            $usuario = $this->usuarioModel->obtenerPorCorreo($correo);
            
            if (!$usuario) {
                $this->usuarioModel->registrarIntentoAcceso($correo, $ip, false, 'Usuario no encontrado');
                return ['exito' => false, 'mensaje' => 'Credenciales inválidas'];
            }
            
            // Verificar si está bloqueado temporalmente
            if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
                return ['exito' => false, 'mensaje' => 'Cuenta bloqueada temporalmente. Intenta más tarde.'];
            }
            
            // Validar contraseña
            if (!$this->usuarioModel->validarPassword($password, $usuario['password_hash'])) {
                $intentos = $this->usuarioModel->contarIntentosFallidos($correo, 1);
                
                if ($intentos >= MAX_LOGIN_INTENTOS - 1) {
                    $this->usuarioModel->bloquearTemporalmente($usuario['id'], BLOQUEO_MINUTOS);
                    $this->usuarioModel->registrarIntentoAcceso($correo, $ip, false, 'Demasiados intentos fallidos');
                    return ['exito' => false, 'mensaje' => 'Cuenta bloqueada por seguridad'];
                }
                
                $this->usuarioModel->registrarIntentoAcceso($correo, $ip, false, 'Contraseña incorrecta');
                return ['exito' => false, 'mensaje' => 'Credenciales inválidas'];
            }
            
            // Validar que usuario esté activo
            if (!$usuario['activo']) {
                return ['exito' => false, 'mensaje' => 'Usuario inactivo'];
            }
            
            // Login exitoso - crear sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['rol'] = $usuario['rol_nombre']; // usar slug de rol
            $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
            $_SESSION['ultimo_acceso'] = time();
            $_SESSION['codigo_membresia'] = $usuario['codigo_membresia'];
            
            // Actualizar último acceso
            $this->usuarioModel->actualizar($usuario['id'], [
                'ultimo_acceso' => date('Y-m-d H:i:s'),
                'ip_registro' => $ip
            ]);
            
            // Registrar intento exitoso
            $this->usuarioModel->registrarIntentoAcceso($correo, $ip, true);
            
            return [
                'exito' => true,
                'mensaje' => 'Login exitoso',
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre_completo'],
                    'rol' => $usuario['rol_nombre']
                ]
            ];
            
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
    
    /**
     * Procesar logout
     */
    public function logout($usuario_id) {
        try {
            // Registrar en auditoría
            $sql = "INSERT INTO auditoria (usuario_id, accion, tabla_afectada, fecha_hora)
                    VALUES (:usuario_id, 'logout', 'usuarios', NOW())";
            
            // Destruir sesión
            session_destroy();
            
            return ['exito' => true, 'mensaje' => 'Sesión cerrada'];
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => 'Error al cerrar sesión'];
        }
    }
    
    /**
     * Validar sesión activa
     */
    public function validarSesion() {
        return isset($_SESSION['usuario_id']);
    }
}
?>
