<?php
require_once __DIR__ . '/../db.php';
requireAuth();

$pdo = getDB();
$action = $_GET['action'] ?? 'listar';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$errors = [];

// Procesar Guardar (Crear / Editar Contraseña)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $rol = trim($_POST['rol'] ?? 'admin');
    $password = $_POST['password'] ?? '';
    $post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($username)) $errors[] = 'El nombre de usuario es obligatorio.';
    if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';

    if (!$post_id && empty($password)) {
        $errors[] = 'La contraseña es obligatoria para nuevos usuarios.';
    }

    if (empty($errors)) {
        if ($post_id) {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET username = :username, nombre = :nombre, rol = :rol, password_hash = :hash WHERE id = :id");
                $stmt->execute([
                    'username' => $username,
                    'nombre' => $nombre,
                    'rol' => $rol,
                    'hash' => $hash,
                    'id' => $post_id
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET username = :username, nombre = :nombre, rol = :rol WHERE id = :id");
                $stmt->execute([
                    'username' => $username,
                    'nombre' => $nombre,
                    'rol' => $rol,
                    'id' => $post_id
                ]);
            }
            setFlash('success', 'Usuario actualizado correctamente.');
        } else {
            // Verificar si nombre de usuario ya existe
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE username = :username");
            $stmtCheck->execute(['username' => $username]);
            if ($stmtCheck->fetch()) {
                $errors[] = 'Ese nombre de usuario ya existe.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, nombre, rol, password_hash) VALUES (:username, :nombre, :rol, :hash)");
                $stmt->execute([
                    'username' => $username,
                    'nombre' => $nombre,
                    'rol' => $rol,
                    'hash' => $hash
                ]);
                setFlash('success', 'Usuario creado correctamente.');
            }
        }

        if (empty($errors)) {
            header('Location: usuarios.php');
            exit;
        }
    }
}

// Procesar Eliminar
if ($action === 'eliminar' && $id) {
    // Evitar auto-eliminación
    if ($id === (int)$_SESSION['user_id']) {
        setFlash('danger', 'No puedes eliminar tu propia cuenta en sesión activa.');
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        setFlash('success', 'Usuario eliminado correctamente.');
    }
    header('Location: usuarios.php');
    exit;
}

// Obtener usuario para editar
$userActual = null;
if (($action === 'editar' || !empty($errors)) && $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $userActual = $stmt->fetch();
}

$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios del Sistema | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Gestión de Usuarios</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">Inicio Admin</a>
            <a href="casas.php" class="nav-link">Casas</a>
            <a href="integrantes.php" class="nav-link">Integrantes</a>
            <a href="materiales.php" class="nav-link">Materiales</a>
            <a href="usuarios.php" class="nav-link active">Usuarios</a>
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
        <!-- Formulario Usuario -->
        <div class="form-card" style="max-width: 500px; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h2 style="font-family: var(--font-serif); font-size: 1.4rem;">
                    <?= $userActual ? 'Editar Usuario' : 'Nuevo Usuario Administrador' ?>
                </h2>
                <a href="usuarios.php" class="btn btn-outline btn-sm">Cancelar</a>
            </div>

            <form action="usuarios.php?action=<?= $action ?>" method="POST">
                <?php if ($userActual): ?>
                    <input type="hidden" name="id" value="<?= $userActual['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?= sanitize($userActual['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Nombre de Usuario (Login) *</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= sanitize($userActual['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="rol" class="form-label">Rol</label>
                    <select id="rol" name="rol" class="form-control">
                        <option value="admin" <?= ($userActual['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="facilitador" <?= ($userActual['rol'] ?? '') === 'facilitador' ? 'selected' : '' ?>>Facilitador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        Contraseña <?= $userActual ? '(Opcional: Dejar en blanco si no deseas cambiarla)' : '*' ?>
                    </label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" <?= $userActual ? '' : 'required' ?>>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?= $userActual ? 'Guardar Cambios' : 'Crear Usuario' ?>
                    </button>
                    <a href="usuarios.php" class="btn btn-outline" style="flex: 1;">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 class="page-title" style="font-size: 1.8rem;">Usuarios Administradores</h1>
            <p class="page-description">Cuentas con acceso de administración y publicación.</p>
        </div>
        <a href="usuarios.php?action=nuevo" class="btn btn-primary">+ Nuevo Usuario</a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><strong><?= sanitize($u['nombre']) ?></strong></td>
                        <td><code><?= sanitize($u['username']) ?></code></td>
                        <td><span class="member-role"><?= sanitize($u['rol']) ?></span></td>
                        <td><small style="color: var(--text-muted);"><?= date('d/m/Y', strtotime($u['created_at'])) ?></small></td>
                        <td>
                            <a href="usuarios.php?action=editar&id=<?= $u['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                <a href="usuarios.php?action=eliminar&id=<?= $u['id'] ?>" onclick="return confirmDelete('¿Estás seguro de eliminar este usuario?')" class="btn btn-danger btn-sm">Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Gestión de Usuarios</p>
</footer>

<script src="../assets/js/main.js"></script>
</body>
</html>
