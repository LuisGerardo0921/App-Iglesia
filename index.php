<?php
require_once __DIR__ . '/db.php';

$pdo = getDB();

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

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Directorio de Grupos de Iglesia</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link active">Casas</a>
            <a href="materiales.php" class="nav-link">Materiales de Apoyo</a>
            <?php if (isLoggedIn()): ?>
                <a href="admin/index.php" class="nav-link admin-btn">Panel Admin</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Acceso Responsables</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="main-content">
    <section class="page-intro">
        <h1 class="page-title">Casas de Amistad</h1>
        <p class="page-description">
            Encuentra un grupo semanal cerca de tu ubicación. Consulta los horarios, la dirección y contacta directamente al anfitrión o facilitador.
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
                <div class="house-card house-card-item" 
                     data-name="<?= mb_strtolower(sanitize($casa['nombre'])) ?>"
                     data-sector="<?= mb_strtolower(sanitize($casa['ciudad_sector'])) ?>"
                     data-host="<?= mb_strtolower(sanitize($casa['anfitrion_nombre'])) ?>"
                     data-day="<?= sanitize($casa['dia_reunion']) ?>">
                    
                    <div>
                        <div class="house-header">
                            <?php if (!empty($casa['ciudad_sector'])): ?>
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

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Directorio de Casas y Materiales de Apoyo</p>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
