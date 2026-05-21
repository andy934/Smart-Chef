<?php
// ✅ session_start() siempre al inicio, antes de cualquier output o lógica
session_start();
require_once '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: explorar.php');
    exit;
}

// Obtener receta
$stmt = $pdo->prepare(
    'SELECT r.*, u.nombre AS autor
     FROM recetas r JOIN usuarios u ON r.usuario_id = u.id
     WHERE r.id = ?'
);
$stmt->execute([$id]);
$receta = $stmt->fetch();
if (!$receta) {
    header('Location: explorar.php');
    exit;
}

// Obtener ingredientes
$stmtIng = $pdo->prepare('SELECT nombre, cantidad FROM ingredientes WHERE receta_id = ? ORDER BY id');
$stmtIng->execute([$id]);
$ingredientes = $stmtIng->fetchAll();

// Ver si el usuario está logueado y es el autor
$esAutor = !empty($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === (int)$receta['usuario_id'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — <?= htmlspecialchars($receta['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/recetas.css" rel="stylesheet">
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="detalle-page">

        <!-- Imagen o placeholder -->
        <?php if (!empty($receta['imagen_ruta'])): ?>
            <img src="../<?= htmlspecialchars($receta['imagen_ruta']) ?>"
                alt="<?= htmlspecialchars($receta['titulo']) ?>"
                class="detalle-img">
        <?php else: ?>
            <div class="detalle-img-placeholder">🍳</div>
        <?php endif; ?>

        <!-- Título y meta -->
        <h1 class="detalle-titulo"><?= htmlspecialchars($receta['titulo']) ?></h1>

        <div class="detalle-meta">
            <span>👤 <strong><?= htmlspecialchars($receta['autor']) ?></strong></span>
            <span>⏱ <strong><?= $receta['tiempo_min'] ?> min</strong></span>
            <span>📅 <?= date('d/m/Y', strtotime($receta['created_at'])) ?></span>
        </div>

        <!-- Acciones del autor -->
        <?php if ($esAutor): ?>
            <div style="display:flex; gap:.75rem; margin-bottom:1.5rem;">
                <a href="editar-receta.php?id=<?= $receta['id'] ?>" class="btn-edit" style="padding:.5rem 1.25rem;">✏️ Editar</a>
                <button class="btn-delete" style="padding:.5rem 1.25rem;"
                    onclick="confirmarEliminar(<?= $receta['id'] ?>)">🗑️ Eliminar</button>
            </div>
        <?php endif; ?>

        <!-- Ingredientes -->
        <h2 class="detalle-section-title">Ingredientes</h2>
        <?php if (empty($ingredientes)): ?>
            <p style="color:var(--muted); font-size:.9rem;">Sin ingredientes registrados.</p>
        <?php else: ?>
            <ul class="ingredientes-list">
                <?php foreach ($ingredientes as $ing): ?>
                    <li>
                        <span><?= htmlspecialchars($ing['nombre']) ?></span>
                        <?php if (!empty($ing['cantidad'])): ?>
                            <span class="cantidad"><?= htmlspecialchars($ing['cantidad']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Pasos -->
        <h2 class="detalle-section-title">Preparación</h2>
        <p class="pasos-text"><?= htmlspecialchars($receta['pasos']) ?></p>

        <div style="margin-top:2rem;">
            <a href="explorar.php" style="color:var(--brand); text-decoration:none; font-size:.9rem;">
                ← Volver a explorar
            </a>
        </div>

    </div>

    <!-- Modal eliminar (solo si es autor) -->
    <?php if ($esAutor): ?>
        <div class="modal-overlay" id="modalEliminar">
            <div class="modal-box">
                <h3>¿Eliminar receta?</h3>
                <p>Esta acción no se puede deshacer.</p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="document.getElementById('modalEliminar').classList.remove('active')">Cancelar</button>
                    <button class="btn-confirm-delete" id="btnConfirmar">Eliminar</button>
                </div>
            </div>
        </div>

        <script>
            let recetaId = null;

            function confirmarEliminar(id) {
                recetaId = id;
                document.getElementById('modalEliminar').classList.add('active');
            }

            document.getElementById('btnConfirmar').addEventListener('click', async () => {
                const fd = new FormData();
                fd.append('receta_id', recetaId);
                const res = await fetch('../api/recetas/eliminar.php', {
                    method: 'POST',
                    body: fd
                });
                if (res.ok) {
                    window.location.href = 'mis-recetas.php';
                }
            });
        </script>
    <?php endif; ?>

</body>

</html>