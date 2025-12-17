# Aquinos-Main
# 🛋️ CNA Upholstery

<div align="center">

![CNA Upholstery](https://img.shields.io/badge/CNA-Upholstery-blue?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Status](https://img.shields.io/badge/Status-En%20Producci%C3%B3n-success?style=for-the-badge)

**Sistema completo de gestión para servicios de tapicería profesional**

[🌐 Ver Proyecto](https://aquinossolution.com/cna.upholstery/) | [📧 Contacto](mailto:alexs199.ale@gmail.com)

</div>

---

## 📋 Índice

- [Descripción](#-descripción)
- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [Funcionalidades Principales](#-funcionalidades-principales)
- [Seguridad](#-seguridad)
- [Multiidioma](#-multiidioma)
- [Capturas de Pantalla](#-capturas-de-pantalla)
- [Licencia](#-licencia)
- [Autor](#-autor)

---

## 🎯 Descripción

**CNA Upholstery System** es una aplicación web completa diseñada para gestionar todas las operaciones de un negocio de tapicería profesional. Desarrollada para **CNA Upholstery** esta plataforma integra desde la presentación de servicios hasta la gestión administrativa completa.

El sistema está actualmente en producción en [aquinossolution.com/cna.upholstery/]([https://aquinossolution.com](https://aquinossolution.com/cna.upholstery/)) y maneja operaciones reales del negocio.

### ✨ Aspectos Destacados

- ✅ Sistema completo de facturación y presupuestos
- ✅ Gestión de clientes y proyectos
- ✅ Landing page bilingüe (inglés/español)
- ✅ Panel administrativo
- ✅ Generación de imagenes
- ✅ Sistema de seguridad avanzado
- ✅ Diseño responsive y moderno

---

## 🚀 Características

### 📱 Landing Page Pública
- **Diseño Responsive**: Totalmente optimizado para móviles, tablets y escritorio
- **Multiidioma**: Cambio dinámico entre inglés y español
- **Sección de Servicios**: Presentación detallada de servicios de tapicería
- **Galería de Proyectos**: Muestra visual de trabajos realizados
- **Formulario de Contacto**: Sistema de consultas integrado con validación
- **Integración WhatsApp**: Botón de contacto directo

### 💼 Panel Administrativo
- **Dashboard Completo**: Vista general con estadísticas y métricas
- **Gestión de Facturas**: Crear, editar, visualizar y eliminar facturas
- **Gestión de Presupuestos**: Sistema completo de cotizaciones
- **Base de Datos de Clientes**: Registro y gestión de información de clientes
- **Búsqueda y Filtros Avanzados**: Por número, cliente, estado, tipo, fecha
- **Conversión Estimate → Invoice**: Convertir presupuestos en facturas con un clic
- **Generación de PDFs**: Documentos profesionales descargables
- **Generación de Imágenes**: Versiones visuales de documentos para compartir
- **Sistema de Proyectos**: Upload y gestión de galería de trabajos

### 🔒 Seguridad
- Protección contra inyección SQL mediante PDO
- Sanitización de datos XSS
- Rate limiting para prevenir ataques de fuerza bruta
- Sistema de logs de seguridad
- Sesiones seguras con timeouts
- Validación de IP y user agent

### 📊 Sistema de Documentos
- Numeración automática secuencial
- Cálculo automático de impuestos (6.625%)
- Items ilimitados por documento
- Notas y detalles adicionales
- Estados: Pendiente, Pagado, Confirmado
- Visualización profesional en PDF
- Descarga como imagen para redes sociales

---

## 🛠️ Tecnologías

### Backend
- **PHP 7.4+**: Lenguaje principal del servidor
- **MySQL 8.0+**: Base de datos relacional
- **PDO**: Capa de abstracción de base de datos
- **Sessions**: Manejo de autenticación y estado

### Frontend
- **HTML5**: Estructura semántica
- **CSS3**: Estilos modernos
- **Tailwind CSS**: Framework de utilidades CSS
- **JavaScript Vanilla**: Interactividad y validación
- **Responsive Design**: Mobile-first approach

### Librerías y Herramientas
- **Font Awesome**: Iconografía
- **Google Fonts (Poppins)**: Tipografía moderna
- **SweetAlert2**: Alertas y modales elegantes
- **PDF Generation**: Sistema nativo de generación de PDFs
- **Image Processing (GD/Imagick)**: Generación de imágenes

---

## 📁 Estructura del Proyecto

```
Aquinos-Main/CNA-Upholstery
│
├── 📄 index.php                    # Landing page principal (bilingüe)
├── 📄 login.php                    # Sistema de autenticación
├── 📄 logout.php                   # Cierre de sesión
├── 📄 mail.php                     # Manejo de formularios de contacto
├── 📄 projects.php                 # Galería pública de proyectos
│
├── 📁 admin/                       # Panel de administración
│   ├── index.php                   # Dashboard principal
│   ├── clients.php                 # Gestión de clientes
│   ├── create-invoice.php          # Crear facturas
│   ├── create-estimate.php         # Crear presupuestos
│   ├── edit-document.php           # Editar documentos
│   ├── view-document.php           # Visualizar documentos
│   ├── delete-document.php         # Eliminar documentos
│   ├── convert-to-invoice.php      # Convertir estimate a invoice
│   ├── generate-image.php          # Generar imagen de documento
│   ├── upload-project.php          # Subir proyectos a galería
│   └── settings.php                # Configuraciones del sistema
│
├── 📁 config/                      # Configuraciones del sistema
│   ├── config.php                  # Configuración general
│   ├── database.php                # Conexión a base de datos
│   └── security.php                # Funciones de seguridad
│
├── 📁 includes/                    # Funciones auxiliares
│   └── functions.php               # Funciones globales del sistema
│
├── 📁 logs/                        # Logs de seguridad y sistema
    └── security.log                # Registro de eventos de seguridad
```

---

## 💻 Instalación

### Requisitos Previos

- **Servidor Web**: Apache 2.4+ o Nginx
- **PHP**: 7.4 o superior
- **MySQL**: 8.0 o superior
- **Extensiones PHP**:
  - PDO y pdo_mysql
  - GD o Imagick (para generación de imágenes)
  - mbstring
  - json
  - session

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tuusuario/Aquinos-Main.git
   cd Aquinos-Main
   ```

2. **Configurar la base de datos**
   ```sql
   CREATE DATABASE cna_upholstery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Importar el esquema de base de datos**
   ```bash
   mysql -u tu_usuario -p cna_upholstery < database/schema.sql
   ```

4. **Configurar credenciales**
   
   Edita `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cna_upholstery');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

5. **Configurar URLs**
   
   Edita `config/config.php`:
   ```php
   define('BASE_URL', 'https://tudominio.com/');
   ```

6. **Configurar permisos**
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   chmod 644 config/*.php
   ```

7. **Crear usuario administrador**
   
   Ejecuta el script SQL o crea manualmente:
   ```sql
   INSERT INTO usuarios (username, password, email) 
   VALUES ('admin', PASSWORD_HASH_AQUI, 'admin@tudominio.com');
   ```

---

## ⚙️ Configuración

### Configuración de la Empresa

En `config/config.php`:

```php
define('COMPANY_NAME', 'CNA Upholstery');
define('COMPANY_OWNER', 'Claudio N Aquino');
define('COMPANY_PHONE', '908-510-9157');
define('COMPANY_EMAIL', 'cnaupholstery0@gmail.com');
define('COMPANY_ADDRESS', '29 Downstream Drive, Flanders, NJ, 07836');
```

### Configuración de Impuestos

```php
define('TAX_RATE', 0.06625); // 6.625% NJ Tax
define('TAX_LABEL', 'Tax (6.625%)');
```

### Configuración de Seguridad

En `config/security.php`:

```php
define('SESSION_LIFETIME', 3600);        // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);         // Intentos máximos
define('LOCKOUT_TIME', 900);             // 15 minutos de bloqueo
```

---

## 📖 Uso

### Acceso al Sistema

1. **Landing Page**: `https://aquinossolution.com/cna.upholstery/`
2. **Panel Admin**: `https://aquinossolution.com/cna.upholstery/admin/`
3. **Login**: `https://aquinossolution.com/cna.upholstery/login.php`

### Credenciales por Defecto

Por seguridad, las credenciales deben ser configuradas durante la instalación. No hay credenciales por defecto en producción.

### Flujo de Trabajo Típico

1. **Cliente contacta** → Formulario de contacto o WhatsApp
2. **Crear presupuesto** → Admin crea estimate con detalles del trabajo
3. **Cliente aprueba** → Cambiar estado a "Confirmado"
4. **Realizar trabajo** → Actualizar galería de proyectos
5. **Convertir a factura** → Botón de conversión automática
6. **Generar PDF** → Enviar al cliente
7. **Registrar pago** → Marcar como "Pagado"

---

## 🎨 Funcionalidades Principales

### 1. Gestión de Facturas (Invoices)

- Crear facturas con múltiples items
- Numeración automática (formato: INV-001, INV-002, etc.)
- Cálculo automático de subtotal, impuesto y total
- Selección de cliente existente o crear nuevo
- Notas adicionales personalizables
- Estados: Pendiente/Pagado
- Descarga en PDF profesional
- Generación de imagen para compartir

### 2. Gestión de Presupuestos (Estimates)

- Crear cotizaciones detalladas
- Numeración automática (formato: EST-001, EST-002, etc.)
- Mismas funcionalidades que facturas
- Conversión directa a factura con un clic
- Estados: Pendiente/Confirmado/Rechazado

### 3. Base de Datos de Clientes

- Registro completo de clientes
- Información: Nombre, teléfono, email, dirección
- Historial de documentos por cliente
- Búsqueda y filtrado rápido
- Reutilización de datos en nuevos documentos

### 4. Galería de Proyectos

- Upload de imágenes de trabajos realizados
- Títulos y descripciones bilingües
- Visualización en grid responsive
- Gestión desde panel admin
- Muestra automática en landing page

### 5. Sistema de Reportes

- Estadísticas de documentos del mes
- Total de clientes registrados
- Totales pagados y pendientes
- Filtros por fecha, tipo, estado
- Exportación de datos

---

## 🔐 Seguridad

El sistema implementa múltiples capas de seguridad:

### Autenticación
- Hash seguro de contraseñas (bcrypt)
- Protección contra fuerza bruta
- Sesiones con timeout automático
- Validación de IP y user agent

### Protección de Datos
- PDO con prepared statements
- Sanitización de inputs (XSS)
- CSRF token en formularios críticos
- Validación de tipos de archivo

### Auditoría
- Logs de intentos de login fallidos
- Registro de accesos administrativos
- Logs de cambios en documentos
- Detección de actividad sospechosa

### Ejemplos de Eventos Registrados
```
[2025-12-16 10:23:45] FAILED_LOGIN - IP: 192.168.1.100 - User: admin
[2025-12-16 10:25:12] SUCCESSFUL_LOGIN - IP: 192.168.1.100 - User: admin
[2025-12-16 11:30:00] INVOICE_CREATED - ID: INV-123 - User: admin
```

---

## 🌐 Multiidioma

El sistema soporta **inglés** y **español** completamente:

### Cambio de Idioma
- Switch en la barra de navegación
- Persistencia mediante sesiones
- Parámetro URL: `?lang=en` o `?lang=es`

### Áreas Traducidas
- ✅ Landing page completa
- ✅ Panel administrativo
- ✅ Formularios y validaciones
- ✅ Mensajes de error y éxito
- ✅ Documentos PDF generados
- ✅ Emails automatizados

---

## 📸 Capturas de Pantalla

### Landing Page

<div>
<img width="1886" height="862" alt="image" src="https://github.com/user-attachments/assets/f0fe183c-32a3-412d-9d9a-59bf18664cb5"/>
</div>

### Panel Administrativo
<div>
<img width="1900" height="604" alt="image" src="https://github.com/user-attachments/assets/f33d2259-163d-4c66-8241-c285a198bf81" />
</div>



## 🤝 Contribuciones

Este es un proyecto privado en producción. Si deseas contribuir o reportar un bug:

1. 📧 Contacta a: alexs199.ale@gmail.com
2. 🐛 Describe el problema o mejora
3. 📎 Incluye capturas o logs si aplica por favor

---

## 📄 Licencia

Este proyecto es de uso privado para **CNA Upholstery**. Todos los derechos reservados.

**Copyright © 2025 Aquinos'Solution - CNA Upholstery**

---

## 👤 Autor

### Alejandro Aquino


- 📍 Ubicación: San Lorenzo, Paraguay
- 📞 Teléfono: [983363503](tel:983363503)
- 📧 Email: [alexs199.ale@gmail.com](mailto:alexs199.ale@gmail.com)
- 🌐 Website: [aquinossolution.com](https://aquinossolution.com)
- 💬 WhatsApp: [Chat directo](https://wa.me/983363503)

---

<div align="center">

### ⭐ Si te gusta este proyecto, considera darle una estrella

**Hecho con ❤️ para CNA Upholstery - MUCHAS GRACIAS POR CONFIAR**

[🔝 Volver arriba](#️-cna-upholstery---sistema-de-gestión-empresarial)

</div>
