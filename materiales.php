<?php
require_once __DIR__ . '/db.php';

$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM materiales ORDER BY publicado_en DESC");
$materiales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales de Apoyo | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Materiales Semanales</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">Casas</a>
            <a href="materiales.php" class="nav-link active">Materiales de Apoyo</a>
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
        <h1 class="page-title">Materiales de Enseñanza</h1>
        <p class="page-description">
            Recursos y guías de estudio en PDF para anfitriones y facilitadores. Puedes previsualizarlos o descargarlos para las reuniones semanales.
        </p>
    </section>

    <?php if (empty($materiales)): ?>
        <p style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
            No hay materiales publicados actualmente.
        </p>
    <?php else: ?>
        <div class="materials-grid">
            <?php foreach ($materiales as $mat): ?>
                <div class="material-card">
                    <div>
                        <div class="material-tag"><?= sanitize($mat['semana']) ?></div>
                        <h2 class="material-title"><?= sanitize($mat['titulo']) ?></h2>
                        <p class="material-desc"><?= sanitize($mat['descripcion']) ?></p>
                    </div>

                    <div class="card-actions" style="margin-top: 1rem;">
                        <?php if (file_exists(__DIR__ . '/' . $mat['archivo_path'])): ?>
                            <a href="<?= sanitize($mat['archivo_path']) ?>" target="_blank" class="btn btn-primary" style="flex: 1;">
                                Abrir PDF
                            </a>
                            <a href="<?= sanitize($mat['archivo_path']) ?>" download class="btn btn-outline" style="flex: 1;">
                                Descargar
                            </a>
                        <?php else: ?>
                            <span class="btn btn-outline" style="width: 100%; color: var(--text-muted); cursor: not-allowed;">
                                Archivo pendiente
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Materiales de Apoyo</p>
</footer>

</body>
</html>
