<?php
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // 1. Intentar conexión a PostgreSQL
        $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s", DB_HOST, DB_PORT, DB_NAME);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            ensureMigrations($pdo);
            return $pdo;
        } catch (PDOException $e) {
            // Intentar host alternativo 'db' si falló en localhost
            if (DB_HOST === '127.0.0.1' || DB_HOST === 'localhost') {
                try {
                    $dsnAlt = sprintf("pgsql:host=db;port=%s;dbname=%s", DB_PORT, DB_NAME);
                    $pdo = new PDO($dsnAlt, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    ensureMigrations($pdo);
                    return $pdo;
                } catch (PDOException $e2) {
                    // Fallback a SQLite local
                }
            }
        }

        // 2. Fallback local transparente con SQLite para desarrollo/vista previa
        $sqliteFile = __DIR__ . '/casas_amistad.sqlite';
        $initRequired = !file_exists($sqliteFile);
        
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if ($initRequired) {
            initSQLiteSchema($pdo);
        } else {
            ensureMigrations($pdo);
        }
    }
    return $pdo;
}

function ensureMigrations(PDO $pdo): void {
    try {
        // Verificar si la columna codigo_id existe en integrantes
        $pdo->query("SELECT codigo_id FROM integrantes LIMIT 1");
    } catch (Exception $e) {
        // Agregar columna codigo_id si no existe
        try {
            $pdo->exec("ALTER TABLE integrantes ADD COLUMN codigo_id VARCHAR(20)");
            // Asignar IDs por defecto a filas existentes
            $stmt = $pdo->query("SELECT id FROM integrantes ORDER BY id ASC");
            $rows = $stmt->fetchAll();
            $code = 1001;
            foreach ($rows as $row) {
                $upd = $pdo->prepare("UPDATE integrantes SET codigo_id = :code WHERE id = :id");
                $upd->execute(['code' => (string)$code, 'id' => $row['id']]);
                $code++;
            }
        } catch (Exception $e2) {
            // Ignorar errores de columna duplicada
        }
    }
}

function initSQLiteSchema(PDO $pdo): void {
    $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS casas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            direccion TEXT NOT NULL,
            ciudad_sector TEXT DEFAULT '',
            anfitrion_nombre TEXT NOT NULL,
            facilitador_nombre TEXT NOT NULL,
            telefono TEXT NOT NULL,
            dia_reunion TEXT NOT NULL,
            horario TEXT NOT NULL,
            mapa_url TEXT DEFAULT '',
            activa INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS integrantes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_id TEXT UNIQUE NOT NULL,
            casa_id INTEGER REFERENCES casas(id) ON DELETE SET NULL,
            nombre_completo TEXT NOT NULL,
            telefono TEXT DEFAULT '',
            email TEXT DEFAULT '',
            rol TEXT DEFAULT 'Integrante',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS materiales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT NOT NULL,
            descripcion TEXT DEFAULT '',
            semana TEXT NOT NULL,
            archivo_path TEXT NOT NULL,
            publicado_en DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            nombre TEXT NOT NULL,
            rol TEXT NOT NULL DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        INSERT INTO casas (nombre, direccion, ciudad_sector, anfitrion_nombre, facilitador_nombre, telefono, dia_reunion, horario, mapa_url, activa)
        VALUES 
        ('Casa de Amistad Norte - Valle Real', 'Calle Los Olivos #420, Col. Valle Real', 'Zona Norte', 'Carlos Ruiz', 'Mateo Fernández', '5551234567', 'Jueves', '19:30', 'https://www.google.com/maps/search/?api=1&query=Calle+Los+Olivos+420', 1),
        ('Casa de Amistad Sur - Coyoacán', 'Av. Hidalgo #108, Barrio Santa Catarina', 'Zona Sur', 'Elena Gómez', 'Sofía Morales', '5559876543', 'Viernes', '20:00', 'https://www.google.com/maps/search/?api=1&query=Av+Hidalgo+108+Coyoacan', 1),
        ('Casa de Amistad Poniente - Del Valle', 'Calle San Lorenzo #812, Col. Del Valle', 'Zona Poniente', 'Roberto Méndez', 'Daniel Vega', '5552345678', 'Miércoles', '19:00', 'https://www.google.com/maps/search/?api=1&query=Calle+San+Lorenzo+812+Del+Valle', 1),
        ('Casa de Amistad Oriente - Lindavista', 'Calle Matanzas #55, Col. Lindavista', 'Zona Oriente', 'Lucía Torres', 'Andrés Ramírez', '5558765432', 'Sábado', '18:00', 'https://www.google.com/maps/search/?api=1&query=Calle+Matanzas+55+Lindavista', 1);

        INSERT INTO integrantes (codigo_id, casa_id, nombre_completo, telefono, email, rol)
        VALUES 
        ('1001', 1, 'Carlos Ruiz', '5551234567', 'carlos.ruiz@email.com', 'Anfitrión'),
        ('1002', 1, 'Mateo Fernández', '5551112233', 'mateo.f@email.com', 'Facilitador'),
        ('1003', 1, 'Mariana Sánchez', '5553334455', 'mariana.s@email.com', 'Integrante'),
        ('1004', 1, 'Fernando López', '5554445566', 'fernando.l@email.com', 'Integrante'),
        ('1005', 2, 'Elena Gómez', '5559876543', 'elena.g@email.com', 'Anfitrión'),
        ('1006', 2, 'Sofía Morales', '5552223344', 'sofia.m@email.com', 'Facilitador'),
        ('1007', 2, 'Jorge Reyes', '5556667788', 'jorge.r@email.com', 'Integrante'),
        ('1008', 3, 'Roberto Méndez', '5552345678', 'roberto.m@email.com', 'Anfitrión'),
        ('1009', 3, 'Daniel Vega', '5557778899', 'daniel.v@email.com', 'Facilitador'),
        ('1010', 3, 'Claudia Jiménez', '5558889900', 'claudia.j@email.com', 'Integrante'),
        ('1011', 4, 'Lucía Torres', '5558765432', 'lucia.t@email.com', 'Anfitrión'),
        ('1012', 4, 'Andrés Ramírez', '5559990011', 'andres.r@email.com', 'Facilitador');

        INSERT INTO materiales (titulo, descripcion, semana, archivo_path)
        VALUES 
        ('Estudio 1: Fundamentos de la Comunidad', 'Guía de discusión para grupos pequeños enfocada en el apoyo mutuo y la comunión.', 'Semana 1 - Agosto 2026', 'uploads/estudio_semana_1.pdf'),
        ('Estudio 2: La Oración Diaria', 'Material práctico sobre hábitos de oración personal y en grupo.', 'Semana 2 - Agosto 2026', 'uploads/estudio_semana_2.pdf');

        INSERT INTO usuarios (username, password_hash, nombre, rol)
        VALUES 
        ('admin', '$2y$12$Tkx8vRVHijyDdRwF1lMnnufBMKkiFe0H5H7EWBwGFUAdckg12qiua', 'Administrador Principal', 'admin'),
        ('facilitador', '$2y$12$Tkx8vRVHijyDdRwF1lMnnufBMKkiFe0H5H7EWBwGFUAdckg12qiua', 'Facilitador General', 'facilitador');
SQL;
    $pdo->exec($sql);
}

// Helpers de Autenticación
function requireAuth(): void {
    if (!isLoggedIn()) {
        $loginUrl = file_exists('login.php') ? 'login.php' : '../login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'nombre' => $_SESSION['nombre'],
        'rol' => $_SESSION['rol']
    ];
}

function currentMemberProfile(): ?array {
    return $_SESSION['member'] ?? null;
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Helpers de Mensajes Flash
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
