<?php
require_once __DIR__ . '/db.php';

$error = '';

if (isLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            setFlash('success', 'Sesión iniciada correctamente.');
            header('Location: admin/index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor ingresa usuario y contraseña.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Acceso Administrador</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">&larr; Ir al Directorio</a>
        </nav>
    </div>
</header>

<main class="main-content">
    <div class="form-card" style="max-width: 420px; margin: 2rem auto;">
        <h1 class="page-title" style="font-size: 1.6rem; text-align: center; margin-bottom: 0.5rem;">Iniciar Sesión</h1>
        <p class="page-description" style="text-align: center; margin-bottom: 1.5rem; font-size: 0.88rem;">
            Ingresa con tus credenciales de Administrador o Facilitador.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Nombre de Usuario</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ej. admin" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Ingresar al Panel
            </button>
        </form>

        <div style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted); text-align: center; border-top: 1px solid var(--border-color); padding-top: 1rem;">
            Credenciales iniciales de prueba:<br>
            <strong>Usuario:</strong> <code>admin</code> | <strong>Contraseña:</strong> <code>admin123</code>
        </div>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Panel de Control</p>
</footer>

</body>
</html>
