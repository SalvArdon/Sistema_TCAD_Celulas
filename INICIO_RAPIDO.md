# 🚀 INICIO RÁPIDO - SISTEMA TCAD CÉLULAS

## ⏱️ Instalación en 5 minutos

### 1️⃣ **Verificar XAMPP**
```
✓ Apache corriendo
✓ MySQL corriendo
✓ phpMyAdmin disponible en http://localhost/phpmyadmin
```

### 2️⃣ **Crear Base de Datos**

Abre http://localhost/phpmyadmin y ejecuta:

```sql
CREATE DATABASE tcad_celulas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tcad_celulas;
```

Luego copia todo el contenido de:  
`Sistema_TCAD_Celulas/database/tcad_celulas.sql`

**Pega y ejecuta en phpMyAdmin**

### 3️⃣ **Insertar Datos de Prueba** (Opcional pero RECOMENDADO)

Abre nuevamente la pestaña SQL y ejecuta:  
`Sistema_TCAD_Celulas/database/datos_prueba.sql`

### 4️⃣ **Acceder al Sistema**

En tu navegador:
```
http://localhost/portafolio/Sistema_TCAD_Celulas/
```

Serás redirigido automáticamente a **login.php**

### 5️⃣ **Iniciar Sesión**

**Correo:** `pastor@iglesia.com`  
**Contraseña:** `password123`

---

## 🎯 Lo que verás:

✅ **Dashboard** con estadísticas en tiempo real  
✅ **Gráficas** de asistencia y ofrendas  
✅ **Menú lateral** con todas las opciones  
✅ **Sección de reportes** pendientes  

---

## 📱 Prueba desde tu Teléfono

1. Asegúrate XAMPP esté local (mismo WiFi)
2. Ve a tu PC y busca su IP: `ipconfig` (Windows) o `ifconfig` (Linux)
3. En el teléfono abre:
   ```
   http://[IP_DE_TU_PC]:80/portafolio/Sistema_TCAD_Celulas/
   ```
4. Inicia sesión y prueba reportar una reunión
5. La interfaz es 100% responsiva

---

## 🔐 Otros Usuarios de Prueba

```
Líder de Área:
  Email: lider.jovenes@iglesia.com
  Pass: password123

Líder de Célula:
  Email: juan.perez@iglesia.com
  Pass: password123

Tesorero:
  Email: tesorero@iglesia.com
  Pass: password123
```

---

## 📞 Si Hay Problemas

| Error | Solución |
|-------|----------|
| "No se pudo conectar" | Verifica MySQL está corriendo |
| "404 Not Found" | Verifica estructura de carpetas |
| "Login no funciona" | Asegúrate de ejecutar `datos_prueba.sql` |
| "Página en blanco" | Revisa `php_errors.log` en `C:\xampp\apache\logs\` |

---

## 📚 Documentación

- **README.md** - Inicio rápido
- **MANUAL_INSTALACION.md** - Guía completa
- **PROYECTO_COMPLETADO.md** - Todo lo creado

Todos en carpeta: `Sistema_TCAD_Celulas/docs/`

---

## ✨ Características Principales

### 📊 Dashboard
- Estadísticas en vivo
- Gráficas interactivas
- Alertas de células sin reporte

### 📱 Reportar Reunión (Mobile)
- Botón grande y visible
- Asistentes, nuevos, ofrenda
- Comentarios del líder
- Envío instantáneo

### 💰 Gestión de Ofrendas
- Estados: Reportada → Recibida → Conciliada
- Tesorero confirma recepción
- Historial completo
- Discrepancias detectadas

### 👥 Gestión de Usuarios
- 5 roles diferentes
- Código de membresía único
- Auditoría de acceso
- Bloqueo temporal

### 📊 Reportes y Gráficas
- Asistencia por semana
- Ofrendas por área
- Servidores por ministerio
- Exportable a PDF (próximamente)

---

## 🎯 Flujo de Uso Típico

```
1. Pastor inicia sesión
   ↓
2. Ve estadísticas en Dashboard
   ↓
3. Revisa células sin reporte
   ↓
4. Delega líder de área
   ↓
5. Líder delega a líderes de células
   ↓
6. Líder de célula reporta reunión desde teléfono
   ↓
7. Tesorero confirma ofrenda
   ↓
8. Pastor ve datos actualizados en tiempo real
```

---

## 🔧 Personalización

### Cambiar configuración:
```php
// Archivo: config/config.php

define('DIAS_ALERTA_SIN_REPORTE', 14);  // Alertar después de 14 días
define('SESSION_TIMEOUT', 3600);         // Sesión 1 hora
define('MONEDA', 'USD');                 // Cambiar a tu moneda
```

### Cambiar colores:
```css
// Archivo: assets/css/tailwind.css
// Edita variables CSS personalizadas
```

---

## 📊 Estructura de Datos

```
Iglesia
├── Pastor
│   ├── Líder de Área Jóvenes
│   │   ├── Líder de Célula Centro
│   │   │   └── 12 servidores
│   │   └── Líder de Célula San Benito
│   │       └── 15 servidores
│   └── Líder de Área Matrimonios
│       └── Células...
└── Tesorero
    └── Gestiona ofrendas
```

---

## ✅ Verificaciones Finales

- [ ] phpMyAdmin accesible
- [ ] Base de datos `tcad_celulas` creada
- [ ] Tablas en BD (14 total)
- [ ] Datos de prueba insertados
- [ ] Estructura de carpetas correcta
- [ ] Login funciona
- [ ] Dashboard visible
- [ ] Gráficas cargan

---

## 🎓 Capacitación Recomendada

1. **Pastor:** Explicar dashboard y delegación
2. **Líderes de Área:** Mostrar cómo delegar
3. **Líderes de Célula:** Entrenar formulario de reporte
4. **Tesorero:** Explicar confirmación de ofrendas

---

## 📞 Soporte Rápido

Si algo no funciona:

1. Verifica que **MySQL esté corriendo**
2. Abre `http://localhost/phpmyadmin`
3. Expande la BD `tcad_celulas`
4. Verifica que existan 14 tablas
5. Ejecuta query:
   ```sql
   SELECT COUNT(*) FROM usuarios;
   ```
   Debe devolver: **8 usuarios de prueba**

---

## 🎉 ¡Listo para Usar!

El sistema está completamente funcional. Comienza a agregar tus células, usuarios y realiza pruebas.

**Bienvenido al Sistema TCAD Células v1.0.0** 🙌

---

**Creado:** 9 de Marzo de 2026  
**Versión:** 1.0.0  
**Estado:** ✅ LISTO PARA USAR
