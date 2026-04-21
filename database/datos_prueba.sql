-- ================================================================
-- SCRIPT DE DATOS DE PRUEBA - TCAD CELULAS
-- Compatible con: database/tcad_celulas_db.sql
-- ================================================================
-- Este script asume que ya ejecutaste tcad_celulas_db.sql.
-- Incluye inserciones idempotentes para evitar errores por duplicados.

USE tcad_celulas;

START TRANSACTION;

-- Password para todos los usuarios de prueba: Admin123!
SET @password_hash = '$argon2id$v=19$m=65536,t=4,p=3$ZXhYd3FSWC9MSE9pUHd3NQ$cVUToIarWh1/pysMeVMJWkanN0i25xMEr1CoLf7fXyM';

-- ================================================================
-- USUARIOS
-- ================================================================
INSERT INTO usuarios (
    nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo
) VALUES
(
    'Pastor Principal',
    'pastor@iglesia.com',
    '+503 7123-4567',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'pastor' LIMIT 1),
    'MEM000001',
    CURDATE(),
    TRUE
),
(
    'Maria Garcia - Lider Jovenes',
    'lider.jovenes@iglesia.com',
    '+503 7234-5678',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'lider_area' LIMIT 1),
    'MEM000002',
    CURDATE(),
    TRUE
),
(
    'Carlos Lopez - Lider Matrimonios',
    'lider.matrimonios@iglesia.com',
    '+503 7345-6789',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'lider_area' LIMIT 1),
    'MEM000003',
    CURDATE(),
    TRUE
),
(
    'Juan Perez - Lider Celula Centro',
    'juan.perez@iglesia.com',
    '+503 7456-7890',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'lider_celula' LIMIT 1),
    'MEM000004',
    CURDATE(),
    TRUE
),
(
    'Ana Martinez - Lider Celula San Benito',
    'ana.martinez@iglesia.com',
    '+503 7567-8901',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'lider_celula' LIMIT 1),
    'MEM000005',
    CURDATE(),
    TRUE
),
(
    'David Rodriguez - Tesorero',
    'tesorero@iglesia.com',
    '+503 7678-9012',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'tesorero' LIMIT 1),
    'MEM000006',
    CURDATE(),
    TRUE
),
(
    'Roberto Garcia',
    'roberto@iglesia.com',
    '+503 7789-0123',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'servidor' LIMIT 1),
    'MEM000007',
    CURDATE(),
    TRUE
),
(
    'Marta Sanchez',
    'marta@iglesia.com',
    '+503 7890-1234',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'servidor' LIMIT 1),
    'MEM000008',
    CURDATE(),
    TRUE
),
(
    'Pedro Flores',
    'pedro@iglesia.com',
    '+503 7901-2345',
    @password_hash,
    (SELECT id FROM roles WHERE nombre = 'servidor' LIMIT 1),
    'MEM000009',
    CURDATE(),
    TRUE
)
ON DUPLICATE KEY UPDATE
    nombre_completo = VALUES(nombre_completo),
    telefono = VALUES(telefono),
    password_hash = VALUES(password_hash),
    rol_id = VALUES(rol_id),
    activo = VALUES(activo);

-- ================================================================
-- LIDERES DE AREA
-- ================================================================
UPDATE areas_servicio a
JOIN usuarios u ON u.correo = 'lider.jovenes@iglesia.com'
SET a.lider_id = u.id
WHERE a.nombre = 'Jovenes';

UPDATE areas_servicio a
JOIN usuarios u ON u.correo = 'lider.matrimonios@iglesia.com'
SET a.lider_id = u.id
WHERE a.nombre = 'Matrimonios';

-- ================================================================
-- SERVIDORES
-- ================================================================
INSERT INTO servidores (
    usuario_id, area_servicio_id, dui, genero, fecha_nacimiento, bautizado, activo
)
SELECT
    u.id,
    a.id,
    x.dui,
    x.genero,
    x.fecha_nacimiento,
    x.bautizado,
    TRUE
FROM (
    SELECT 'roberto@iglesia.com' AS correo, 'Jovenes' AS area, '01234567-8' AS dui, 'M' AS genero, '1998-01-10' AS fecha_nacimiento, TRUE AS bautizado
    UNION ALL
    SELECT 'marta@iglesia.com', 'Matrimonios', '02345678-9', 'F', '1999-05-22', TRUE
    UNION ALL
    SELECT 'pedro@iglesia.com', 'Hombres', '03456789-0', 'M', '2000-07-15', TRUE
) x
JOIN usuarios u ON u.correo = x.correo
JOIN areas_servicio a ON a.nombre = x.area
ON DUPLICATE KEY UPDATE
    genero = VALUES(genero),
    fecha_nacimiento = VALUES(fecha_nacimiento),
    bautizado = VALUES(bautizado),
    activo = VALUES(activo);

-- ================================================================
-- CELULAS
-- ================================================================
INSERT INTO celulas (
    nombre, lider_id, lider_area_id, anfitrion_id, area_servicio_id, direccion, zona, dia_semana, hora_inicio, estado, cantidad_promedio_asistentes
)
SELECT
    x.nombre,
    ul.id AS lider_id,
    ula.id AS lider_area_id,
    ua.id AS anfitrion_id,
    ar.id AS area_servicio_id,
    x.direccion,
    x.zona,
    x.dia_semana,
    x.hora_inicio,
    'activa' AS estado,
    x.promedio
FROM (
    SELECT
        'Celula Centro' AS nombre,
        'juan.perez@iglesia.com' AS lider_correo,
        'lider.jovenes@iglesia.com' AS lider_area_correo,
        'roberto@iglesia.com' AS anfitrion_correo,
        'Jovenes' AS area_nombre,
        'Calle Principal 123, Centro' AS direccion,
        'Centro' AS zona,
        'Lunes' AS dia_semana,
        '19:00:00' AS hora_inicio,
        12 AS promedio
    UNION ALL
    SELECT
        'Celula San Benito',
        'ana.martinez@iglesia.com',
        'lider.jovenes@iglesia.com',
        'marta@iglesia.com',
        'Jovenes',
        'Avenida Independencia 456, San Benito',
        'San Benito',
        'Miercoles',
        '19:30:00',
        15
) x
JOIN usuarios ul ON ul.correo = x.lider_correo
LEFT JOIN usuarios ula ON ula.correo = x.lider_area_correo
LEFT JOIN usuarios ua ON ua.correo = x.anfitrion_correo
JOIN areas_servicio ar ON ar.nombre = x.area_nombre
ON DUPLICATE KEY UPDATE
    lider_id = VALUES(lider_id),
    lider_area_id = VALUES(lider_area_id),
    anfitrion_id = VALUES(anfitrion_id),
    area_servicio_id = VALUES(area_servicio_id),
    direccion = VALUES(direccion),
    zona = VALUES(zona),
    dia_semana = VALUES(dia_semana),
    hora_inicio = VALUES(hora_inicio),
    estado = VALUES(estado),
    cantidad_promedio_asistentes = VALUES(cantidad_promedio_asistentes);

-- ================================================================
-- REUNIONES
-- ================================================================
INSERT INTO reuniones (
    celula_id, fecha_reunion, realizada, cantidad_asistentes, cantidad_nuevos, lider_reporta_id, comentarios, temas_tratados
)
SELECT
    c.id,
    x.fecha_reunion,
    TRUE,
    x.asistentes,
    x.nuevos,
    u.id,
    x.comentarios,
    x.temas
FROM (
    SELECT
        'Celula Centro' AS celula_nombre,
        DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS fecha_reunion,
        12 AS asistentes,
        1 AS nuevos,
        'juan.perez@iglesia.com' AS lider_correo,
        'Buena asistencia y participacion del grupo.' AS comentarios,
        'La gracia de Dios - Efesios 2:8-9' AS temas
    UNION ALL
    SELECT
        'Celula San Benito',
        DATE_SUB(CURDATE(), INTERVAL 3 DAY),
        14,
        2,
        'ana.martinez@iglesia.com',
        'Excelente reunion con testimonios de fe.',
        'Fe y obras - Santiago 2:26'
) x
JOIN celulas c ON c.nombre = x.celula_nombre
JOIN usuarios u ON u.correo = x.lider_correo
ON DUPLICATE KEY UPDATE
    realizada = VALUES(realizada),
    cantidad_asistentes = VALUES(cantidad_asistentes),
    cantidad_nuevos = VALUES(cantidad_nuevos),
    lider_reporta_id = VALUES(lider_reporta_id),
    comentarios = VALUES(comentarios),
    temas_tratados = VALUES(temas_tratados);

-- ================================================================
-- OFRENDAS
-- ================================================================
INSERT INTO ofrendas (
    reunion_id, monto, moneda, estado, lider_reporta_id, usuario_recibe_id, usuario_concilia_id,
    fecha_reporte, fecha_recepcion, fecha_conciliacion, notas, descrepancia
)
SELECT
    r.id,
    x.monto,
    'USD',
    x.estado,
    ul.id AS lider_reporta_id,
    ur.id AS usuario_recibe_id,
    uc.id AS usuario_concilia_id,
    x.fecha_reporte,
    x.fecha_recepcion,
    x.fecha_conciliacion,
    x.notas,
    x.descrepancia
FROM (
    SELECT
        'Celula Centro' AS celula_nombre,
        DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS fecha_reunion,
        125.50 AS monto,
        'reportada' AS estado,
        'juan.perez@iglesia.com' AS lider_correo,
        NULL AS recibe_correo,
        NULL AS concilia_correo,
        DATE_SUB(NOW(), INTERVAL 1 DAY) AS fecha_reporte,
        NULL AS fecha_recepcion,
        NULL AS fecha_conciliacion,
        'Pendiente de recepcion por tesoreria.' AS notas,
        NULL AS descrepancia
    UNION ALL
    SELECT
        'Celula San Benito',
        DATE_SUB(CURDATE(), INTERVAL 3 DAY),
        150.00,
        'conciliada',
        'ana.martinez@iglesia.com',
        'tesorero@iglesia.com',
        'tesorero@iglesia.com',
        DATE_SUB(NOW(), INTERVAL 3 DAY),
        DATE_SUB(NOW(), INTERVAL 3 DAY),
        DATE_SUB(NOW(), INTERVAL 2 DAY),
        'Conciliacion completa sin diferencias.',
        0.00
) x
JOIN celulas c ON c.nombre = x.celula_nombre
JOIN reuniones r ON r.celula_id = c.id AND r.fecha_reunion = x.fecha_reunion
JOIN usuarios ul ON ul.correo = x.lider_correo
LEFT JOIN usuarios ur ON ur.correo = x.recibe_correo
LEFT JOIN usuarios uc ON uc.correo = x.concilia_correo
ON DUPLICATE KEY UPDATE
    monto = VALUES(monto),
    estado = VALUES(estado),
    lider_reporta_id = VALUES(lider_reporta_id),
    usuario_recibe_id = VALUES(usuario_recibe_id),
    usuario_concilia_id = VALUES(usuario_concilia_id),
    fecha_reporte = VALUES(fecha_reporte),
    fecha_recepcion = VALUES(fecha_recepcion),
    fecha_conciliacion = VALUES(fecha_conciliacion),
    notas = VALUES(notas),
    descrepancia = VALUES(descrepancia);

-- ================================================================
-- DELEGACIONES
-- ================================================================
INSERT INTO delegaciones (
    usuario_delegador_id, usuario_delegado_id, area_servicio_id, celula_id, rol_nuevo, activa, fecha_delegacion, razon
)
SELECT
    udel.id,
    udeg.id,
    ar.id,
    NULL,
    NULL,
    TRUE,
    CURDATE(),
    x.razon
FROM (
    SELECT 'pastor@iglesia.com' AS delegador, 'lider.jovenes@iglesia.com' AS delegado, 'Jovenes' AS area_nombre, 'Delegacion liderazgo area Jovenes' AS razon
    UNION ALL
    SELECT 'pastor@iglesia.com', 'lider.matrimonios@iglesia.com', 'Matrimonios', 'Delegacion liderazgo area Matrimonios'
    UNION ALL
    SELECT 'lider.jovenes@iglesia.com', 'juan.perez@iglesia.com', 'Jovenes', 'Delegacion como lider de Celula Centro'
) x
JOIN usuarios udel ON udel.correo = x.delegador
JOIN usuarios udeg ON udeg.correo = x.delegado
JOIN areas_servicio ar ON ar.nombre = x.area_nombre
WHERE NOT EXISTS (
    SELECT 1
    FROM delegaciones d
    WHERE d.usuario_delegador_id = udel.id
      AND d.usuario_delegado_id = udeg.id
      AND d.area_servicio_id = ar.id
      AND d.activa = TRUE
);

-- ================================================================
-- NOTIFICACIONES
-- ================================================================
INSERT INTO notificaciones (usuario_destino_id, titulo, mensaje, tipo, leida)
SELECT
    u.id,
    x.titulo,
    x.mensaje,
    x.tipo,
    FALSE
FROM (
    SELECT 'lider.jovenes@iglesia.com' AS correo, 'Nueva asignacion' AS titulo, 'Has sido designado como lider de area Jovenes.' AS mensaje, 'delegacion' AS tipo
    UNION ALL
    SELECT 'juan.perez@iglesia.com', 'Recordatorio', 'Tu celula tiene reporte pendiente.', 'alerta_reporte'
    UNION ALL
    SELECT 'tesorero@iglesia.com', 'Ofrenda pendiente', 'Existe una ofrenda sin confirmar recepcion.', 'ofrenda_pendiente'
) x
JOIN usuarios u ON u.correo = x.correo
WHERE NOT EXISTS (
    SELECT 1
    FROM notificaciones n
    WHERE n.usuario_destino_id = u.id
      AND n.titulo = x.titulo
      AND n.tipo = x.tipo
      AND n.leida = FALSE
);

COMMIT;

-- ================================================================
-- VERIFICACION RAPIDA
-- ================================================================
SELECT 'usuarios' AS tabla, COUNT(*) AS total FROM usuarios
UNION ALL
SELECT 'servidores', COUNT(*) FROM servidores
UNION ALL
SELECT 'celulas', COUNT(*) FROM celulas
UNION ALL
SELECT 'reuniones', COUNT(*) FROM reuniones
UNION ALL
SELECT 'ofrendas', COUNT(*) FROM ofrendas
UNION ALL
SELECT 'delegaciones', COUNT(*) FROM delegaciones
UNION ALL
SELECT 'notificaciones', COUNT(*) FROM notificaciones;
