# 🚀 GUÍA DE EJECUCIÓN Y CREDENCIALES

Esta guía contiene los comandos exactos y las credenciales de acceso para levantar y administrar la aplicación localmente.

---

## 1. Comandos de Terminal para Levantar la Aplicación

Abre la **Terminal** en tu Mac y ejecuta los siguientes comandos en orden:

### Paso 1: Entrar a la carpeta del proyecto
```bash
cd "/Users/luisgerardoricorojas/Desktop/APP IGLESIA"
```

### Paso 2: Iniciar la Base de Datos PostgreSQL y Adminer (Docker)
*(Asegúrate de que la aplicación Docker Desktop esté abierta en tu Mac)*
```bash
docker-compose up -d
```

### Paso 3: Iniciar el Servidor Web PHP
```bash
php -S localhost:8000
```

---

## 🌐 Enlaces de Acceso Local

* **Sitio Web Público:** [http://localhost:8000](http://localhost:8000)
* **Panel Administrador:** [http://localhost:8000/login.php](http://localhost:8000/login.php)
* **Adminer (Visualizador de Base de Datos):** [http://localhost:8080](http://localhost:8080)

---

## 🔑 Credenciales de Acceso

### A. Panel de Administración (Web App)
* **URL:** `http://localhost:8000/login.php`
* **Usuario:** `admin`
* **Contraseña:** `admin123`

---

### B. Adminer (Administrador de Base de Datos PostgreSQL)
* **URL:** `http://localhost:8080`
* **Motor de base de datos:** `PostgreSQL`
* **Servidor:** `db`
* **Usuario:** `postgres`
* **Contraseña:** `postgrespassword`
* **Base de datos:** `casas_amistad`

---

## 💡 IDs de Acceso de Prueba para Integrantes (Modal de Entrada)

Cuando los usuarios abran la página web por primera vez, pueden probar ingresando alguno de estos IDs de acceso:

* **ID `1001`**: Carlos Ruiz (Anfitrión - Casa Norte)
* **ID `1002`**: Mateo Fernández (Facilitador - Casa Norte)
* **ID `1005`**: Elena Gómez (Anfitrión - Casa Sur)
* **ID `1008`**: Roberto Méndez (Anfitrión - Casa Poniente)

---

## 🛑 Detener los Servicios

* Para detener el servidor web PHP: Presiona `Ctrl + C` en la terminal.
* Para detener los contenedores de Docker:
  ```bash
  docker-compose down
  ```
