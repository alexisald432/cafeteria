<?php
session_start();
// Si no está logueado y no está en la página de login, redirigir
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    $isInPages = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
    $loginPath = $isInPages ? '../login.php' : 'login.php';
    header("Location: " . $loginPath);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Cafetería - Administración de productos, pedidos y clientes">
    <title>Cafetería Admin - Sistema de Gestión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php
    // Detectar si estamos en la raíz o en /pages/
    $basePath = '';
    if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
        $basePath = '../';
    }
    ?>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/styles.css">
</head>
<body>
