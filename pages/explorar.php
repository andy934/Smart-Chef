<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$stmt = $pdo->query(
    'SELECT r.id, r.titulo, r.tiempo_min, r.imagen_ruta, u.nombre AS autor
     FROM recetas r
     JOIN usuarios u ON r.usuario_id = u.id
     ORDER BY r.created_at DESC'
);
$recetas = $stmt->fetchAll();

// Cargar etiquetas de todas las recetas de una sola consulta
$etiquetasMap = [];
if (!empty($recetas)) {
    $ids     = implode(',', array_column($recetas, 'id'));
    $tagStmt = $pdo->query(
        "SELECT re.receta_id, e.nombre, e.tipo
         FROM receta_etiquetas re
         JOIN etiquetas e ON e.id = re.etiqueta_id
         WHERE re.receta_id IN ($ids)
         ORDER BY e.tipo, e.nombre"
    );
    foreach ($tagStmt->fetchAll() as $t) {
        $etiquetasMap[$t['receta_id']][] = $t;
    }
}
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
    <style>
        .card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
            padding: 0 1.1rem .75rem;
        }

        .card-tag {
            font-size: .7rem;
            padding: .2rem .6rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .card-tag-dieta {
            background: #fff7ed;
            color: var(--brand);
            border: 1px solid #fdd5c4;
        }

        .card-tag-alergeno {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
    </style>
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

                    <?php if (!empty($etiquetasMap[$r['id']])): ?>
                        <div class="card-tags">
                            <?php foreach ($etiquetasMap[$r['id']] as $tag): ?>
                                <span class="card-tag card-tag-<?= $tag['tipo'] ?>">
                                    <?= $tag['tipo'] === 'dieta' ? '🥗' : '⚠️' ?>
                                    <?= htmlspecialchars($tag['nombre']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>

</html>