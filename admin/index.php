<?php
require_once __DIR__ . '/../db.php';
requireAuth();

$user = currentUser();
$pdo = getDB();

// Métricas clave
$totalCasas = $pdo->query("SELECT COUNT(*) FROM casas")->fetchColumn();
$casasActivas = $pdo->query("SELECT COUNT(*) FROM casas WHERE activa = TRUE")->fetchColumn();
$totalIntegrantes = $pdo->query("SELECT COUNT(*) FROM integrantes")->fetchColumn();
$totalMateriales = $pdo->query("SELECT COUNT(*) FROM materiales")->fetchColumn();

// Casas recientes
$recientesCasas = $pdo->query("SELECT * FROM casas ORDER BY created_at DESC LIMIT 5")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Panel de Control General</span>
        </div>
        <nav class="nav-links">
            <a href="../index.php" target="_blank" class="nav-link">Ver Sitio Público</a>
            <a href="casas.php" class="nav-link">Casas</a>
            <a href="integrantes.php" class="nav-link">Integrantes</a>
            <a href="materiales.php" class="nav-link">Materiales</a>
            <a href="usuarios.php" class="nav-link">Usuarios</a>
            <a href="../logout.php" class="nav-link" style="color: #a83232;">Cerrar Sesión</a>
        </nav>
    </div>
</header>

<main class="main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div>
    <?php endif; ?>

    <section class="page-intro" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title">Bienvenido, <?= sanitize($user['nombre']) ?></h1>
            <p class="page-description">Control completo sobre las casas de amistad, asignación de integrantes y materiales semanales.</p>
        </div>
        <div>
            <a href="http://localhost:8080" target="_blank" class="btn btn-outline btn-sm">
                Abrir Base de Datos en Adminer &rarr;
            </a>
        </div>
    </section>

    <!-- Cards de Métricas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
        <div class="form-card" style="padding: 1.25rem; text-align: center;">
            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Casas Registradas</span>
            <div style="font-size: 2.2rem; font-family: var(--font-serif); font-weight: bold; color: var(--accent-primary); margin: 0.25rem 0;">
                <?= $totalCasas ?>
            </div>
            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= $casasActivas ?> activas actualmente</span>
        </div>

        <div class="form-card" style="padding: 1.25rem; text-align: center;">
            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Total Integrantes</span>
            <div style="font-size: 2.2rem; font-family: var(--font-serif); font-weight: bold; color: var(--text-main); margin: 0.25rem 0;">
                <?= $totalIntegrantes ?>
            </div>
            <span style="font-size: 0.8rem; color: var(--text-muted);">Asignados a grupos</span>
        </div>

        <div class="form-card" style="padding: 1.25rem; text-align: center;">
            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Materiales PDF</span>
            <div style="font-size: 2.2rem; font-family: var(--font-serif); font-weight: bold; color: var(--text-main); margin: 0.25rem 0;">
                <?= $totalMateriales ?>
            </div>
            <span style="font-size: 0.8rem; color: var(--text-muted);">Guias de estudio</span>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div style="margin-bottom: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="casas.php?action=nueva" class="btn btn-primary">+ Nueva Casa de Amistad</a>
        <a href="integrantes.php?action=nuevo" class="btn btn-outline">+ Registrar Integrante</a>
        <a href="materiales.php?action=nuevo" class="btn btn-outline">+ Subir Material PDF</a>
    </div>

    <!-- Tabla Resumen de Casas -->
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-family: var(--font-serif); font-size: 1.35rem; font-weight: 600;">Casas de Amistad Recientes</h2>
        <a href="casas.php" class="btn btn-outline btn-sm">Ver Todas &rarr;</a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre de la Casa</th>
                    <th>Sector / Zona</th>
                    <th>Anfitrión</th>
                    <th>Facilitador</th>
                    <th>Día y Hora</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recientesCasas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted);">No hay casas registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recientesCasas as $c): ?>
                        <tr>
                            <td><strong><?= sanitize($c['nombre']) ?></strong></td>
                            <td><?= sanitize($c['ciudad_sector']) ?></td>
                            <td><?= sanitize($c['anfitrion_nombre']) ?></td>
                            <td><?= sanitize($c['facilitador_nombre']) ?></td>
                            <td><?= sanitize($c['dia_reunion']) ?> <?= sanitize($c['horario']) ?></td>
                            <td>
                                <?php if ($c['activa']): ?>
                                    <span style="color: #2e7d32; font-weight: 600; font-size: 0.8rem;">Activa</span>
                                <?php else: ?>
                                    <span style="color: #c62828; font-weight: 600; font-size: 0.8rem;">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="casas.php?action=editar&id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Panel Administrativo</p>
</footer>

</body>
</html>
