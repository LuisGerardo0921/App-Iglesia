<?php
require_once __DIR__ . '/../db.php';
requireAuth();

$pdo = getDB();
$action = $_GET['action'] ?? 'listar';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$errors = [];

// Procesar Guardar (Crear / Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad_sector = trim($_POST['ciudad_sector'] ?? '');
    $anfitrion_nombre = trim($_POST['anfitrion_nombre'] ?? '');
    $facilitador_nombre = trim($_POST['facilitador_nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $dia_reunion = trim($_POST['dia_reunion'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $mapa_url = trim($_POST['mapa_url'] ?? '');
    $activa = isset($_POST['activa']) ? true : false;
    $post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($nombre)) $errors[] = 'El nombre de la casa es obligatorio.';
    if (empty($direccion)) $errors[] = 'La dirección es obligatoria.';
    if (empty($anfitrion_nombre)) $errors[] = 'El nombre del anfitrión es obligatorio.';
    if (empty($facilitador_nombre)) $errors[] = 'El nombre del facilitador es obligatorio.';
    if (empty($telefono)) $errors[] = 'El teléfono de contacto es obligatorio.';

    if (empty($errors)) {
        if ($post_id) {
            // Actualizar
            $stmt = $pdo->prepare("
                UPDATE casas SET 
                    nombre = :nombre,
                    direccion = :direccion,
                    ciudad_sector = :ciudad_sector,
                    anfitrion_nombre = :anfitrion_nombre,
                    facilitador_nombre = :facilitador_nombre,
                    telefono = :telefono,
                    dia_reunion = :dia_reunion,
                    horario = :horario,
                    mapa_url = :mapa_url,
                    activa = :activa
                WHERE id = :id
            ");
            $stmt->execute([
                'nombre' => $nombre,
                'direccion' => $direccion,
                'ciudad_sector' => $ciudad_sector,
                'anfitrion_nombre' => $anfitrion_nombre,
                'facilitador_nombre' => $facilitador_nombre,
                'telefono' => $telefono,
                'dia_reunion' => $dia_reunion,
                'horario' => $horario,
                'mapa_url' => $mapa_url,
                'activa' => $activa ? 'true' : 'false',
                'id' => $post_id
            ]);
            setFlash('success', 'Casa actualizada correctamente.');
        } else {
            // Insertar
            $stmt = $pdo->prepare("
                INSERT INTO casas (nombre, direccion, ciudad_sector, anfitrion_nombre, facilitador_nombre, telefono, dia_reunion, horario, mapa_url, activa)
                VALUES (:nombre, :direccion, :ciudad_sector, :anfitrion_nombre, :facilitador_nombre, :telefono, :dia_reunion, :horario, :mapa_url, :activa)
            ");
            $stmt->execute([
                'nombre' => $nombre,
                'direccion' => $direccion,
                'ciudad_sector' => $ciudad_sector,
                'anfitrion_nombre' => $anfitrion_nombre,
                'facilitador_nombre' => $facilitador_nombre,
                'telefono' => $telefono,
                'dia_reunion' => $dia_reunion,
                'horario' => $horario,
                'mapa_url' => $mapa_url,
                'activa' => $activa ? 'true' : 'false'
            ]);
            setFlash('success', 'Casa registrada con éxito.');
        }
        header('Location: casas.php');
        exit;
    }
}

// Procesar Eliminar
if ($action === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM casas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    setFlash('success', 'Casa eliminada correctamente.');
    header('Location: casas.php');
    exit;
}

// Obtener Casa para edición
$casaActual = null;
if (($action === 'editar' || !empty($errors)) && $id) {
    $stmt = $pdo->prepare("SELECT * FROM casas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $casaActual = $stmt->fetch();
}

// Obtener todas las casas
$casas = $pdo->query("
    SELECT c.*, COUNT(i.id) AS total_integrantes 
    FROM casas c 
    LEFT JOIN integrantes i ON c.id = i.casa_id 
    GROUP BY c.id 
    ORDER BY c.nombre ASC
")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Casas | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Gestión de Casas</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">Inicio Admin</a>
            <a href="casas.php" class="nav-link active">Casas</a>
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

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-left: 1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?= sanitize($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($action === 'nueva' || $action === 'editar'): ?>
        <!-- Formulario Crear/Editar Casa -->
        <div class="form-card" style="max-width: 680px; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h2 style="font-family: var(--font-serif); font-size: 1.4rem;">
                    <?= $casaActual ? 'Editar Casa de Amistad' : 'Nueva Casa de Amistad' ?>
                </h2>
                <a href="casas.php" class="btn btn-outline btn-sm">Cancelar</a>
            </div>

            <form action="casas.php?action=<?= $action ?>" method="POST">
                <?php if ($casaActual): ?>
                    <input type="hidden" name="id" value="<?= $casaActual['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre o Identificación de la Casa *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej. Casa de Amistad Valle Real" value="<?= sanitize($casaActual['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="ciudad_sector" class="form-label">Sector / Zona</label>
                        <input type="text" id="ciudad_sector" name="ciudad_sector" class="form-control" placeholder="Ej. Zona Norte" value="<?= sanitize($casaActual['ciudad_sector'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono de Contacto *</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Ej. 5551234567" value="<?= sanitize($casaActual['telefono'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección Completa *</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Ej. Calle Los Olivos #420, Col. Valle Real" value="<?= sanitize($casaActual['direccion'] ?? '') ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="anfitrion_nombre" class="form-label">Nombre del Anfitrión *</label>
                        <input type="text" id="anfitrion_nombre" name="anfitrion_nombre" class="form-control" value="<?= sanitize($casaActual['anfitrion_nombre'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="facilitador_nombre" class="form-label">Nombre del Facilitador *</label>
                        <input type="text" id="facilitador_nombre" name="facilitador_nombre" class="form-control" value="<?= sanitize($casaActual['facilitador_nombre'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="dia_reunion" class="form-label">Día de Reunión</label>
                        <select id="dia_reunion" name="dia_reunion" class="form-control">
                            <?php 
                            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                            $diaSel = $casaActual['dia_reunion'] ?? 'Jueves';
                            foreach ($dias as $d): ?>
                                <option value="<?= $d ?>" <?= $diaSel === $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="horario" class="form-label">Horario</label>
                        <input type="text" id="horario" name="horario" class="form-control" placeholder="Ej. 19:30" value="<?= sanitize($casaActual['horario'] ?? '19:30') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="mapa_url" class="form-label">Enlace a Google Maps (Ubicación)</label>
                    <input type="url" id="mapa_url" name="mapa_url" class="form-control" placeholder="https://maps.google.com/..." value="<?= sanitize($casaActual['mapa_url'] ?? '') ?>">
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                    <input type="checkbox" id="activa" name="activa" value="1" <?= (!isset($casaActual) || $casaActual['activa']) ? 'checked' : '' ?>>
                    <label for="activa" style="cursor: pointer; font-size: 0.9rem;">Casa Activa (Visible en el directorio público)</label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?= $casaActual ? 'Guardar Cambios' : 'Crear Casa' ?>
                    </button>
                    <a href="casas.php" class="btn btn-outline" style="flex: 1;">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Tabla Principal de Casas -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 class="page-title" style="font-size: 1.8rem;">Directorio de Casas</h1>
            <p class="page-description">Administra los datos de cada casa de amistad.</p>
        </div>
        <a href="casas.php?action=nueva" class="btn btn-primary">+ Nueva Casa</a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Casa / Sector</th>
                    <th>Anfitrión / Facilitador</th>
                    <th>Teléfono</th>
                    <th>Horario</th>
                    <th>Integrantes</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($casas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay casas registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($casas as $c): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($c['nombre']) ?></strong>
                                <?php if (!empty($c['ciudad_sector'])): ?>
                                    <br><small style="color: var(--text-muted);"><?= sanitize($c['ciudad_sector']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem;">Anfitrión: <?= sanitize($c['anfitrion_nombre']) ?></span><br>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Facilitador: <?= sanitize($c['facilitador_nombre']) ?></span>
                            </td>
                            <td><?= sanitize($c['telefono']) ?></td>
                            <td><?= sanitize($c['dia_reunion']) ?><br><small style="color: var(--text-muted);"><?= sanitize($c['horario']) ?> hrs</small></td>
                            <td>
                                <a href="integrantes.php?casa_id=<?= $c['id'] ?>" style="text-decoration: underline;">
                                    <?= (int)$c['total_integrantes'] ?> miembros
                                </a>
                            </td>
                            <td>
                                <?php if ($c['activa']): ?>
                                    <span style="color: #2e7d32; font-weight: 600; font-size: 0.8rem;">Activa</span>
                                <?php else: ?>
                                    <span style="color: #c62828; font-weight: 600; font-size: 0.8rem;">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="casas.php?action=editar&id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                                <a href="casas.php?action=eliminar&id=<?= $c['id'] ?>" onclick="return confirmDelete('¿Estás seguro de eliminar esta casa?')" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Gestión de Casas</p>
</footer>

<script src="../assets/js/main.js"></script>
</body>
</html>
