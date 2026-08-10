# 🏠 Las Casas de Mi Amistad — App Web

Aplicación web ligera para organizar y consultar grupos pequeños ("casas de amistad"), horarios, materiales semanales y administración de integrantes.

## 🛠️ Tecnologías y Arquitectura

* **Backend / Hosting:** Google Apps Script (`Code.gs`)
* **Base de Datos:** Google Sheets (`Las Casas de Mi Amistad - BD`)
* **Archivos PDF:** Google Drive
* **Frontend:** Vanilla HTML5, CSS3, JavaScript ES6
* **Ubicaciones:** Enlaces directos a Google Maps Search (`https://www.google.com/maps/search/?api=1&query=...`)

---

## 📁 Estructura del Proyecto

* **`Code.gs`**: Servidor de backend Google Apps Script. Conecta con Google Sheets, maneja autenticación de PINs y API CRUD.
* **`Index.html`**: Plantilla principal que sirve de contenedor.
* **`PublicView.html`**: Interfaz pública para la comunidad (Buscador, tarjetas de casas, botones de WhatsApp, Llamada y Maps).
* **`AdminView.html`**: Panel de control del Administrador (Crear/editar casas, integrantes, materiales y usuarios).
* **`Styles.html` / `styles.css`**: Sistema visual con la paleta Lino (`#F5F0EB`) + Terracota (`#C05C46`) + Verde Oliva (`#1C2A26`).
* **`Scripts.html` / `app.js`**: Lógica de cliente, manejo de vistas, modales y comunicación con el backend.
* **`LocalPreview.html`**: Archivo HTML unificado para visualizar y probar la aplicación directamente en cualquier navegador web local.

---

## 💻 Vista Previa en Localhost

Puedes abrir directamente el archivo `LocalPreview.html` en tu navegador sin necesidad de internet ni servidor de Google.

* **PIN de Prueba Anfitrión:** `1001`
* **PIN de Prueba Administrador:** `1234`

---

## 🚀 Despliegue en Google Apps Script

1. Crea una hoja de cálculo en [Google Sheets](https://sheets.google.com).
2. Ve a **Extensiones** ➔ **Apps Script**.
3. Copia cada archivo (`Code.gs`, `Index.html`, `PublicView.html`, `AdminView.html`, `Styles.html`, `Scripts.html`) al editor de Apps Script.
4. Haz clic en **Desplegar** ➔ **Nuevo despliegue** ➔ Tipo: **Aplicación web**.
5. Configura **Quién tiene acceso:** *Cualquier persona*.
