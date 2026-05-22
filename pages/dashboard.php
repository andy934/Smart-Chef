<?php
require_once '../includes/auth.php';
requireLoginPage();
$nombre = htmlspecialchars($_SESSION['usuario_nombre']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="hero">
        <h2>¡Bienvenido, <?= $nombre ?>!</h2>
        <p>¿Qué quieres hacer hoy?</p>
        <a href="crear-receta.php" class="btn-brand">+ Nueva receta</a>
    </div>

    <div class="quick-cards">
        <a href="mis-recetas.php" class="qcard">
            <span class="qcard-icon">📖</span>
            <h3>Mis recetas</h3>
            <p>Administra las recetas que has creado</p>
        </a>
        <a href="explorar.php" class="qcard">
            <span class="qcard-icon">🔍</span>
            <h3>Explorar</h3>
            <p>Descubre recetas de otros usuarios</p>
        </a>
        <a href="mis-guardadas.php" class="qcard">
            <span class="qcard-icon">🔖</span>
            <h3>Guardadas</h3>
            <p>Recetas que guardaste para después</p>
        </a>
    </div>

</body>

</html>