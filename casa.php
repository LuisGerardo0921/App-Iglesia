<?php
require_once __DIR__ . '/db.php';

$pdo = getDB();
$casa_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$casa_id) {
    header('Location: index.php');
    exit;
}

// Obtener datos de la casa
$stmt = $pdo->prepare("SELECT * FROM casas WHERE id = :id AND activa = TRUE");
$stmt->execute(['id' => $casa_id]);
$casa = $stmt->fetch();

if (!$casa) {
    header('Location: index.php');
    exit;
}

// Obtener integrantes asignados a esta casa
$stmtMembers = $pdo->prepare("SELECT * FROM integrantes WHERE casa_id = :casa_id ORDER BY rol DESC, nombre_completo ASC");
$stmtMembers->execute(['casa_id' => $casa_id]);
$integrantes = $stmtMembers->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($casa['nombre']) ?> | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Detalle de Grupo</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">Volver al Directorio</a>
            <a href="materiales.php" class="nav-link">Materiales</a>
        </nav>
    </div>
</header>

<main class="main-content">
    <div style="margin-bottom: 1.5rem;">
        <a href="index.php" class="btn btn-outline btn-sm">&larr; Volver al listado</a>
    </div>

    <div class="form-card" style="max-width: 800px; margin: 0 auto;">
        <div class="page-intro" style="margin-bottom: 1.5rem; padding-bottom: 1rem;">
            <?php if (!empty($casa['ciudad_sector'])): ?>
                <span class="house-sector"><?= sanitize($casa['ciudad_sector']) ?></span>
            <?php endif; ?>
            <h1 class="page-title" style="font-size: 2rem;"><?= sanitize($casa['nombre']) ?></h1>
            <p class="page-description">Reunión los <?= sanitize($casa['dia_reunion']) ?> a las <?= sanitize($casa['horario']) ?> hrs.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.25rem;">
                    Información de Contacto
                </h3>
                <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <strong>Dirección:</strong> <?= sanitize($casa['direccion']) ?>
                </p>
                <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <strong>Anfitrión:</strong> <?= sanitize($casa['anfitrion_nombre']) ?>
                </p>
                <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <strong>Facilitador:</strong> <?= sanitize($casa['facilitador_nombre']) ?>
                </p>
                <p style="margin-bottom: 1rem; font-size: 0.9rem;">
                    <strong>Teléfono:</strong> <?= sanitize($casa['telefono']) ?>
                </p>

                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="tel:<?= preg_replace('/[^0-9]/', '', $casa['telefono']) ?>" class="btn btn-primary btn-sm">
                        Llamar por teléfono
                    </a>
                    <?php 
                        $whatsapp_num = preg_replace('/[^0-9]/', '', $casa['telefono']);
                        if (strlen($whatsapp_num) === 10) $whatsapp_num = '52' . $whatsapp_num;
                    ?>
                    <a href="https://wa.me/<?= $whatsapp_num ?>" target="_blank" class="btn btn-outline btn-sm">
                        Enviar WhatsApp
                    </a>
                    <?php if (!empty($casa['mapa_url'])): ?>
                        <a href="<?= sanitize($casa['mapa_url']) ?>" target="_blank" class="btn btn-outline btn-sm">
                            Ver en Google Maps
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.25rem;">
                    Integrantes del Grupo (<?= count($integrantes) ?>)
                </h3>
                <?php if (empty($integrantes)): ?>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">No hay integrantes asignados aún a esta casa.</p>
                <?php else: ?>
                    <ul class="members-list">
                        <?php foreach ($integrantes as $m): ?>
                            <li class="member-item">
                                <span style="font-weight: 500; font-size: 0.9rem;"><?= sanitize($m['nombre_completo']) ?></span>
                                <span class="member-role"><?= sanitize($m['rol']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Directorio de Casas</p>
</footer>

</body>
</html>
