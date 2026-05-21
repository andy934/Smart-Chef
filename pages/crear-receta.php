<?php
require_once '../includes/auth.php';
requireLoginPage();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Crear Receta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/recetas.css" rel="stylesheet">
</head>

<body>

    <?php require_once '../includes/navbar.php'; ?>

    <div class="form-page">
        <div class="form-card">
            <h2>Nueva receta</h2>

            <div class="alert-box alert-error" id="alertError"></div>
            <div class="alert-box alert-success" id="alertSuccess"></div>

            <!-- Paso 1: datos de la receta -->
            <form id="formReceta" novalidate>

                <p class="form-section-title">Información general</p>

                <div class="mb-field">
                    <label class="form-label" for="titulo">Nombre de la receta</label>
                    <input type="text" id="titulo" name="titulo" class="form-control"
                        placeholder="Ej. Pozole rojo estilo Sinaloa" required>
                </div>

                <div class="mb-field">
                    <label class="form-label" for="tiempo_min">Tiempo de preparación (minutos)</label>
                    <input type="number" id="tiempo_min" name="tiempo_min" class="form-control"
                        placeholder="Ej. 45" min="1" required>
                </div>

                <p class="form-section-title">Ingredientes</p>
                <div id="ingredientesContainer"></div>
                <button type="button" class="btn-add-ing" onclick="agregarIngrediente()">
                    + Agregar ingrediente
                </button>

                <p class="form-section-title">Pasos de preparación</p>
                <div class="mb-field">
                    <label class="form-label" for="pasos">Describe los pasos</label>
                    <textarea id="pasos" name="pasos" class="form-control"
                        placeholder="Paso 1: &#10;Paso 2: " required></textarea>
                </div>

                <p class="form-section-title">Imagen de la receta (opcional)</p>
                <div class="mb-field">
                    <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('imagenInput').click()">
                        <span class="upload-icon">📷</span>
                        <span class="upload-text">Haz clic para seleccionar una imagen</span>
                        <span class="upload-hint">JPG, PNG o WEBP · Máximo 2 MB</span>
                    </div>
                    <input type="file" id="imagenInput" accept="image/jpeg,image/png,image/webp"
                        style="display:none" onchange="previsualizarImagen(this)">
                    <div id="previewContainer" style="display:none; margin-top:.75rem; position:relative;">
                        <img id="previewImg" src="" alt="Vista previa"
                            style="width:100%; max-height:220px; object-fit:cover; border-radius:10px; border:1px solid var(--border);">
                        <button type="button" onclick="quitarImagen()"
                            style="position:absolute;top:.5rem;right:.5rem;background:#fff;border:1px solid var(--border);
                         border-radius:6px;padding:.2rem .5rem;cursor:pointer;font-size:.8rem;color:#dc2626;">
                            × Quitar
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="mis-recetas.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-brand-submit" id="btnGuardar">
                        Publicar receta
                    </button>
                </div>

            </form>
        </div>
    </div>

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
    </style>

    <script>
        let contadorIng = 0;

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

        agregarIngrediente();
        agregarIngrediente();

        function previsualizarImagen(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                mostrarError('La imagen no debe superar 2 MB.');
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

        function quitarImagen() {
            document.getElementById('imagenInput').value = '';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'flex';
        }

        const form = document.getElementById('formReceta');
        const btnGuardar = document.getElementById('btnGuardar');
        const alertError = document.getElementById('alertError');
        const alertOk = document.getElementById('alertSuccess');

        function mostrarError(msg) {
            alertError.innerHTML = Array.isArray(msg) ?
                `<ul>${msg.map(e=>`<li>${e}</li>`).join('')}</ul>` : msg;
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
                mostrarError('El nombre de la receta es obligatorio.');
                return;
            }
            if (!tiempo_min) {
                mostrarError('El tiempo de preparación es obligatorio.');
                return;
            }
            if (!pasos) {
                mostrarError('Los pasos de preparación son obligatorios.');
                return;
            }
            if (!tieneIng) {
                mostrarError('Agrega al menos un ingrediente.');
                return;
            }

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner"></span>Publicando...';

            try {
                // Paso 1: guardar receta
                const res = await fetch('../api/recetas/crear.php', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await res.json();

                if (!res.ok) {
                    mostrarError(data.errores || data.error || 'Error al guardar.');
                    return;
                }

                const recetaId = data.receta_id;

                // Paso 2: subir imagen si el usuario eligió una
                const imagenFile = document.getElementById('imagenInput').files[0];
                if (imagenFile) {
                    const fdImg = new FormData();
                    fdImg.append('receta_id', recetaId);
                    fdImg.append('imagen', imagenFile);
                    await fetch('../api/recetas/subir-imagen.php', {
                        method: 'POST',
                        body: fdImg
                    });
                }

                alertOk.textContent = '¡Receta publicada! Redirigiendo...';
                alertOk.style.display = 'block';
                setTimeout(() => window.location.href = `detalle.php?id=${recetaId}`, 1200);

            } catch {
                mostrarError('No se pudo conectar con el servidor.');
            } finally {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = 'Publicar receta';
            }
        });
    </script>

</body>

</html>