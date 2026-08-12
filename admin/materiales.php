<?php
require_once __DIR__ . '/../db.php';
requireAuth();

$pdo = getDB();
$action = $_GET['action'] ?? 'listar';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$errors = [];

// Asegurar que existe la carpeta uploads
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Procesar Subir / Guardar Material
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $semana = trim($_POST['semana'] ?? '');
    $post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (empty($titulo)) $errors[] = 'El título del material es obligatorio.';
    if (empty($semana)) $errors[] = 'El campo semana/fecha es obligatorio.';

    $archivo_path = '';

    // Manejo de archivo PDF subido
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['archivo']['tmp_name'];
        $fileName = $_FILES['archivo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'pdf') {
            $errors[] = 'Solo se permiten archivos con formato PDF.';
        } else {
            $newFileName = 'material_' . time() . '_' . uniqid() . '.pdf';
            $destPath = UPLOAD_DIR . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $archivo_path = 'uploads/' . $newFileName;
            } else {
                $errors[] = 'Error al guardar el archivo en el servidor.';
            }
        }
    } else if (!$post_id) {
        $errors[] = 'Debes seleccionar un archivo PDF para subir.';
    }

    if (empty($errors)) {
        if ($post_id) {
            if (!empty($archivo_path)) {
                // Eliminar archivo anterior si existe
                $stmtPrev = $pdo->prepare("SELECT archivo_path FROM materiales WHERE id = :id");
                $stmtPrev->execute(['id' => $post_id]);
                $prev = $stmtPrev->fetch();
                if ($prev && file_exists(__DIR__ . '/../' . $prev['archivo_path'])) {
                    @unlink(__DIR__ . '/../' . $prev['archivo_path']);
                }

                $stmt = $pdo->prepare("
                    UPDATE materiales SET 
                        titulo = :titulo,
                        descripcion = :descripcion,
                        semana = :semana,
                        archivo_path = :archivo_path
                    WHERE id = :id
                ");
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'semana' => $semana,
                    'archivo_path' => $archivo_path,
                    'id' => $post_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE materiales SET 
                        titulo = :titulo,
                        descripcion = :descripcion,
                        semana = :semana
                    WHERE id = :id
                ");
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'semana' => $semana,
                    'id' => $post_id
                ]);
            }
            setFlash('success', 'Material de apoyo actualizado correctamente.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO materiales (titulo, descripcion, semana, archivo_path)
                VALUES (:titulo, :descripcion, :semana, :archivo_path)
            ");
            $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'semana' => $semana,
                'archivo_path' => $archivo_path
            ]);
            setFlash('success', 'Material PDF subido con éxito.');
        }
        header('Location: materiales.php');
        exit;
    }
}

// Procesar Eliminar
if ($action === 'eliminar' && $id) {
    $stmtPrev = $pdo->prepare("SELECT archivo_path FROM materiales WHERE id = :id");
    $stmtPrev->execute(['id' => $id]);
    $mat = $stmtPrev->fetch();

    if ($mat && file_exists(__DIR__ . '/../' . $mat['archivo_path'])) {
        @unlink(__DIR__ . '/../' . $mat['archivo_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM materiales WHERE id = :id");
    $stmt->execute(['id' => $id]);
    setFlash('success', 'Material de apoyo eliminado correctamente.');
    header('Location: materiales.php');
    exit;
}

// Obtener Material para edición
$materialActual = null;
if (($action === 'editar' || !empty($errors)) && $id) {
    $stmt = $pdo->prepare("SELECT * FROM materiales WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $materialActual = $stmt->fetch();
}

// Listar materiales
$materiales = $pdo->query("SELECT * FROM materiales ORDER BY publicado_en DESC")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Materiales | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div>
            <a href="index.php" class="brand-title"><?= APP_NAME ?></a>
            <span class="brand-subtitle">Gestión de Materiales</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link">Inicio Admin</a>
            <a href="casas.php" class="nav-link">Casas</a>
            <a href="integrantes.php" class="nav-link">Integrantes</a>
            <a href="materiales.php" class="nav-link active">Materiales</a>
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
        <!-- Formulario Subir/Editar Material -->
        <div class="form-card" style="max-width: 600px; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                <h2 style="font-family: var(--font-serif); font-size: 1.4rem;">
                    <?= $materialActual ? 'Editar Material PDF' : 'Subir Nuevo Material PDF' ?>
                </h2>
                <a href="materiales.php" class="btn btn-outline btn-sm">Cancelar</a>
            </div>

            <form action="materiales.php?action=<?= $action ?>" method="POST" enctype="multipart/form-data">
                <?php if ($materialActual): ?>
                    <input type="hidden" name="id" value="<?= $materialActual['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="titulo" class="form-label">Título del Estudio o Guía *</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Estudio 3: El Amor Fraternal" value="<?= sanitize($materialActual['titulo'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="semana" class="form-label">Semana o Periodo *</label>
                    <input type="text" id="semana" name="semana" class="form-control" placeholder="Ej. Semana 35 - Agosto 2026" value="<?= sanitize($materialActual['semana'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion" class="form-label">Descripción breve</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Resumen o temas principales del estudio..."><?= sanitize($materialActual['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="archivo" class="form-label">
                        Archivo PDF <?= $materialActual ? '(Opcional: Seleccionar solo si deseas reemplazar el archivo)' : '*' ?>
                    </label>
                    <input type="file" id="archivo" name="archivo" class="form-control" accept="application/pdf" <?= $materialActual ? '' : 'required' ?>>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?= $materialActual ? 'Guardar Cambios' : 'Subir Archivo PDF' ?>
                    </button>
                    <a href="materiales.php" class="btn btn-outline" style="flex: 1;">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Listado de Materiales -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 class="page-title" style="font-size: 1.8rem;">Materiales de Apoyo</h1>
            <p class="page-description">Publica y administra guías en PDF para la enseñanza semanal.</p>
        </div>
        <a href="materiales.php?action=nuevo" class="btn btn-primary">+ Subir PDF</a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Semana</th>
                    <th>Título / Descripción</th>
                    <th>Publicado</th>
                    <th>Archivo PDF</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($materiales)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay materiales publicados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($materiales as $mat): ?>
                        <tr>
                            <td><strong><?= sanitize($mat['semana']) ?></strong></td>
                            <td>
                                <strong><?= sanitize($mat['titulo']) ?></strong>
                                <?php if (!empty($mat['descripcion'])): ?>
                                    <br><small style="color: var(--text-muted);"><?= sanitize($mat['descripcion']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small style="color: var(--text-muted);"><?= date('d/m/Y', strtotime($mat['publicado_en'])) ?></small></td>
                            <td>
                                <?php if (file_exists(__DIR__ . '/../' . $mat['archivo_path'])): ?>
                                    <a href="../<?= sanitize($mat['archivo_path']) ?>" target="_blank" class="btn btn-outline btn-sm">Abrir PDF</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Archivo no encontrado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="materiales.php?action=editar&id=<?= $mat['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                                <a href="materiales.php?action=eliminar&id=<?= $mat['id'] ?>" onclick="return confirmDelete('¿Estás seguro de eliminar este material?')" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="app-footer">
    <p><?= APP_NAME ?> &mdash; Gestión de Materiales</p>
</footer>

<script src="../assets/js/main.js"></script>
</body>
</html>
