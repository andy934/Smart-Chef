<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SmartChef</title>
</head>

<body>
    <?php
    // Redirige al login si no hay sesión, al dashboard si hay sesión
    session_start();
    if (!empty($_SESSION['usuario_id'])) {
        header('Location: pages/dashboard.php');
    } else {
        header('Location: pages/login.php');
    }
    exit;
    ?>
</body>

</html>