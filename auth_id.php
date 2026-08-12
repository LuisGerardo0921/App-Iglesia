<?php
require_once __DIR__ . '/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_id = trim($_POST['codigo_id'] ?? '');

    if (empty($codigo_id)) {
        $response['message'] = 'Por favor ingresa tu ID de acceso.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT i.*, 
                   c.nombre AS casa_nombre, 
                   c.direccion AS casa_direccion, 
                   c.ciudad_sector AS casa_sector, 
                   c.dia_reunion AS casa_dia, 
                   c.horario AS casa_horario, 
                   c.anfitrion_nombre AS casa_anfitrion, 
                   c.facilitador_nombre AS casa_facilitador, 
                   c.telefono AS casa_telefono, 
                   c.mapa_url AS casa_mapa
            FROM integrantes i 
            LEFT JOIN casas c ON i.casa_id = c.id 
            WHERE LOWER(i.codigo_id) = LOWER(:codigo_id)
        ");
        $stmt->execute(['codigo_id' => $codigo_id]);
        $member = $stmt->fetch();

        if ($member) {
            $_SESSION['member'] = $member;
            $response['success'] = true;
            $response['message'] = "Bienvenido, " . $member['nombre_completo'];
        } else {
            $response['message'] = "El ID '{$codigo_id}' no fue encontrado. Solicita tu ID al Administrador.";
        }
    }

    // Si es petición AJAX/JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Si es formulario normal
    if ($response['success']) {
        setFlash('success', "¡Hola {$member['nombre_completo']}! Sesión iniciada con tu ID.");
    } else {
        setFlash('danger', $response['message']);
    }
    
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: {$redirect}");
    exit;
}

// Salir de sesión de integrante
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['member']);
    setFlash('success', 'Sesión de perfil cerrada correctamente.');
    header('Location: index.php');
    exit;
}
