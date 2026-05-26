<?php
session_start();
require_once '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: explorar.php');
    exit;
}

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

$stmtIng = $pdo->prepare('SELECT nombre, cantidad FROM ingredientes WHERE receta_id = ? ORDER BY id');
$stmtIng->execute([$id]);
$ingredientes = $stmtIng->fetchAll();

$stmtTag = $pdo->prepare(
    'SELECT e.nombre, e.tipo FROM receta_etiquetas re
     JOIN etiquetas e ON e.id = re.etiqueta_id
     WHERE re.receta_id = ? ORDER BY e.tipo, e.nombre'
);
$stmtTag->execute([$id]);
$etiquetas = $stmtTag->fetchAll();

$usuarioId = !empty($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
$esAutor   = $usuarioId > 0 && $usuarioId === (int)$receta['usuario_id'];

// Ver si el usuario ya guardó esta receta
$yaGuardada = false;
if ($usuarioId > 0 && !$esAutor) {
    $stmtG = $pdo->prepare('SELECT id FROM recetas_guardadas WHERE usuario_id = ? AND receta_id = ?');
    $stmtG->execute([$usuarioId, $id]);
    $yaGuardada = (bool)$stmtG->fetch();
}
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
    <style>
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

        /* Etiquetas */
        .detalle-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: 1.5rem;
        }

        .detalle-tag {
            font-size: .78rem;
            padding: .25rem .75rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .detalle-tag-dieta {
            background: #fff7ed;
            color: var(--brand);
            border: 1px solid #fdd5c4;
        }

        .detalle-tag-alergeno {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Botón guardar */
        .btn-guardar {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 500;
            cursor: pointer;
            border: 1.5px solid var(--border);
            color: var(--muted);
            background: #fff;
            transition: all .2s;
            font-family: 'DM Sans', sans-serif;
        }

        .btn-guardar:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        .btn-guardar.guardada {
            border-color: var(--brand);
            color: var(--brand);
            background: #fff7ed;
        }
    </style>
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="detalle-page">

        <?php if (!empty($receta['imagen_ruta'])): ?>
            <img src="../<?= htmlspecialchars($receta['imagen_ruta']) ?>"
                alt="<?= htmlspecialchars($receta['titulo']) ?>" class="detalle-img">
        <?php else: ?>
            <div class="detalle-img-placeholder">🍳</div>
        <?php endif; ?>

        <h1 class="detalle-titulo"><?= htmlspecialchars($receta['titulo']) ?></h1>

        <div class="detalle-meta">
            <span>👤 <strong><?= htmlspecialchars($receta['autor']) ?></strong></span>
            <span>⏱ <strong><?= $receta['tiempo_min'] ?> min</strong></span>
            <span>📅 <?= date('d/m/Y', strtotime($receta['created_at'])) ?></span>
        </div>

        <!-- Etiquetas -->
        <?php if (!empty($etiquetas)): ?>
            <div class="detalle-tags">
                <?php foreach ($etiquetas as $tag): ?>
                    <span class="detalle-tag detalle-tag-<?= $tag['tipo'] ?>">
                        <?= $tag['tipo'] === 'dieta' ? '🥗' : '⚠️' ?>
                        <?= htmlspecialchars($tag['nombre']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div style="display:flex; gap:.75rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <?php if ($esAutor): ?>
                <a href="editar-receta.php?id=<?= $receta['id'] ?>" class="btn-edit" style="padding:.5rem 1.25rem;">✏️ Editar</a>
                <button class="btn-accion btn-pdf" style="padding:.5rem 1.25rem;"
                    onclick="window.location.href='../api/recetas/exportar-pdf.php?id=<?= $receta['id'] ?>'">
                    📄 Exportar PDF
                </button>
                <button class="btn-delete" style="padding:.5rem 1.25rem;"
                    onclick="confirmarEliminar(<?= $receta['id'] ?>, '<?= htmlspecialchars(addslashes($receta['titulo'])) ?>')">
                    🗑️ Eliminar
                </button>

            <?php elseif ($usuarioId > 0): ?>
                <!-- Botón guardar (solo para usuarios logueados que no son el autor) -->
                <button class="btn-guardar <?= $yaGuardada ? 'guardada' : '' ?>" id="btnGuardar"
                    onclick="toggleGuardar(<?= $receta['id'] ?>)">
                    <?= $yaGuardada ? '🔖 Guardada' : '🔖 Guardar receta' ?>
                </button>

            <?php else: ?>
                <!-- No logueado: invitar a iniciar sesión -->
                <a href="login.php" class="btn-guardar">🔖 Inicia sesión para guardar</a>
            <?php endif; ?>

        </div>

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

        <h2 class="detalle-section-title">Preparación</h2>
        <p class="pasos-text"><?= htmlspecialchars($receta['pasos']) ?></p>

        <div style="margin-top:2rem;">
            <a href="explorar.php" style="color:var(--brand); text-decoration:none; font-size:.9rem;">
                ← Volver a explorar
            </a>
        </div>

    </div>

    <!-- Alerta flotante -->
    <div id="alertFlotante" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;max-width:300px;z-index:1000;border-radius:10px;padding:.85rem 1rem;font-size:.9rem;"></div>

    <?php if ($esAutor): ?>
        <!-- Modal eliminar -->
        <div class="modal-overlay" id="modalEliminar">
            <div class="modal-box">
                <h3>¿Eliminar receta?</h3>
                <p id="modalMensaje">Esta acción no se puede deshacer.</p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button class="btn-confirm-delete" id="btnConfirmar">Sí, eliminar</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // ── Guardar / desguardar ──────────────────────────────────
        async function toggleGuardar(recetaId) {
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            try {
                const fd = new FormData();
                fd.append('receta_id', recetaId);
                const res = await fetch('../api/recetas/guardar.php', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (res.ok) {
                    const guardada = data.guardada;
                    btn.textContent = guardada ? '🔖 Guardada' : '🔖 Guardar receta';
                    btn.classList.toggle('guardada', guardada);
                    mostrarAlerta(data.mensaje, guardada ? 'ok' : 'ok');
                } else {
                    mostrarAlerta(data.error || 'Error al guardar.', 'err');
                }
            } catch {
                mostrarAlerta('No se pudo conectar con el servidor.', 'err');
            } finally {
                btn.disabled = false;
            }
        }

        function mostrarAlerta(msg, tipo) {
            const el = document.getElementById('alertFlotante');
            el.textContent = msg;
            el.style.background = tipo === 'ok' ? '#f0fdf4' : '#fef2f2';
            el.style.border = tipo === 'ok' ? '1px solid #bbf7d0' : '1px solid #fecaca';
            el.style.color = tipo === 'ok' ? '#166534' : '#991b1b';
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 3000);
        }

        <?php if ($esAutor): ?>
            // ── Eliminar receta ───────────────────────────────────────
            let recetaId = null;

            function confirmarEliminar(id, titulo) {
                recetaId = id;
                document.getElementById('modalMensaje').textContent =
                    `¿Seguro que quieres eliminar "${titulo}"? Esta acción no se puede deshacer.`;
                document.getElementById('modalEliminar').classList.add('active');
            }

            function cerrarModal() {
                document.getElementById('modalEliminar').classList.remove('active');
                recetaId = null;
            }

            document.getElementById('modalEliminar').addEventListener('click', function(e) {
                if (e.target === this) cerrarModal();
            });

            document.getElementById('btnConfirmar').addEventListener('click', async () => {
                if (!recetaId) return;
                const id = recetaId;
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
                        window.location.href = 'mis-recetas.php';
                    } else {
                        mostrarAlerta(data.error || 'Error al eliminar.', 'err');
                    }
                } catch {
                    mostrarAlerta('No se pudo conectar con el servidor.', 'err');
                }
            });
        <?php endif; ?>
    </script>

</body>

</html>