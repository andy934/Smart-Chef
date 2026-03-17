<?php
// Cualquier usuario puede ver recetas (sin login)
require_once '../includes/db.php';

// Obtener todas las recetas con nombre del autor
$stmt = $pdo->query(
    'SELECT r.id, r.titulo, r.tiempo_min, r.imagen_ruta, u.nombre AS autor
     FROM recetas r
     JOIN usuarios u ON r.usuario_id = u.id
     ORDER BY r.created_at DESC'
);
$recetas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Explorar Recetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/recetas.css" rel="stylesheet">
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="page-header">
        <div>
            <h2>Explorar recetas</h2>
            <p><?= count($recetas) ?> receta<?= count($recetas) !== 1 ? 's' : '' ?> disponible<?= count($recetas) !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <div class="recipes-grid">
        <?php if (empty($recetas)): ?>
            <div class="empty-state">
                <span class="empty-state-icon">🍽️</span>
                <h3>Aún no hay recetas</h3>
                <p>¡Sé el primero en publicar una receta!</p>
            </div>
        <?php else: ?>
            <?php foreach ($recetas as $r): ?>
                <a href="detalle.php?id=<?= $r['id'] ?>" class="recipe-card">
                    <?php if (!empty($r['imagen_ruta'])): ?>
                        <img src="../<?= htmlspecialchars($r['imagen_ruta']) ?>"
                            alt="<?= htmlspecialchars($r['titulo']) ?>"
                            class="recipe-card-img">
                    <?php else: ?>
                        <div class="recipe-card-placeholder">🍳</div>
                    <?php endif; ?>
                    <div class="recipe-card-body">
                        <div class="recipe-card-title"><?= htmlspecialchars($r['titulo']) ?></div>
                        <div class="recipe-card-meta">
                            <span>👤 <?= htmlspecialchars($r['autor']) ?></span>
                            <span class="recipe-card-time">⏱ <?= $r['tiempo_min'] ?> min</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>

</html>