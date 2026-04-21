-- ========================================================================
-- TCAD CELULAS - ESQUEMA UNIFICADO
-- Archivo unico: tcad_celulas_db.sql
-- ========================================================================
-- Este script consolida la estructura de la BD en un solo documento.
-- Incluye correcciones funcionales y observaciones tecnicas.
--
-- Correcciones aplicadas:
-- 1) Se estandariza "servidores" para multiples areas por usuario:
--    UNIQUE (usuario_id, area_servicio_id)
-- 2) Se usa columna "dui" (alineada con el backend actual).
-- 3) Se deja una sola ofrenda por reunion con UNIQUE (reunion_id).
-- 4) Se eliminan definers en vistas para portabilidad entre entornos.
-- 5) Se estandarizan dias sin tildes en ENUM (Miercoles, Sabado).
--
-- Observaciones:
-- - Si usas scripts viejos de datos, ajusta textos con tildes en filtros
--   exactos (ej: "Jovenes" vs "Jovenes" con tilde en query literal).
-- - La columna "descrepancia" se mantiene asi por compatibilidad con codigo.
--   (Recomendado futuro: renombrar a "discrepancia" junto al backend).
-- ========================================================================

SET NAMES utf8mb4;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS tcad_celulas;
CREATE DATABASE tcad_celulas
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tcad_celulas;

-- ========================================================================
-- TABLA: roles
-- ========================================================================
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    nivel_acceso INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_roles_nombre (nombre),
    INDEX idx_roles_nivel (nivel_acceso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: usuarios
-- ========================================================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(120) NOT NULL,
    telefono VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    codigo_membresia CHAR(10) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME,
    ip_registro VARCHAR(45),
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_correo (correo),
    UNIQUE KEY uk_usuarios_codigo_membresia (codigo_membresia),
    INDEX idx_usuarios_rol (rol_id),
    INDEX idx_usuarios_correo (correo),
    INDEX idx_usuarios_codigo (codigo_membresia),
    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (rol_id) REFERENCES roles(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: areas_servicio
-- ========================================================================
CREATE TABLE areas_servicio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    lider_id INT,
    cantidad_servidores INT DEFAULT 0,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_areas_nombre (nombre),
    INDEX idx_areas_lider (lider_id),
    INDEX idx_areas_activa (activa),
    CONSTRAINT fk_areas_lider
        FOREIGN KEY (lider_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: servidores
-- ========================================================================
CREATE TABLE servidores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    area_servicio_id INT NOT NULL,
    dui VARCHAR(20) DEFAULT NULL,
    genero ENUM('M', 'F', 'Otro'),
    fecha_nacimiento DATE,
    bautizado BOOLEAN DEFAULT FALSE,
    fecha_bautizo DATE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_ingreso DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_servidores_usuario_area (usuario_id, area_servicio_id),
    UNIQUE KEY uk_servidores_dui (dui),
    INDEX idx_servidores_area (area_servicio_id),
    INDEX idx_servidores_activo (activo),
    CONSTRAINT fk_servidores_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_servidores_area
        FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: celulas
-- ========================================================================
CREATE TABLE celulas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
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
    UNIQUE KEY uk_celulas_nombre (nombre),
    INDEX idx_celulas_lider (lider_id),
    INDEX idx_celulas_lider_area (lider_area_id),
    INDEX idx_celulas_anfitrion (anfitrion_id),
    INDEX idx_celulas_area (area_servicio_id),
    INDEX idx_celulas_estado (estado),
    CONSTRAINT fk_celulas_lider
        FOREIGN KEY (lider_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_celulas_lider_area
        FOREIGN KEY (lider_area_id) REFERENCES usuarios(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_celulas_anfitrion
        FOREIGN KEY (anfitrion_id) REFERENCES usuarios(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_celulas_area
        FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: reuniones
-- ========================================================================
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
    UNIQUE KEY uk_reuniones_celula_fecha (celula_id, fecha_reunion),
    INDEX idx_reuniones_celula (celula_id),
    INDEX idx_reuniones_fecha (fecha_reunion),
    INDEX idx_reuniones_lider_reporta (lider_reporta_id),
    CONSTRAINT fk_reuniones_celula
        FOREIGN KEY (celula_id) REFERENCES celulas(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_reuniones_lider_reporta
        FOREIGN KEY (lider_reporta_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: asistencias
-- ========================================================================
CREATE TABLE asistencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reunion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    asistio BOOLEAN DEFAULT TRUE,
    nombre_visitante VARCHAR(100),
    telefono_visitante VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_asistencias_reunion_usuario (reunion_id, usuario_id),
    INDEX idx_asistencias_reunion (reunion_id),
    INDEX idx_asistencias_usuario (usuario_id),
    CONSTRAINT fk_asistencias_reunion
        FOREIGN KEY (reunion_id) REFERENCES reuniones(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_asistencias_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: ofrendas
-- ========================================================================
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
    UNIQUE KEY uk_ofrendas_reunion (reunion_id),
    INDEX idx_ofrendas_estado (estado),
    INDEX idx_ofrendas_lider_reporta (lider_reporta_id),
    INDEX idx_ofrendas_usuario_recibe (usuario_recibe_id),
    INDEX idx_ofrendas_usuario_concilia (usuario_concilia_id),
    CONSTRAINT fk_ofrendas_reunion
        FOREIGN KEY (reunion_id) REFERENCES reuniones(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_ofrendas_lider_reporta
        FOREIGN KEY (lider_reporta_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_ofrendas_usuario_recibe
        FOREIGN KEY (usuario_recibe_id) REFERENCES usuarios(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_ofrendas_usuario_concilia
        FOREIGN KEY (usuario_concilia_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: delegaciones
-- ========================================================================
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
    INDEX idx_delegaciones_delegador (usuario_delegador_id),
    INDEX idx_delegaciones_delegado (usuario_delegado_id),
    INDEX idx_delegaciones_area (area_servicio_id),
    INDEX idx_delegaciones_celula (celula_id),
    CONSTRAINT fk_delegaciones_delegador
        FOREIGN KEY (usuario_delegador_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_delegaciones_delegado
        FOREIGN KEY (usuario_delegado_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_delegaciones_area
        FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_delegaciones_celula
        FOREIGN KEY (celula_id) REFERENCES celulas(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: materiales_estudio
-- ========================================================================
CREATE TABLE materiales_estudio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    area_servicio_id INT,
    celula_id INT,
    tipo ENUM('pdf', 'video', 'documento', 'presentacion', 'otro') NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    tamano_bytes INT,
    version INT DEFAULT 1,
    version_anterior_id INT,
    subido_por_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_materiales_area (area_servicio_id),
    INDEX idx_materiales_celula (celula_id),
    INDEX idx_materiales_tipo (tipo),
    INDEX idx_materiales_subido_por (subido_por_id),
    INDEX idx_materiales_version_anterior (version_anterior_id),
    CONSTRAINT fk_materiales_area
        FOREIGN KEY (area_servicio_id) REFERENCES areas_servicio(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_materiales_celula
        FOREIGN KEY (celula_id) REFERENCES celulas(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_materiales_subido_por
        FOREIGN KEY (subido_por_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_materiales_version_anterior
        FOREIGN KEY (version_anterior_id) REFERENCES materiales_estudio(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: notificaciones
-- ========================================================================
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
    INDEX idx_notificaciones_usuario (usuario_destino_id),
    INDEX idx_notificaciones_tipo (tipo),
    INDEX idx_notificaciones_leida (leida),
    CONSTRAINT fk_notificaciones_usuario
        FOREIGN KEY (usuario_destino_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: auditoria
-- ========================================================================
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
    INDEX idx_auditoria_usuario (usuario_id),
    INDEX idx_auditoria_accion (accion),
    INDEX idx_auditoria_tabla (tabla_afectada),
    INDEX idx_auditoria_fecha (fecha_hora),
    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: log_acceso
-- ========================================================================
CREATE TABLE log_acceso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(120),
    ip_direccion VARCHAR(45),
    exitoso BOOLEAN,
    razon_fallo VARCHAR(100),
    user_agent VARCHAR(255),
    dispositivo VARCHAR(100),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_acceso_correo (correo),
    INDEX idx_log_acceso_exitoso (exitoso),
    INDEX idx_log_acceso_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- TABLA: configuracion_sistema
-- ========================================================================
CREATE TABLE configuracion_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) NOT NULL,
    valor VARCHAR(255),
    descripcion TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'json') DEFAULT 'texto',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_configuracion_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- DATOS BASE (CATALOGO MINIMO)
-- ========================================================================
INSERT INTO roles (nombre, descripcion, nivel_acceso) VALUES
('pastor', 'Acceso total al sistema', 5),
('lider_area', 'Gestion de area de servicio', 3),
('lider_celula', 'Reporte de celula', 2),
('tesorero', 'Gestion de ofrendas', 4),
('servidor', 'Acceso limitado', 1);

INSERT INTO areas_servicio (nombre, descripcion) VALUES
('Jovenes', 'Ministerio de jovenes'),
('Multimedia', 'Produccion audiovisual y transmision'),
('Matrimonios', 'Ministerio para parejas'),
('Mujeres', 'Ministerio femenino'),
('Trafico', 'Organizacion de eventos'),
('Protocolo', 'Celebraciones y ceremonias'),
('Hombres', 'Ministerio masculino'),
('Celulas Familiares', 'Red de celulas en los hogares');

-- ========================================================================
-- VISTAS
-- ========================================================================
DROP VIEW IF EXISTS vw_celulas_detalle;
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

DROP VIEW IF EXISTS vw_servidores_por_area;
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

DROP VIEW IF EXISTS vw_ofrendas_pendientes;
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

-- ========================================================================
-- FIN
-- ========================================================================
