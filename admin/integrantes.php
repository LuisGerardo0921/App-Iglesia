<?php
require_once __DIR__ . '/../db.php';
requireAuth();

$pdo = getDB();
$action = $_GET['action'] ?? 'listar';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$filter_casa = filter_input(INPUT_GET, 'casa_id', FILTER_VALIDATE_INT);

$errors = [];

// Obtener lista de casas para dropdowns y filtros
$casas = $pdo->query("SELECT id, nombre, ciudad_sector FROM casas ORDER BY nombre ASC")->fetchAll();

// Procesar Guardar (Crear / Editar Integrante)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = trim($_POST['rol'] ?? 'Integrante');
    $casa_id = filter_input(INPUT_POST, 'casa_id', FILTER_VALIDATE_INT) ?: null;
    $post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($nombre_completo)) $errors[] = 'El nombre completo es obligatorio.';

    if (empty($errors)) {
        if ($post_id) {
            // Actualizar
            $stmt = $pdo->prepare("
                UPDATE integrantes SET 
                    nombre_completo = :nombre_completo,
                    telefono = :telefono,
                    email = :email,
                    rol = :rol,
                    casa_id = :casa_id
                WHERE id = :id
            ");
            $stmt->execute([
                'nombre_completo' => $nombre_completo,
                'telefono' => $telefono,
                'email' => $email,
                'rol' => $rol,
                'casa_id' => $casa_id,
                'id' => $post_id
            ]);
            setFlash('success', 'Integrante actualizado correctamente.');
        } else {
            // Insertar
            $stmt = $pdo->prepare("
                INSERT INTO integrantes (nombre_completo, telefono, email, rol, casa_id)
                VALUES (:nombre_completo, :telefono, :email, :rol, :casa_id)
            ");
            $stmt->execute([
                'nombre_completo' => $nombre_completo,
                'telefono' => $telefono,
                'email' => $email,
                'rol' => $rol,
                'casa_id' => $casa_id
            ]);
            setFlash('success', 'Integrante registrado con éxito.');
        }
        header('Location: integrantes.php' . ($casa_id ? '?casa_id=' . $casa_id : ''));
        exit;
    }
}

// Procesar Eliminar
if ($action === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM integrantes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    setFlash('success', 'Integrante eliminado correctamente.');
    header('Location: integrantes.php' . ($filter_casa ? '?casa_id=' . $filter_casa : ''));
    exit;
}

// Obtener Integrante para edición
$integranteActual = null;
if (($action === 'editar' || !empty($errors)) && $id) {
    $stmt = $pdo->prepare("SELECT * FROM integrantes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $integranteActual = $stmt->fetch();
}

// Consultar lista de integrantes
$query = "
    SELECT i.*, c.nombre AS nombre_casa 
    FROM integrantes i 
    LEFT JOIN casas c ON i.casa_id = c.id 
";
$params = [];

if ($filter_casa) {
    $query .= " WHERE i.casa_id = :casa_id ";
    $params['casa_id'] = $filter_casa;
}

$query .= " ORDER BY i.nombre_completo ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$integrantes = $stmt->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Integrantes | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Gestión de Integrantes</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">Inicio Admin</a>
            <a href="casas.php" class="nav-link">Casas</a>
            <a href="integrantes.php" class="nav-link active">Integrantes</a>
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

    <?php if ($action === 'nuevo' || $action === 'editar'): ?>
        <!-- Formulario Crear/Editar Integrante -->
        <div class="form-card" style="max-width: 600px; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h2 style="font-family: var(--font-serif); font-size: 1.4rem;">
                    <?= $integranteActual ? 'Editar Integrante' : 'Registrar Nuevo Integrante' ?>
                </h2>
                <a href="integrantes.php" class="btn btn-outline btn-sm">Cancelar</a>
            </div>

            <form action="integrantes.php?action=<?= $action ?>" method="POST">
                <?php if ($integranteActual): ?>
                    <input type="hidden" name="id" value="<?= $integranteActual['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" placeholder="Ej. María Elena López" value="<?= sanitize($integranteActual['nombre_completo'] ?? '') ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Ej. 5551112233" value="<?= sanitize($integranteActual['telefono'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="maria@email.com" value="<?= sanitize($integranteActual['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="rol" class="form-label">Rol en el Grupo</label>
                        <select id="rol" name="rol" class="form-control">
                            <?php 
                            $roles = ['Integrante', 'Anfitrión', 'Facilitador'];
                            $rolSel = $integranteActual['rol'] ?? 'Integrante';
                            foreach ($roles as $r): ?>
                                <option value="<?= $r ?>" <?= $rolSel === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="casa_id" class="form-label">Casa Asignada</label>
                        <select id="casa_id" name="casa_id" class="form-control">
                            <option value="">-- Sin Asignar --</option>
                            <?php 
                            $casaSel = $integranteActual['casa_id'] ?? $filter_casa;
                            foreach ($casas as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (string)$casaSel === (string)$c['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($c['nombre']) ?> (<?= sanitize($c['ciudad_sector']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?= $integranteActual ? 'Guardar Cambios' : 'Registrar Integrante' ?>
                    </button>
                    <a href="integrantes.php" class="btn btn-outline" style="flex: 1;">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Header y Filtro -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 class="page-title" style="font-size: 1.8rem;">Registro de Integrantes</h1>
            <p class="page-description">Consulta y asigna personas a los grupos de casa.</p>
        </div>
        <div>
            <a href="integrantes.php?action=nuevo<?= $filter_casa ? '&casa_id=' . $filter_casa : '' ?>" class="btn btn-primary">+ Registrar Integrante</a>
        </div>
    </div>

    <!-- Filtro por Casa -->
    <div class="search-toolbar" style="margin-bottom: 1.5rem;">
        <label for="casaFilter" style="font-size: 0.88rem; font-weight: 600;">Filtrar por Casa:</label>
        <select id="casaFilter" class="filter-select" onchange="window.location.href = this.value ? 'integrantes.php?casa_id=' + this.value : 'integrantes.php'">
            <option value="">Todas las Casas</option>
            <?php foreach ($casas as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string)$filter_casa === (string)$c['id'] ? 'selected' : '' ?>>
                    <?= sanitize($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($filter_casa): ?>
            <a href="integrantes.php" class="btn btn-outline btn-sm">Limpiar Filtro</a>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Rol</th>
                    <th>Casa Asignada</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($integrantes)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No se encontraron integrantes.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($integrantes as $m): ?>
                        <tr>
                            <td><strong><?= sanitize($m['nombre_completo']) ?></strong></td>
                            <td><span class="member-role"><?= sanitize($m['rol']) ?></span></td>
                            <td>
                                <?php if ($m['nombre_casa']): ?>
                                    <a href="casas.php?action=editar&id=<?= $m['casa_id'] ?>" style="font-weight: 500;">
                                        <?= sanitize($m['nombre_casa']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); italic;">Sin casa asignada</span>
                                <?php endif; ?>
                            </td>
                            <td><?= sanitize($m['telefono']) ?></td>
                            <td><?= sanitize($m['email']) ?></td>
                            <td>
                                <a href="integrantes.php?action=editar&id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                                <a href="integrantes.php?action=eliminar&id=<?= $m['id'] ?><?= $filter_casa ? '&casa_id=' . $filter_casa : '' ?>" onclick="return confirmDelete('¿Estás seguro de eliminar a este integrante?')" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Gestión de Integrantes</p>
</footer>

<script src="../assets/js/main.js"></script>
</body>
</html>
