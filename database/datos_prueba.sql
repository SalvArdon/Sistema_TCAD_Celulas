-- ================================================================
-- SCRIPT DE DATOS DE PRUEBA - TCAD CÉLULAS
-- Inserta usuarios, células y ejemplos para pruebas
-- ================================================================

-- IMPORTANTE: Ejecuta primero el script tcad_celulas.sql antes de esto

USE tcad_celulas;

-- ================================================================
-- INSERTAR USUARIOS DE PRUEBA
-- Contraseña para todos: password123
-- Hash Argon2ID: $2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8
-- ================================================================

-- 1. PASTOR (Acceso Total)
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'Pastor Principal',
    'pastor@iglesia.com',
    '+503 7123-4567',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    1,
    'MEM000001',
    NOW(),
    TRUE
);

-- 2. LIDER DE AREA - Jovenes
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'María García - Líder Jóvenes',
    'lider.jovenes@iglesia.com',
    '+503 7234-5678',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    2,
    'MEM000002',
    NOW(),
    TRUE
);

-- 3. LIDER DE AREA - Matrimonios
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'Carlos López - Líder Matrimonios',
    'lider.matrimonios@iglesia.com',
    '+503 7345-6789',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    2,
    'MEM000003',
    NOW(),
    TRUE
);

-- 4. LIDER DE CELULA - Centro
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'Juan Pérez - Líder Célula Centro',
    'juan.perez@iglesia.com',
    '+503 7456-7890',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    3,
    'MEM000004',
    NOW(),
    TRUE
);

-- 5. LIDER DE CELULA - San Benito
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'Ana Martínez - Líder Célula San Benito',
    'ana.martinez@iglesia.com',
    '+503 7567-8901',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    3,
    'MEM000005',
    NOW(),
    TRUE
);

-- 6. TESORERO
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'David Rodríguez - Tesorero',
    'tesorero@iglesia.com',
    '+503 7678-9012',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    4,
    'MEM000006',
    NOW(),
    TRUE
);

-- 7. SERVIDORES (Miembros)
INSERT INTO usuarios (nombre_completo, correo, telefono, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES (
    'Roberto García',
    'roberto@iglesia.com',
    '+503 7789-0123',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    5,
    'MEM000007',
    NOW(),
    TRUE
), (
    'Marta Sánchez',
    'marta@iglesia.com',
    '+503 7890-1234',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    5,
    'MEM000008',
    NOW(),
    TRUE
), (
    'Pedro Flores',
    'pedro@iglesia.com',
    '+503 7901-2345',
    '$2y$10$bDz5Z6H9V2tKm1L8P9Q0ZOKq0RnZ8X5Z0Y0A1B2C3D4E5F6G7H8',
    5,
    'MEM000009',
    NOW(),
    TRUE
);

-- ================================================================
-- ACTUALIZAR LÍDERES DE ÁREAS
-- ================================================================

UPDATE areas_servicio SET lider_id = 2 WHERE nombre = 'Jóvenes';
UPDATE areas_servicio SET lider_id = 3 WHERE nombre = 'Matrimonios';

-- ================================================================
-- REGISTRAR SERVIDORES
-- ================================================================

INSERT INTO servidores (usuario_id, area_servicio_id, genero, bautizado, activo) VALUES
(7, 1, 'M', TRUE, TRUE),
(8, 3, 'F', TRUE, TRUE),
(9, 7, 'M', TRUE, TRUE);

-- ================================================================
-- INSERTAR CÉLULAS
-- ================================================================

INSERT INTO celulas (nombre, lider_id, lider_area_id, area_servicio_id, direccion, zona, dia_semana, hora_inicio, estado, cantidad_promedio_asistentes)
VALUES
-- Célula Centro
(
    'Célula Centro',
    4,
    2,
    1,
    'Calle Principal 123, Centro',
    'Centro',
    'Lunes',
    '19:00',
    'activa',
    12
),
-- Célula San Benito
(
    'Célula San Benito',
    5,
    2,
    1,
    'Avenida Independencia 456, San Benito',
    'San Benito',
    'Miércoles',
    '19:30',
    'activa',
    15
);

-- ================================================================
-- INSERTAR REUNIONES DE EJEMPLO
-- ================================================================

INSERT INTO reuniones (celula_id, fecha_reunion, realizada, cantidad_asistentes, cantidad_nuevos, lider_reporta_id, comentarios, temas_tratados)
VALUES
(
    1,
    DATE_SUB(CURDATE(), INTERVAL 1 DAY),
    TRUE,
    12,
    1,
    4,
    'Buena asistencia. Grupo muy participativo.',
    'La Gracia de Dios - Efesios 2:8-9'
),
(
    2,
    DATE_SUB(CURDATE(), INTERVAL 3 DAY),
    TRUE,
    14,
    2,
    5,
    'Excelente célula. Muchas oraciones contestadas.',
    'Fe y Obras - Santiago 2:26'
);

-- ================================================================
-- INSERTAR OFRENDAS
-- ================================================================

INSERT INTO ofrendas (reunion_id, monto, moneda, estado, lider_reporta_id, fecha_reporte)
VALUES
(
    1,
    125.50,
    'USD',
    'reportada',
    4,
    DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
    2,
    150.00,
    'USD',
    'conciliada',
    5,
    DATE_SUB(NOW(), INTERVAL 3 DAY)
);

-- Actualizar estado de segunda ofrenda
UPDATE ofrendas SET 
    estado = 'conciliada',
    usuario_recibe_id = 6,
    fecha_recepcion = DATE_SUB(NOW(), INTERVAL 3 DAY),
    usuario_concilia_id = 6,
    fecha_conciliacion = DATE_SUB(NOW(), INTERVAL 2 DAY)
WHERE id = 2;

-- ================================================================
-- INSERTAR DELEGACIONES
-- ================================================================

INSERT INTO delegaciones (usuario_delegador_id, usuario_delegado_id, area_servicio_id, activa, fecha_delegacion, razon)
VALUES
(1, 2, 1, TRUE, NOW(), 'Delegación de liderazgo del área Jóvenes'),
(1, 3, 3, TRUE, NOW(), 'Delegación de liderazgo del área Matrimonios'),
(2, 4, 1, TRUE, NOW(), 'María delega a Juan como líder de célula Centro'),
(2, 5, 1, TRUE, NOW(), 'María delega a Ana como líder de célula San Benito');

-- ================================================================
-- INSERTAR NOTIFICACIONES DE EJEMPLO
-- ================================================================

INSERT INTO notificaciones (usuario_destino_id, titulo, mensaje, tipo, leida)
VALUES
(2, 'Nueva asignación', 'Has sido designado como Líder de Área Jóvenes', 'delegacion', FALSE),
(4, 'Recordatorio', 'Tu célula tiene reporte pendiente de hace 3 días', 'alerta_reporte', FALSE),
(6, 'Ofrenda pendiente', 'Hay una ofrenda sin confirmar recepción', 'ofrenda_pendiente', FALSE);

-- ================================================================
-- INSERTAR AUDITORÍA DE EJEMPLO
-- ================================================================

INSERT INTO auditoria (usuario_id, accion, tabla_afectada, registro_id, fecha_hora, ip_usuario)
VALUES
(1, 'insertar', 'usuarios', 2, NOW(), '127.0.0.1'),
(1, 'insertar', 'celulas', 1, NOW(), '127.0.0.1'),
(4, 'insertar', 'reuniones', 1, NOW(), '192.168.1.100'),
(6, 'actualizar', 'ofrendas', 1, NOW(), '192.168.1.105');

-- ================================================================
-- FIN - DATOS DE PRUEBA INSERTADOS
-- ================================================================

-- Verificación
SELECT 'Total de usuarios:' as info, COUNT(*) FROM usuarios;
SELECT 'Total de células:', COUNT(*) FROM celulas;
SELECT 'Total de reuniones:', COUNT(*) FROM reuniones;
SELECT 'Total de ofrendas:', COUNT(*) FROM ofrendas;
SELECT 'Total de auditoría:', COUNT(*) FROM auditoria;
