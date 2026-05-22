<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLoginPage();

$usuario_id = usuarioActual();

$stmt = $pdo->prepare(
    'SELECT r.id, r.titulo, r.tiempo_min, r.imagen_ruta, u.nombre AS autor, rg.guardado_at
     FROM recetas_guardadas rg
     JOIN recetas r  ON r.id  = rg.receta_id
     JOIN usuarios u ON u.id  = r.usuario_id
     WHERE rg.usuario_id = ?
     ORDER BY rg.guardado_at DESC'
);
$stmt->execute([$usuario_id]);
$recetas = $stmt->fetchAll();

// Etiquetas de las recetas guardadas
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
    <title>SmartChef — Mis Guardadas</title>
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

        .btn-desguardar {
            flex: 1;
            padding: .45rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 500;
            cursor: pointer;
            border: 1.5px solid #fecaca;
            color: #dc2626;
            background: #fff;
            transition: background .2s;
            text-align: center;
        }

        .btn-desguardar:hover {
            background: #fef2f2;
        }

        /* Modal */
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
            <h2>Mis guardadas</h2>
            <p><?= count($recetas) ?> receta<?= count($recetas) !== 1 ? 's' : '' ?> guardada<?= count($recetas) !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <div class="recipes-grid">
        <?php if (empty($recetas)): ?>
            <div class="empty-state">
                <span class="empty-state-icon">🔖</span>
                <h3>No tienes recetas guardadas</h3>
                <p>Explora recetas y guarda las que más te gusten.</p>
                <br>
                <a href="explorar.php" class="btn-brand">Explorar recetas</a>
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
                                <span>👤 <?= htmlspecialchars($r['autor']) ?></span>
                                <span class="recipe-card-time">⏱ <?= $r['tiempo_min'] ?> min</span>
                            </div>
                        </div>
                    </a>

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

                    <div class="recipe-card-actions">
                        <button class="btn-desguardar"
                            onclick="confirmarDesguardar(<?= (int)$r['id'] ?>, '<?= htmlspecialchars(addslashes($r['titulo'])) ?>')">
                            🔖 Quitar de guardadas
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal confirmación -->
    <div class="modal-overlay" id="modalDesguardar">
        <div class="modal-box">
            <h3>¿Quitar receta?</h3>
            <p id="modalMensaje">Esta receta se eliminará de tu colección.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-confirm-delete" id="btnConfirmar">Sí, quitar</button>
            </div>
        </div>
    </div>

    <!-- Alertas flotantes -->
    <div id="alertOk" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;max-width:300px;z-index:1000;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:.85rem 1rem;font-size:.9rem;"></div>
    <div id="alertErr" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;max-width:300px;z-index:1000;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:.85rem 1rem;font-size:.9rem;"></div>

    <script>
        let recetaIdAQuitar = null;

        function confirmarDesguardar(id, titulo) {
            recetaIdAQuitar = id;
            document.getElementById('modalMensaje').textContent =
                `¿Quieres quitar "${titulo}" de tu colección?`;
            document.getElementById('modalDesguardar').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalDesguardar').classList.remove('active');
            recetaIdAQuitar = null;
        }

        document.getElementById('modalDesguardar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        document.getElementById('btnConfirmar').addEventListener('click', async () => {
            if (!recetaIdAQuitar) return;
            const id = recetaIdAQuitar;
            cerrarModal();

            try {
                const fd = new FormData();
                fd.append('receta_id', id);
                const res = await fetch('../api/recetas/guardar.php', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (res.ok) {
                    mostrarAlerta('alertOk', data.mensaje || 'Receta quitada.');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    mostrarAlerta('alertErr', data.error || 'Error al quitar.');
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