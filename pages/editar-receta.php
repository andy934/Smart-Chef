<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLoginPage();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: mis-recetas.php');
    exit;
}

checkOwner($pdo, $id, usuarioActual());

$stmt = $pdo->prepare('SELECT * FROM recetas WHERE id = ?');
$stmt->execute([$id]);
$receta = $stmt->fetch();

$stmtIng = $pdo->prepare('SELECT nombre, cantidad FROM ingredientes WHERE receta_id = ? ORDER BY id');
$stmtIng->execute([$id]);
$ingredientes = $stmtIng->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Editar Receta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/recetas.css" rel="stylesheet">
    <style>
        .image-upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .image-upload-area:hover {
            border-color: var(--brand);
            background: #fff9f7;
        }

        .upload-icon {
            font-size: 2rem;
        }

        .upload-text {
            font-size: .9rem;
            font-weight: 500;
            color: var(--ink);
        }

        .upload-hint {
            font-size: .78rem;
            color: var(--muted);
        }

        .img-actual {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border);
            margin-bottom: .75rem;
            display: block;
        }
    </style>
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="form-page">
        <div class="form-card">
            <h2>Editar receta</h2>

            <div class="alert-box alert-error" id="alertError"></div>
            <div class="alert-box alert-success" id="alertSuccess"></div>

            <form id="formEditar" novalidate>
                <input type="hidden" name="receta_id" value="<?= $receta['id'] ?>">

                <p class="form-section-title">Información general</p>

                <div class="mb-field">
                    <label class="form-label" for="titulo">Nombre de la receta</label>
                    <input type="text" id="titulo" name="titulo" class="form-control"
                        value="<?= htmlspecialchars($receta['titulo']) ?>" required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="tiempo_min">Tiempo de preparación (minutos)</label>
                    <input type="number" id="tiempo_min" name="tiempo_min" class="form-control"
                        value="<?= $receta['tiempo_min'] ?>" min="1" required>
                </div>

                <p class="form-section-title">Ingredientes</p>
                <div id="ingredientesContainer"></div>
                <button type="button" class="btn-add-ing" onclick="agregarIngrediente()">
                    + Agregar ingrediente
                </button>

                <p class="form-section-title">Pasos de preparación</p>
                <div class="mb-field">
                    <label class="form-label" for="pasos">Describe los pasos</label>
                    <textarea id="pasos" name="pasos" class="form-control" required><?= htmlspecialchars($receta['pasos']) ?></textarea>
                </div>

                <!-- ── SECCIÓN IMAGEN ── -->
                <p class="form-section-title">Imagen de la receta</p>
                <div class="mb-field">

                    <?php if (!empty($receta['imagen_ruta'])): ?>
                        <!-- Imagen actual -->
                        <div id="imagenActual">
                            <img src="../<?= htmlspecialchars($receta['imagen_ruta']) ?>"
                                alt="Imagen actual" class="img-actual">
                            <button type="button" class="btn-delete" style="padding:.45rem 1rem; font-size:.82rem;"
                                onclick="eliminarImagenActual()">
                                🗑️ Eliminar imagen actual
                            </button>
                        </div>
                        <div id="uploadNueva" style="display:none; margin-top:.75rem;">
                        <?php else: ?>
                            <div id="uploadNueva">
                            <?php endif; ?>
                            <div class="image-upload-area" id="uploadArea"
                                onclick="document.getElementById('imagenInput').click()">
                                <span class="upload-icon">📷</span>
                                <span class="upload-text">Haz clic para seleccionar una imagen</span>
                                <span class="upload-hint">JPG, PNG o WEBP · Máximo 2 MB</span>
                            </div>
                            <input type="file" id="imagenInput" accept="image/jpeg,image/png,image/webp"
                                style="display:none" onchange="previsualizarImagen(this)">
                            <div id="previewContainer" style="display:none; margin-top:.75rem; position:relative;">
                                <img id="previewImg" src="" alt="Vista previa" class="img-actual" style="margin-bottom:0">
                                <button type="button" onclick="quitarPreview()"
                                    style="position:absolute;top:.5rem;right:.5rem;background:#fff;
                             border:1px solid var(--border);border-radius:6px;
                             padding:.2rem .5rem;cursor:pointer;font-size:.8rem;color:#dc2626;">
                                    × Quitar
                                </button>
                            </div>
                            </div>

                        </div>
                        <!-- ── FIN SECCIÓN IMAGEN ── -->

                        <div class="form-actions">
                            <a href="detalle.php?id=<?= $receta['id'] ?>" class="btn-secondary">Cancelar</a>
                            <button type="submit" class="btn-brand-submit" id="btnGuardar">
                                Guardar cambios
                            </button>
                        </div>

            </form>
        </div>
    </div>

    <!-- Alerta flotante para eliminar imagen -->
    <div class="alert-box alert-success" id="alertFlotante"
        style="position:fixed;bottom:1.5rem;right:1.5rem;max-width:300px;z-index:200"></div>

    <script>
        const RECETA_ID = <?= $receta['id'] ?>;
        let contadorIng = 0;

        // ── Ingredientes ─────────────────────────────────────────
        function agregarIngrediente(nombre = '', cantidad = '') {
            const idx = contadorIng++;
            const div = document.createElement('div');
            div.className = 'ingredient-row';
            div.id = `ing-${idx}`;
            div.innerHTML = `
      <input type="text" name="ingredientes[${idx}][nombre]"   class="form-control" placeholder="Ingrediente" value="${nombre}">
      <input type="text" name="ingredientes[${idx}][cantidad]" class="form-control" placeholder="Cantidad"    value="${cantidad}">
      <button type="button" class="btn-remove-ing" onclick="document.getElementById('ing-${idx}').remove()">×</button>
    `;
            document.getElementById('ingredientesContainer').appendChild(div);
        }

        const ingredientesExistentes = <?= json_encode($ingredientes) ?>;
        if (ingredientesExistentes.length > 0) {
            ingredientesExistentes.forEach(ing => agregarIngrediente(ing.nombre, ing.cantidad));
        } else {
            agregarIngrediente();
            agregarIngrediente();
        }

        // ── Imagen: previsualizar nueva ───────────────────────────
        function previsualizarImagen(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar 2 MB.');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('previewContainer').style.display = 'block';
                document.getElementById('uploadArea').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        function quitarPreview() {
            document.getElementById('imagenInput').value = '';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'flex';
        }

        // ── Imagen: eliminar la actual del servidor ───────────────
        async function eliminarImagenActual() {
            const fd = new FormData();
            fd.append('receta_id', RECETA_ID);
            const res = await fetch('../api/recetas/eliminar-imagen.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();

            if (res.ok) {
                document.getElementById('imagenActual').style.display = 'none';
                document.getElementById('uploadNueva').style.display = 'block';
                const al = document.getElementById('alertFlotante');
                al.textContent = data.mensaje;
                al.style.display = 'block';
                setTimeout(() => al.style.display = 'none', 3000);
            } else {
                alert(data.error || 'Error al eliminar la imagen.');
            }
        }

        // ── Submit ────────────────────────────────────────────────
        const form = document.getElementById('formEditar');
        const btnGuardar = document.getElementById('btnGuardar');
        const alertError = document.getElementById('alertError');
        const alertOk = document.getElementById('alertSuccess');

        function showError(msg) {
            alertError.innerHTML = Array.isArray(msg) ?
                `<ul>${msg.map(e => `<li>${e}</li>`).join('')}</ul>` : msg;
            alertError.style.display = 'block';
            alertOk.style.display = 'none';
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertError.style.display = 'none';

            const titulo = document.getElementById('titulo').value.trim();
            const tiempo_min = document.getElementById('tiempo_min').value;
            const pasos = document.getElementById('pasos').value.trim();
            const inputs = document.querySelectorAll('[name^="ingredientes"][name$="[nombre]"]');
            const tieneIng = [...inputs].some(i => i.value.trim() !== '');

            if (!titulo) {
                showError('El nombre es obligatorio.');
                return;
            }
            if (!tiempo_min) {
                showError('El tiempo es obligatorio.');
                return;
            }
            if (!pasos) {
                showError('Los pasos son obligatorios.');
                return;
            }
            if (!tieneIng) {
                showError('Agrega al menos un ingrediente.');
                return;
            }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner"></span>Guardando...';

            try {
                // Paso 1: guardar cambios de texto
                const res = await fetch('../api/recetas/editar.php', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await res.json();
                if (!res.ok) {
                    showError(data.errores || data.error || 'Error al guardar.');
                    return;
                }

                // Paso 2: subir nueva imagen si eligió una
                const imagenFile = document.getElementById('imagenInput').files[0];
                if (imagenFile) {
                    const fdImg = new FormData();
                    fdImg.append('receta_id', RECETA_ID);
                    fdImg.append('imagen', imagenFile);
                    await fetch('../api/recetas/subir-imagen.php', {
                        method: 'POST',
                        body: fdImg
                    });
                }

                alertOk.textContent = '¡Cambios guardados! Redirigiendo...';
                alertOk.style.display = 'block';
                setTimeout(() => window.location.href = `detalle.php?id=${RECETA_ID}`, 1200);

            } catch {
                showError('No se pudo conectar con el servidor.');
            } finally {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = 'Guardar cambios';
            }
        });
    </script>

</body>

</html>