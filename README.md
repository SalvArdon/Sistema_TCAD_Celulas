# 📋 README - Sistema TCAD Células

## 🎯 ¿Qué es Sistema TCAD Células?

Sistema moderno y responsivo para **iglesias** que permite gestionar:

✅ Reuniones celulares (células)  
✅ Asistencia y nuevos visitantes  
✅ Control de ofrendas con auditoría  
✅ Servidores y liderazgo  
✅ Material de estudio  
✅ Reportes y estadísticas  
✅ Notificaciones automáticas  

---

## 🚀 Inicio Rápido

### 1️⃣ Instala XAMPP
Descarga desde: https://www.apachefriends.org/

### 2️⃣ Coloca los archivos
```
C:\xampp\htdocs\portafolio\Sistema_TCAD_Celulas\
```

### 3️⃣ Crea la base de datos
- Abre http://localhost/phpmyadmin
- Crea BD: `tcad_celulas`
- Ejecuta script: `database/tcad_celulas.sql`

### 4️⃣ Accede al sistema
```
http://localhost/portafolio/Sistema_TCAD_Celulas/
```

### 5️⃣ Inicia sesión
```
Email: pastor@iglesia.com
Contraseña: (la que configuraste)
```

---

## ✨ Características Clave

### 📱 **Mobile First**
- Diseño responsive
- Botones grandes
- Formularios simples
- Perfecto para teléfono

### 👥 **Gestión de Usuarios**
- 5 roles diferentes
- Control de permisos
- Código de membresía único
- Auditoría completa

### 🏘️ **Control de Células**
- Ubicación con GPS
- Horarios fijos
- Seguimiento de líderes
- Historial de reuniones

### 💰 **Finanzas**
- Reporte de ofrenda en móvil
- Estados: Reportada → Recibida → Conciliada
- Dashboard financiero
- Auditoría de cambios

### 📊 **Reportes**
- Estadísticas en tiempo real
- Gráficas interactivas
- Exportación a PDF/Excel
- Alertas automáticas

---

## 🗂️ Estructura

```
Sistema_TCAD_Celulas/
├── config/           - Configuración
├── models/           - Acceso a datos
├── controllers/      - Lógica de negocio
├── views/            - Interfaces HTML
├── api/              - Endpoints REST
├── assets/           - CSS, JS, Imágenes
├── database/         - Scripts SQL
├── docs/             - Documentación
└── index.php         - Punto de entrada
```

---

## 🔐 Seguridad

✅ Contraseñas encriptadas (Argon2ID)  
✅ Sesiones con timeout  
✅ Prepared Statements (sin SQL Injection)  
✅ Auditoría completa  
✅ Control de rol-based access  
✅ Bloqueo temporal tras intentos fallidos  

---

## 📖 Documentación Completa

Ver: **[MANUAL_INSTALACION.md](./MANUAL_INSTALACION.md)**

---

## 🎓 Roles del Sistema

| Rol | Permisos |
|-----|----------|
| 👨‍💼 **Pastor** | Acceso total, auditoría |
| 👥 **Líder de Área** | Gestión de área, delegación |
| 👤 **Líder de Célula** | Reporte de reunión, ofrenda |
| 💵 **Tesorero** | Confirmación de ofrendas |
| 👤 **Servidor** | Acceso limitado a info |

---

## 📞 Stack Tecnológico

- **Frontend:** Tailwind CSS, HTML5, JavaScript, Chart.js
- **Backend:** PHP (MVC)
- **DB:** MySQL
- **Servidor:** XAMPP/Apache
- **Protocolo:** REST API con JSON

---

## ✅ Checklist de Instalación

- [ ] XAMPP instalado y corriendo
- [ ] BD `tcad_celulas` creada
- [ ] Script SQL ejecutado
- [ ] Credenciales en `config/database.php`
- [ ] Acceso a http://localhost/portafolio/Sistema_TCAD_Celulas/
- [ ] Login exitoso
- [ ] Dashboard visible

---

## 🐛 Problemas Comunes

| Problema | Solución |
|----------|----------|
| Error de conexión | Verifica MySQL está corriendo |
| Página en blanco | Revisa errores en `php_errors.log` |
| Login no funciona | Confirma contraseña en BD |
| Sesión expira rápido | Aumenta `SESSION_TIMEOUT` en config |

---

## 🎯 Próximos Pasos

1. [ ] Crear usuarios en la iglesia
2. [ ] Registrar células
3. [ ] Asignar líderes
4. [ ] Entrenar a líderes de célula
5. [ ] Subir material de estudio
6. [ ] Comenzar a reportar reuniones

---

## 📄 Versión

**v1.0.0** - 9 de Marzo de 2026

---

**¿Preguntas?** Consulta la documentación completa en `/docs/`
