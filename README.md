# 🏠 Las Casas de Mi Amistad — App Web (PHP + PostgreSQL)

Aplicación web moderna, sobria y minimalista para la gestión y consulta pública de los grupos de iglesia en casas ("Casas de Amistad"), materiales de enseñanza en PDF e integrantes.

---

## 📋 Características Principales

* **Vista Pública de Usuarios:**
  * Directorio interactivo de casas con buscador en tiempo real y filtro por día de reunión.
  * Tarjetas con información detallada (Dirección, Anfitrión, Facilitador, Teléfono con enlace a llamada y WhatsApp, ubicación en Google Maps).
  * Vista individual de cada casa con el listado actualizado de personas que la integran.
  * Sección de **Materiales de Apoyo** con guías y estudios semanales en PDF listos para visualizar o descargar.

* **Panel de Administración (Control Total):**
  * Acceso protegido por autenticación segura con contraseñas encriptadas (`password_hash` BCRYPT).
  * **Gestión de Casas (CRUD):** Crear, editar, activar/desactivar y eliminar casas.
  * **Gestión de Integrantes (CRUD):** Registrar nuevos miembros, asignar o reasignar a una casa específica y filtrar por grupo.
  * **Gestión de Materiales (CRUD):** Subir archivos PDF de enseñanza, título, semana y descripción.
  * **Gestión de Usuarios:** Crear nuevos administradores y cambiar contraseñas.

* **Diseño Visual:**
  * **Sin AI Slop:** Estilo editorial limpio, tipografía legible, colores cálidos/sobrios, componentes minimalistas y sin emojis.
  * 100% responsivo para teléfonos y computadoras.

---

## 🛠️ Tecnologías y Arquitectura

* **Backend:** PHP 8.x
* **Base de Datos:** PostgreSQL 15 (vía PDO `pdo_pgsql`)
* **Gestor de Base de Datos:** Adminer (en puerto `8080`)
* **Frontend:** HTML5, CSS3 personalizado y Vanilla JavaScript
* **Contenedores:** Docker Compose

---

## 🚀 Cómo Ejecutar la Aplicación

### 1. Iniciar Base de Datos PostgreSQL y Adminer con Docker

Asegúrate de tener Docker instalado y ejecutándose, luego abre una terminal en la carpeta del proyecto y ejecuta:

```bash
docker-compose up -d
```

Esto iniciará:
* **PostgreSQL** en `localhost:5432` con la base de datos `casas_amistad` e importará el esquema inicial de `schema.sql`.
* **Adminer** en `http://localhost:8080` para visualizar y administrar la base de datos de manera gráfica.

### 2. Acceso a Adminer para Visualizar la Base de Datos

Abre tu navegador en `http://localhost:8080` e ingresa con los siguientes datos:

* **Sistema:** PostgreSQL
* **Servidor:** `db` (o `127.0.0.1` si te conectas desde fuera del contenedor)
* **Usuario:** `postgres`
* **Contraseña:** `postgrespassword`
* **Base de datos:** `casas_amistad`

### 3. Iniciar el Servidor Web PHP

En la misma carpeta del proyecto, inicia el servidor incorporado de PHP:

```bash
php -S localhost:8000
```

Abre en tu navegador:
* **Sitio Público:** `http://localhost:8000`
* **Acceso Administración:** `http://localhost:8000/login.php`

---

## 🔑 Credenciales Iniciales de Prueba

* **Usuario:** `admin`
* **Contraseña:** `admin123`

---

## 📁 Estructura del Proyecto

```
APP IGLESIA/
├── docker-compose.yml       # Configuración para PostgreSQL y Adminer
├── schema.sql               # Esquema de tablas e inserción de datos iniciales
├── config.php               # Configuración general y constantes de entorno
├── db.php                   # Conexión PDO PostgreSQL (con fallback automático)
├── index.php                # Directorio público de Casas de Amistad
├── casa.php                 # Vista detallada de casa e integrantes
├── materiales.php           # Sección de descarga y lectura de materiales PDF
├── login.php                # Pantalla de acceso al panel de administración
├── logout.php              # Cierre de sesión
├── admin/                   # Panel de Administración
│   ├── index.php            # Dashboard general con métricas
│   ├── casas.php            # Administrador de Casas
│   ├── integrantes.php      # Administrador de Integrantes
│   ├── materiales.php       # Gestor de subida de PDF
│   └── usuarios.php         # Gestor de usuarios del sistema
├── assets/
│   ├── css/
│   │   └── style.css        # Sistema visual minimalista (Sin AI slop, sin emojis)
│   └── js/
│       └── main.js          # Lógica interactiva de filtro y modales
└── uploads/                 # Directorio donde se almacenan los PDFs subidos
```
