<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLoginPage();

$usuario_id = usuarioActual();

$stmt = $pdo->prepare(
    'SELECT id, titulo, tiempo_min, imagen_ruta, created_at
     FROM recetas
     WHERE usuario_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$usuario_id]);
$recetas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Mis Recetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/recetas.css" rel="stylesheet">
    <style>
        /* Fix: el overlay ocupa toda la pantalla y centra el modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 18, 8, .5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex !important;
        }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            max-width: 380px;
            width: 90%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(26, 18, 8, .15);
        }

        .modal-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            margin-bottom: .5rem;
            color: var(--ink);
        }

        .modal-box p {
            font-size: .9rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .modal-actions {
            display: flex;
            gap: .75rem;
        }

        .btn-cancel {
            flex: 1;
            padding: .75rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: #fff;
            color: var(--muted);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            cursor: pointer;
            transition: border-color .2s, color .2s;
        }

        .btn-cancel:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        .btn-confirm-delete {
            flex: 1;
            padding: .75rem;
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-confirm-delete:hover {
            background: #b91c1c;
        }
    </style>
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="page-header">
        <div>
            <h2>Mis recetas</h2>
            <p><?= count($recetas) ?> receta<?= count($recetas) !== 1 ? 's' : '' ?> publicada<?= count($recetas) !== 1 ? 's' : '' ?></p>
        </div>
        <a href="crear-receta.php" class="btn-brand">+ Nueva receta</a>
    </div>

    <div class="recipes-grid">
        <?php if (empty($recetas)): ?>
            <div class="empty-state">
                <span class="empty-state-icon">📝</span>
                <h3>Aún no tienes recetas</h3>
                <p>¡Comparte tu primera receta con la comunidad!</p>
                <br>
                <a href="crear-receta.php" class="btn-brand">+ Crear receta</a>
            </div>
        <?php else: ?>
            <?php foreach ($recetas as $r): ?>
                <div class="recipe-card" style="cursor:default">
                    <a href="detalle.php?id=<?= $r['id'] ?>" style="text-decoration:none; color:inherit;">
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
                                <span>⏱ <?= $r['tiempo_min'] ?> min</span>
                            </div>
                        </div>
                    </a>
                    <div class="recipe-card-actions">
                        <a href="editar-receta.php?id=<?= $r['id'] ?>" class="btn-edit">✏️ Editar</a>
                        <button class="btn-delete"
                            onclick="confirmarEliminar(<?= (int)$r['id'] ?>, '<?= htmlspecialchars(addslashes($r['titulo'])) ?>')">
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal — fuera del grid para que el z-index funcione bien -->
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-box">
            <h3>¿Eliminar receta?</h3>
            <p id="modalMensaje">Esta acción no se puede deshacer.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-confirm-delete" id="btnConfirmarEliminar">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <!-- Alertas flotantes -->
    <div id="alertOk" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;max-width:300px;z-index:1000;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:.85rem 1rem;font-size:.9rem;"></div>
    <div id="alertErr" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;max-width:300px;z-index:1000;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:.85rem 1rem;font-size:.9rem;"></div>

    <script>
        let recetaIdAEliminar = null;

        function confirmarEliminar(id, titulo) {
            recetaIdAEliminar = id;
            document.getElementById('modalMensaje').textContent =
                `¿Seguro que quieres eliminar "${titulo}"? Esta acción no se puede deshacer.`;
            document.getElementById('modalEliminar').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalEliminar').classList.remove('active');
            recetaIdAEliminar = null;
        }

        // Cerrar modal al hacer clic fuera del box
        document.getElementById('modalEliminar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        document.getElementById('btnConfirmarEliminar').addEventListener('click', async () => {
            if (!recetaIdAEliminar) return;

            const id = recetaIdAEliminar;
            cerrarModal();

            try {
                const fd = new FormData();
                fd.append('receta_id', id);
                const res = await fetch('../api/recetas/eliminar.php', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (res.ok) {
                    mostrarAlerta('alertOk', data.mensaje || 'Receta eliminada.');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    mostrarAlerta('alertErr', data.error || 'Error al eliminar.');
                }
            } catch {
                mostrarAlerta('alertErr', 'No se pudo conectar con el servidor.');
            }
        });

        function mostrarAlerta(id, msg) {
            const el = document.getElementById(id);
            el.textContent = msg;
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 3000);
        }
    </script>

</body>

</html>