<?php
/**
 * MODELO BASE - Proporciona métodos comunes a todos los modelos
 */

class Model {
    protected $conexion;
    protected $tabla;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener todos los registros
     */
    public function obtenerTodos($limite = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->tabla}";
        
        if ($limite) {
            $sql .= " LIMIT :limite OFFSET :offset";
        }
        
        $stmt = $this->conexion->prepare($sql);
        
        if ($limite) {
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM {$this->tabla} WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Insertar registro
     */
    public function insertar($datos) {
        $columnas = implode(', ', array_keys($datos));
        $placeholders = ':' . implode(', :', array_keys($datos));
        
        $sql = "INSERT INTO {$this->tabla} ({$columnas}) VALUES ({$placeholders})";
        $stmt = $this->conexion->prepare($sql);
        
        foreach ($datos as $clave => $valor) {
            $stmt->bindValue(':' . $clave, $valor);
        }
        
        return $stmt->execute() ? $this->conexion->lastInsertId() : false;
    }
    
    /**
     * Actualizar registro
     */
    public function actualizar($id, $datos) {
        $asignaciones = [];
        
        foreach (array_keys($datos) as $clave) {
            $asignaciones[] = "{$clave} = :{$clave}";
        }
        
        $sql = "UPDATE {$this->tabla} SET " . implode(', ', $asignaciones) . " WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        foreach ($datos as $clave => $valor) {
            $stmt->bindValue(':' . $clave, $valor);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar registro
     */
    public function eliminar($id) {
        $sql = "DELETE FROM {$this->tabla} WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Contar registros
     */
    public function contar($condicion = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabla}";
        
        if ($condicion) {
            $sql .= " WHERE " . $condicion;
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'];
    }
}
?>
