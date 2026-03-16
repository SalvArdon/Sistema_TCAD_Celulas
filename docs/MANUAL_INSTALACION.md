# 📱 Sistema TCAD Células - Documentación Completa

## 🎯 Descripción General

**Sistema TCAD Células** es una aplicación web moderna y responsiva para control integral de células (reuniones de comunidad) en iglesias. Gestiona:

- ✅ Reuniones celulares y reportes
- 👥 Servidores y liderazgo
- 💰 Control de ofrendas con auditoría
- 📊 Estadísticas y gráficas
- 📚 Material de estudio
- 🔔 Notificaciones automáticas
- 🎯 Auditoría completa de cambios

---

## 🚀 Requisitos de Instalación

### Hardware/Software:
- **XAMPP** 7.4+ (PHP 7.4+, MySQL 5.7+)
- **Navegador moderno** (Chrome, Firefox, Safari, Edge)
- **Conexión a internet** (para CDN de Tailwind CSS y Chart.js)

### Archivos necesarios:
- Apache habilitado
- MySQL habilitado
- PHP 7.4 o superior

---

## 📥 Instalación Paso a Paso

### 1. **Crear Base de Datos**

Abre **phpMyAdmin** (http://localhost/phpmyadmin)

1. Crea una nueva base de datos llamada `tcad_celulas`
2. Selecciona la BD y ve a la pestaña "SQL"
3. Copia todo el contenido de `database/tcad_celulas.sql`
4. Ejecuta el script

**Resultado esperado:** 14 tablas creadas correctamente

---

### 2. **Configurar Credenciales de Base de Datos**

Edita el archivo `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Usuario MySQL
define('DB_PASS', '');             // Contraseña (vacía por defecto en XAMPP)
define('DB_NAME', 'tcad_celulas');
define('DB_PORT', 3306);
```

---

### 3. **Estructura de Carpetas**

```
Sistema_TCAD_Celulas/
├── config/              # Configuración y conexión DB
│   ├── config.php      # Configuración general
│   └── database.php    # Conexión a BD
├── controllers/         # Lógica de negocio
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── ReunionController.php
│   ├── OfrendaController.php
│   └── ...
├── models/             # Modelos de datos
│   ├── Model.php       # Clase base
│   ├── Usuario.php
│   ├── Celula.php
│   ├── Reunion.php
│   ├── Ofrenda.php
│   └── ...
├── views/              # Vistas HTML (responsivas)
│   ├── login.php
│   ├── dashboard.php
│   └── ...
├── api/                # Endpoints JSON REST
│   ├── auth.php
│   ├── dashboard.php
│   ├── reuniones.php
│   ├── ofrendas.php
│   └── ...
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── database/           # Scripts SQL
│   └── tcad_celulas.sql
├── docs/              # Documentación
└── index.php          # Archivo principal
```

---

## 🔐 Usuarios de Prueba (Después de instalar)

El sistema incluye 5 roles:

### **1. Pastor** (Acceso Total)
```sql
INSERT INTO usuarios (nombre_completo, correo, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES ('Pastor Principal', 'pastor@iglesia.com', '$2y$10...', 1, 'MEM001', NOW(), TRUE);
```

### **2. Líder de Área**
```sql
INSERT INTO usuarios (nombre_completo, correo, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES ('Líder Jóvenes', 'lider.jovenes@iglesia.com', '$2y$10...', 2, 'MEM002', NOW(), TRUE);
```

### **3. Líder de Célula**
```sql
INSERT INTO usuarios (nombre_completo, correo, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES ('Juan Pérez', 'juan@iglesia.com', '$2y$10...', 3, 'MEM003', NOW(), TRUE);
```

### **4. Tesorero**
```sql
INSERT INTO usuarios (nombre_completo, correo, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES ('Tesorero Iglesia', 'tesorero@iglesia.com', '$2y$10...', 4, 'MEM004', NOW(), TRUE);
```

### **5. Servidor**
```sql
INSERT INTO usuarios (nombre_completo, correo, password_hash, rol_id, codigo_membresia, fecha_ingreso, activo)
VALUES ('Servidor General', 'servidor@iglesia.com', '$2y$10...', 5, 'MEM005', NOW(), TRUE);
```

**Para generar contraseña encriptada:**
```php
$password = 'password123';
echo password_hash($password, PASSWORD_ARGON2ID);
```

---

## 🌐 Acceso al Sistema

1. **Abre tu navegador**
2. Ingresa: `http://localhost/portafolio/Sistema_TCAD_Celulas/`
3. Serás redirigido automáticamente a `login.php`
4. Ingresa tus credenciales

---

## 🏗️ Arquitectura Técnica

### **Patrón MVC**
- **M**odel: Clases de datos (Usuario.php, Celula.php, etc.)
- **V**iew: Vistas HTML responsivas con Tailwind CSS
- **C**ontroller: Lógica de negocio

### **API REST**
- Endpoints en carpeta `/api/`
- Respuestas en JSON
- Autenticación por sesión PHP
- CORS habilitado

### **Base de Datos**
- 14 tablas relacionadas
- Llave primaria y foránea en todas
- Vistas SQL predefinidas
- Índices optimizados para búsquedas

---

## 🔒 Seguridad Implementada

✅ **Encriptación:**
- Contraseñas con Argon2ID
- Sesiones con timeout automático

✅ **Protección SQL:**
- Prepared Statements (PDO)
- Prevención de SQL Injection

✅ **Auditoría:**
- Tabla `auditoria` registra todos cambios
- Quién, qué, cuándo, y por qué
- Rastreo de IP y dispositivo

✅ **Control de Acceso:**
- 5 roles con permisos diferenciados
- Validación en cada endpoint
- Bloqueo temporal tras intentos fallidos

---

## 📊 Características Principales

### **1. Módulo de Células**
```
- Registrar código de célula
- Líder responsable
- Ubicación y dirección
- Horario de reunión
- Estado (activa/inactiva/pausada)
- Historial de reuniones
```

### **2. Reportes Móvil-First**
```
- Interfaz optimizada para teléfono
- Campo: Asistentes
- Campo: Nuevos visitantes
- Campo: Monto de ofrenda
- Campo: Comentarios del líder
- Envío inmediato a BD
```

### **3. Control de Ofrendas**
```
Estado 1: "Reportada por líder"
  └─ Registrado en teléfono

Estado 2: "Recibida en iglesia"
  └─ Confirmado por tesorero

Estado 3: "Conciliada"
  └─ Dinero coincide con reporte
```

### **4. Dashboard del Pastor**
```
- Células activas/inactivas
- Total de servidores
- Ofrendas pendientes
- Reportes recientes
- Gráficas de tendencias
- Células sin reporte (alertas)
```

### **5. Delegación Jerárquica**
```
Pastor
  ├─ Delega a → Líder de Área
  │   ├─ Delega a → Líder de Célula
  │   │   └─ Reporta → Reunión & Ofrenda
```

---

## 📱 Responsividad (Mobile First)

El sistema funciona perfectamente en:
- **📱 Smartphones** (iOS/Android)
- **📲 Tablets**
- **💻 Computadoras**

**Breakpoints Tailwind:**
```
sm: 640px
md: 768px
lg: 1024px
xl: 1280px
2xl: 1536px
```

---

## 🔔 Notificaciones Automáticas

Se envían notificaciones cuando:
- ✅ Pastor asigna nuevo líder
- ✅ Líder de área sube material de estudio
- ✅ Célula no reporta reunión (> 14 días)
- ✅ Ofrenda pendiente de confirmación

---

## 🗂️ Modelos de Datos Base

### **Usuario**
```php
- id, nombre_completo, correo, telefono
- password_hash, rol_id
- codigo_membresia (como DUI)
- fecha_ingreso, ultimo_acceso
- activo, bloqueado_hasta
```

### **Célula**
```php
- id, nombre, lider_id, anfitrion_id
- direccion, zona, coordenadas (GPS)
- dia_semana, hora_inicio
- estado, cantidad_promedio_asistentes
```

### **Reunión**
```php
- id, celula_id, fecha_reunion
- realizada, cantidad_asistentes, cantidad_nuevos
- lider_reporta_id, comentarios
- fecha_reporte, dispositivo
```

### **Ofrenda**
```php
- id, reunion_id, monto
- estado (reportada/recibida/conciliada)
- lider_reporta_id, usuario_recibe_id
- usuario_concilia_id, descrepancia
```

---

## ⚙️ Configuración Personalizada

Edita `config/config.php`:

```php
// Días de alerta para células sin reporte
define('DIAS_ALERTA_SIN_REPORTE', 14);

// Moneda del sistema
define('MONEDA', 'USD');

// Timeout de sesión (segundos)
define('SESSION_TIMEOUT', 3600); // 1 hora

// Máximo de intentos de login
define('MAX_LOGIN_INTENTOS', 5);

// Tiempo de bloqueo temporal
define('BLOQUEO_MINUTOS', 15);
```

---

## 🧪 Pruebas Rápidas

### **Test 1: Login**
1. Accede a `http://localhost/portafolio/Sistema_TCAD_Celulas/`
2. Usa credenciales de prueba
3. Deberías ver el dashboard

### **Test 2: Reportar Reunión (Móvil)**
1. Inicia como "Líder de Célula"
2. Haz clic en "Reportar Reunión"
3. Rellena: Asistentes, Ofrenda, Comentarios
4. Envía desde tu teléfono

### **Test 3: Confirmar Ofrenda**
1. Inicia como "Tesorero"
2. Ve a "Ofrendas Pendientes"
3. Cambia estado de ofrenda
4. Verifica auditoría

---

## 🐛 Troubleshooting

### **Error: "No se pudo conectar a BD"**
```
✓ Verifica que MySQL está ejecutándose
✓ Controla credenciales en config/database.php
✓ Asegúrate que BD 'tcad_celulas' existe
```

### **Error: "Sesión expirada"**
```
✓ Inicia sesión nuevamente
✓ Verifica que PHP sessions están habilitadas
✓ Aumenta SESSION_TIMEOUT en config.php
```

### **Error 404 en API**
```
✓ Verifica estructura de carpetas `/api/`
✓ Asegúrate que mod_rewrite está habilitado
✓ Revisa .htaccess si existe
```

---

## 📞 Soporte

Para dudas o problemas:
1. Revisa los logs en `/database/`
2. Consulta la auditoría en tabla `auditoria`
3. Verifica logs de error en `php_errors.log`

---

## 📄 Licencia

Sistema creado para Iglesias - Uso interno - 2026

---

**Versión:** 1.0.0  
**Última actualización:** 9 de Marzo de 2026  
**Desarrollador:** Sistema TCAD
