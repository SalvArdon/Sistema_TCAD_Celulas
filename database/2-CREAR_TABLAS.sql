USE tcad_celulas;

-- ================================================================
-- TABLA 1: ROLES (Sistema de Control de Permisos) - CREAR PRIMERO
-- ================================================================
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    nivel_acceso INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_nombre (nombre),
    INDEX idx_nivel (nivel_acceso)
);

INSERT INTO roles (nombre, descripcion, nivel_acceso) VALUES
('pastor', 'Acceso total al sistema', 5),
('lider_area', 'Gestion de area de servicio', 3),
('lider_celula', 'Reporte de celula', 2),
('tesorero', 'Gestion de ofrendas', 4),
('servidor', 'Acceso limitado', 1);

-- ================================================================
-- TABLA 2: USUARIOS
-- ================================================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(120) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    
    codigo_membresia CHAR(10) UNIQUE NOT NULL,
    fecha_ingreso DATE NOT NULL,
    
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME,
    ip_registro VARCHAR(45),
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_rol (rol_id),
    INDEX idx_correo (correo),
    INDEX idx_codigo_membresia (codigo_membresia),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA 3: AREAS DE SERVICIO
-- ================================================================
CREATE TABLE areas_servicio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    lider_id INT,
    cantidad_servidores INT DEFAULT 0,
    activa BOOLEAN DEFAULT TRUE,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_lider (lider_id),
    INDEX idx_activa (activa),
    FOREIGN KEY (lider_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

INSERT INTO areas_servicio (nombre, descripcion) VALUES
('Jovenes', 'Ministerio de jovenes'),
('Multimedia', 'Produccion audiovisual y transmision'),
('Matrimonios', 'Ministerio para parejas'),
('Mujeres', 'Ministerio femenino'),
('Trafico', 'Organizacion de eventos'),
('Protocolo', 'Celebraciones y ceremonias'),
('Hombres', 'Ministerio masculino'),
('Celulas Familiares', 'Red de celulas en los hogares');

-- ================================================================
-- TABLA 4: SERVIDORES
-- ================================================================
CREATE TABLE servidores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT UNIQUE NOT NULL,
    area_servicio_id INT NOT NULL,
    
    cedula VARCHAR(20) UNIQUE,
    genero ENUM('M', 'F', 'Otro'),
    fecha_nacimiento DATE,
    
    bautizado BOOLEAN DEFAULT FALSE,
    fecha_bautizo DATE,
    activo BOOLEAN DEFAULT TRUE,
    
    fecha_ingreso DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_usuario (usuario_id),
    INDEX idx_area (area_servicio_id),
    INDEX idx_activo (activo),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA 5: CELULAS
-- ================================================================
CREATE TABLE celulas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    lider_id INT NOT NULL,
    lider_area_id INT,
    anfitrion_id INT,
    area_servicio_id INT NOT NULL,
    
    direccion TEXT NOT NULL,
    zona VARCHAR(100),
    coordenadas VARCHAR(50),
    
    dia_semana ENUM('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    
    estado ENUM('activa', 'inactiva', 'pausada') DEFAULT 'activa',
    cantidad_promedio_asistentes INT DEFAULT 0,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_cierre DATE,
    
    INDEX idx_lider (lider_id),
    INDEX idx_lider_area (lider_area_id),
    INDEX idx_area (area_servicio_id),
    INDEX idx_estado (estado),
    FOREIGN KEY (lider_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (lider_area_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (anfitrion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA 6: REUNIONES
-- ================================================================
CREATE TABLE reuniones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    celula_id INT NOT NULL,
    fecha_reunion DATE NOT NULL,
    
    realizada BOOLEAN DEFAULT TRUE,
    motivo_cancelacion TEXT,
    
    cantidad_asistentes INT NOT NULL DEFAULT 0,
    cantidad_nuevos INT DEFAULT 0,
    
    lider_reporta_id INT NOT NULL,
    fecha_reporte DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_reporte VARCHAR(45),
    dispositivo VARCHAR(100),
    
    comentarios TEXT,
    temas_tratados TEXT,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_reunion_unica (celula_id, fecha_reunion),
    INDEX idx_celula (celula_id),
    INDEX idx_fecha (fecha_reunion),
    INDEX idx_lider_reporta (lider_reporta_id),
    FOREIGN KEY (celula_id) REFERENCES celulas(id) ON DELETE CASCADE,
    FOREIGN KEY (lider_reporta_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA 7: ASISTENCIAS
-- ================================================================
CREATE TABLE asistencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reunion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    asistio BOOLEAN DEFAULT TRUE,
    
    nombre_visitante VARCHAR(100),
    telefono_visitante VARCHAR(20),
    
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_reunion_usuario (reunion_id, usuario_id),
    INDEX idx_reunion (reunion_id),
    INDEX idx_usuario (usuario_id),
    FOREIGN KEY (reunion_id) REFERENCES reuniones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ================================================================
-- TABLA 8: OFRENDAS
-- ================================================================
CREATE TABLE ofrendas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reunion_id INT NOT NULL,
    
    monto DECIMAL(10, 2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    
    estado ENUM('reportada', 'recibida', 'conciliada') DEFAULT 'reportada',
    
    lider_reporta_id INT NOT NULL,
    usuario_recibe_id INT,
    usuario_concilia_id INT,
    
    fecha_reporte DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_recepcion DATETIME,
    fecha_conciliacion DATETIME,
    
    notas TEXT,
    descrepancia DECIMAL(10, 2),
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_reunion (reunion_id),
    INDEX idx_estado (estado),
    INDEX idx_lider_reporta (lider_reporta_id),
    INDEX idx_usuario_recibe (usuario_recibe_id),
    INDEX idx_conciliacion (usuario_concilia_id),
    FOREIGN KEY (reunion_id) REFERENCES reuniones(id) ON DELETE CASCADE,
    FOREIGN KEY (lider_reporta_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_recibe_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_concilia_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ================================================================
-- TABLA 9: DELEGACIONES
-- ================================================================
CREATE TABLE delegaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_delegador_id INT NOT NULL,
    usuario_delegado_id INT NOT NULL,
    area_servicio_id INT,
    celula_id INT,
    rol_nuevo VARCHAR(50),
    
    activa BOOLEAN DEFAULT TRUE,
    
    fecha_delegacion DATE DEFAULT CURRENT_DATE,
    fecha_termino DATE,
    razon TEXT,
    
    observaciones TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_delegador (usuario_delegador_id),
    INDEX idx_delegado (usuario_delegado_id),
    INDEX idx_area (area_servicio_id),
    INDEX idx_celula (celula_id),
    FOREIGN KEY (usuario_delegador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_delegado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id) ON DELETE SET NULL,
    FOREIGN KEY (celula_id) REFERENCES celulas(id) ON DELETE SET NULL
);

-- ================================================================
-- TABLA 10: MATERIALES DE ESTUDIO
-- ================================================================
CREATE TABLE materiales_estudio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    
    area_servicio_id INT,
    celula_id INT,
    tipo ENUM('pdf', 'video', 'documento', 'presentacion', 'otro') NOT NULL,
    
    ruta_archivo VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    tamaño_bytes INT,
    
    version INT DEFAULT 1,
    version_anterior_id INT,
    
    subido_por_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_area (area_servicio_id),
    INDEX idx_celula (celula_id),
    INDEX idx_tipo (tipo),
    INDEX idx_subido_por (subido_por_id),
    FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id) ON DELETE SET NULL,
    FOREIGN KEY (celula_id) REFERENCES celulas(id) ON DELETE SET NULL,
    FOREIGN KEY (subido_por_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (version_anterior_id) REFERENCES materiales_estudio(id) ON DELETE SET NULL
);

-- ================================================================
-- TABLA 11: NOTIFICACIONES
-- ================================================================
CREATE TABLE notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_destino_id INT NOT NULL,
    
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('delegacion', 'material', 'alerta_reporte', 'ofrenda_pendiente', 'otro') NOT NULL,
    
    referencia_tabla VARCHAR(50),
    referencia_id INT,
    
    leida BOOLEAN DEFAULT FALSE,
    enviada_email BOOLEAN DEFAULT FALSE,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura DATETIME,
    
    INDEX idx_usuario (usuario_destino_id),
    INDEX idx_tipo (tipo),
    INDEX idx_leida (leida),
    FOREIGN KEY (usuario_destino_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ================================================================
-- TABLA 12: AUDITORIA
-- ================================================================
CREATE TABLE auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    usuario_id INT NOT NULL,
    
    accion ENUM('insertar', 'actualizar', 'eliminar', 'login', 'logout') NOT NULL,
    tabla_afectada VARCHAR(100) NOT NULL,
    registro_id INT,
    
    valor_anterior JSON,
    valor_nuevo JSON,
    
    ip_usuario VARCHAR(45),
    user_agent VARCHAR(255),
    dispositivo VARCHAR(100),
    
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_fecha (fecha_hora),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- ================================================================
-- TABLA 13: LOG DE ACCESO
-- ================================================================
CREATE TABLE log_acceso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(120),
    ip_direccion VARCHAR(45),
    exitoso BOOLEAN,
    razon_fallo VARCHAR(100),
    user_agent VARCHAR(255),
    dispositivo VARCHAR(100),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_correo (correo),
    INDEX idx_exitoso (exitoso),
    INDEX idx_fecha (fecha_hora)
);

-- ================================================================
-- TABLA 14: CONFIGURACION DEL SISTEMA
-- ================================================================
CREATE TABLE configuracion_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor VARCHAR(255),
    descripcion TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'json') DEFAULT 'texto',
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_clave (clave)
);

-- ================================================================
-- VISTAS UTILES
-- ================================================================

CREATE VIEW vw_celulas_detalle AS
SELECT 
    c.id,
    c.nombre,
    c.direccion,
    c.dia_semana,
    c.hora_inicio,
    c.estado,
    c.cantidad_promedio_asistentes,
    u_lider.nombre_completo AS lider_nombre,
    u_lider.telefono AS lider_telefono,
    u_anfitrion.nombre_completo AS anfitrion_nombre,
    a.nombre AS area_servicio,
    c.fecha_creacion
FROM celulas c
LEFT JOIN usuarios u_lider ON c.lider_id = u_lider.id
LEFT JOIN usuarios u_anfitrion ON c.anfitrion_id = u_anfitrion.id
LEFT JOIN areas_servicio a ON c.area_servicio_id = a.id;

CREATE VIEW vw_servidores_por_area AS
SELECT 
    a.id,
    a.nombre AS area,
    COUNT(s.id) AS cantidad_servidores,
    u.nombre_completo AS lider_area
FROM areas_servicio a
LEFT JOIN servidores s ON a.id = s.area_servicio_id AND s.activo = TRUE
LEFT JOIN usuarios u ON a.lider_id = u.id
GROUP BY a.id, a.nombre, u.nombre_completo;

CREATE VIEW vw_ofrendas_pendientes AS
SELECT 
    o.id,
    r.fecha_reunion,
    c.nombre AS celula,
    o.monto,
    o.estado,
    u.nombre_completo AS lider,
    u.telefono,
    DATEDIFF(NOW(), o.fecha_reporte) AS dias_pendiente
FROM ofrendas o
JOIN reuniones r ON o.reunion_id = r.id
JOIN celulas c ON r.celula_id = c.id
JOIN usuarios u ON o.lider_reporta_id = u.id
WHERE o.estado != 'conciliada'
ORDER BY o.fecha_reporte ASC;

-- ================================================================
-- FIN - BASE DE DATOS LISTA PARA USO
-- ================================================================
