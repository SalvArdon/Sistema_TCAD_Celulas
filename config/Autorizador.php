<?php
/**
 * AUTORIZADOR - Manejo de permisos por rol
 */

class Autorizador {
    
    // Definir qué roles pueden acceder a cada módulo
    private static $permisos = [
        'ofrendas' => [1, 2, 4],  // pastor, lider_area, tesorero
        'celulas' => [1, 2, 3],   // pastor, lider_area, lider_celula
        'reuniones' => [1, 2, 3], // pastor, lider_area, lider_celula
        'servidores' => [1, 2],   // pastor, lider_area
        'liderazgo' => [1],       // solo pastor
        'materiales' => [1, 3],   // pastor, lider_celula
        'notificaciones' => [1],  // solo pastor
        'auditoria' => [1],       // solo pastor
        'reportes' => [1, 2, 4],  // pastor, lider_area, tesorero
    ];
    
    // Roles con sus IDs
    public static $roles = [
        1 => 'pastor',
        2 => 'lider_area',
        3 => 'lider_celula',
        4 => 'tesorero',
        5 => 'servidor'
    ];
    
    /**
     * Verificar si un usuario puede acceder a un módulo
     */
    public static function puedeAcceder($modulo) {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
            return false;
        }
        
        $rol_id = $_SESSION['rol'];
        
        // Si no hay permisos definidos para el módulo, no permitir
        if (!isset(self::$permisos[$modulo])) {
            return false;
        }
        
        // Verificar si el rol está en la lista de permisos
        return in_array($rol_id, self::$permisos[$modulo]);
    }
    
    /**
     * Obtener descripción del rol
     */
    public static function nombreRol($rol_id) {
        return self::$roles[$rol_id] ?? 'desconocido';
    }
    
    /**
     * Verificar si puede ejecutar una acción específica
     */
    public static function puedeEjecutar($accion) {
        $rol_id = $_SESSION['rol'] ?? 0;
        
        // Acciones permitidas por rol
        $acciones = [
            'crear_celula' => [1, 2],
            'editar_celula' => [1, 2, 3],
            'crear_reunion' => [2, 3],
            'reportar_ofrenda' => [2, 3, 4],
            'recibir_ofrenda' => [4],
            'conciliar_ofrenda' => [4, 1],
            'crear_servidor' => [1, 2],
        ];
        
        if (!isset($acciones[$accion])) {
            return false;
        }
        
        return in_array($rol_id, $acciones[$accion]);
    }
}
?>
