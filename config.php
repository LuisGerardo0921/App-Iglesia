<?php
// Configuración de Base de Datos PostgreSQL

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'casas_amistad');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgrespassword');

define('APP_NAME', 'Las Casas de Mi Amistad');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Iniciar sesión PHP de manera segura si no ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
