<?php
require_once __DIR__ . '/db.php';

$pdo = getDB();
$member = currentMemberProfile();
$flash = getFlash();

// Obtener todas las casas activas con el conteo de integrantes
$stmt = $pdo->query("
    SELECT c.*, COUNT(i.id) AS total_integrantes 
    FROM casas c 
    LEFT JOIN integrantes i ON c.id = i.casa_id 
    WHERE c.activa = TRUE 
    GROUP BY c.id 
    ORDER BY c.nombre ASC
");
$casas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Casas | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if ($member): ?>
    <!-- Barra Superior de Estado del Usuario -->
    <div class="user-status-bar">
        <div class="user-status-container">
            <div class="user-status-info">
                <span>Hola, <strong><?= sanitize($member['nombre_completo']) ?></strong></span>
                <span class="user-role-badge"><?= sanitize($member['rol']) ?></span>
                <?php if (!empty($member['casa_nombre'])): ?>
                    <span style="opacity: 0.9;">&bull; Casa: <strong><?= sanitize($member['casa_nombre']) ?></strong></span>
                <?php endif; ?>
            </div>
            <div>
                <a href="auth_id.php?action=logout" class="user-status-logout">Cambiar ID / Salir</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Directorio de Grupos de Iglesia</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link active">Casas</a>
            <a href="materiales.php" class="nav-link">Materiales de Apoyo</a>
            <?php if (!$member): ?>
                <button onclick="openIdModal()" class="btn btn-primary btn-sm" style="font-size: 0.85rem;">
                    Ingresar con ID
                </button>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <a href="admin/index.php" class="nav-link admin-btn">Panel Admin</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Acceso Responsables</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div>
    <?php endif; ?>

    <section class="page-intro">
        <h1 class="page-title">
            <?= $member ? "Bienvenido, " . sanitize($member['nombre_completo']) : "Casas de Amistad" ?>
        </h1>
        <p class="page-description">
            <?php if ($member): ?>
                <?php if (!empty($member['casa_nombre'])): ?>
                    Estás asignado a <strong><?= sanitize($member['casa_nombre']) ?></strong>. Revisa los detalles de tu reunión semanal y los integrantes de tu grupo.
                <?php else: ?>
                    Bienvenido a la comunidad. Tu perfil está registrado. Pide al Administrador que te asigne a una Casa de Amistad.
                <?php endif; ?>
            <?php else: ?>
                Encuentra un grupo semanal cerca de tu ubicación. Ingresa tu ID de acceso proporcionado por el Administrador o explora el directorio general.
            <?php endif; ?>
        </p>
    </section>

    <!-- Toolbar de Búsqueda y Filtro -->
    <div class="search-toolbar">
        <div class="search-input-group">
            <input type="text" id="searchHouses" class="search-input" placeholder="Buscar por nombre, sector o anfitrión...">
        </div>
        <div>
            <select id="filterDay" class="filter-select">
                <option value="">Todos los días</option>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miércoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
                <option value="Sábado">Sábado</option>
                <option value="Domingo">Domingo</option>
            </select>
        </div>
    </div>

    <!-- Lista de Casas -->
    <?php if (empty($casas)): ?>
        <p style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
            No hay casas registradas en este momento.
        </p>
    <?php else: ?>
        <div class="houses-grid">
            <?php foreach ($casas as $casa): ?>
                <?php 
                    $isMyHouse = $member && ((int)$member['casa_id'] === (int)$casa['id']);
                ?>
                <div class="house-card house-card-item <?= $isMyHouse ? 'my-house' : '' ?>" 
                     data-name="<?= mb_strtolower(sanitize($casa['nombre'])) ?>"
                     data-sector="<?= mb_strtolower(sanitize($casa['ciudad_sector'])) ?>"
                     data-host="<?= mb_strtolower(sanitize($casa['anfitrion_nombre'])) ?>"
                     data-day="<?= sanitize($casa['dia_reunion']) ?>">
                    
                    <div>
                        <div class="house-header">
                            <?php if ($isMyHouse): ?>
                                <span class="house-sector" style="background-color: var(--accent-primary); color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; margin-bottom: 0.4rem;">
                                    Tu Casa Asignada
                                </span>
                            <?php elseif (!empty($casa['ciudad_sector'])): ?>
                                <span class="house-sector"><?= sanitize($casa['ciudad_sector']) ?></span>
                            <?php endif; ?>
                            <h2 class="house-name"><?= sanitize($casa['nombre']) ?></h2>
                        </div>

                        <div class="house-details">
                            <div class="detail-row">
                                <span class="detail-label">Reunión</span>
                                <span class="badge-schedule">
                                    <?= sanitize($casa['dia_reunion']) ?> - <?= sanitize($casa['horario']) ?> hrs
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Dirección</span>
                                <span class="detail-value"><?= sanitize($casa['direccion']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Anfitrión</span>
                                <span class="detail-value"><?= sanitize($casa['anfitrion_nombre']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Facilitador</span>
                                <span class="detail-value"><?= sanitize($casa['facilitador_nombre']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Integrantes</span>
                                <span class="detail-value"><?= (int)$casa['total_integrantes'] ?> personas</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="casa.php?id=<?= $casa['id'] ?>" class="btn btn-outline" style="flex: 1;">
                            Ver Integrantes
                        </a>
                        <?php if (!empty($casa['mapa_url'])): ?>
                            <a href="<?= sanitize($casa['mapa_url']) ?>" target="_blank" class="btn btn-primary" style="flex: 1;">
                                Ubicación Mapa
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Modal Emergente de Ingreso por ID de Acceso -->
<div id="idAccessModal" class="modal-overlay <?= (!$member && !isset($_GET['noModal'])) ? 'auto-open' : '' ?>">
    <div class="modal-card">
        <button type="button" class="modal-close-btn" onclick="closeIdModal()">&times;</button>
        <h2 class="modal-title">Ingreso con tu ID</h2>
        <p class="modal-subtitle">
            Ingresa el **ID de Acceso** de 4 dígitos proporcionado por el Administrador de tu Casa de Amistad.
        </p>

        <form action="auth_id.php" method="POST">
            <div class="form-group">
                <label for="modalCodigoId" class="form-label" style="text-align: center;">Número de ID de Acceso</label>
                <input type="text" id="modalCodigoId" name="codigo_id" class="form-control" placeholder="Ej. 1001" style="text-align: center; font-size: 1.3rem; letter-spacing: 0.1em; font-weight: bold;" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; font-size: 1rem; padding: 0.75rem;">
                Entrar a Mi Perfil
            </button>
        </form>

        <div style="margin-top: 1.25rem; text-align: center;">
            <button type="button" onclick="closeIdModal()" style="background: none; border: none; font-size: 0.85rem; color: var(--text-muted); cursor: pointer; text-decoration: underline;">
                Explorar el directorio sin ID
            </button>
        </div>
    </div>
</div>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Directorio de Casas y Materiales de Apoyo</p>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
