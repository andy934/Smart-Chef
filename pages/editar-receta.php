<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLoginPage();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: mis-recetas.php');
    exit;
}

// Verificar propiedad
checkOwner($pdo, $id, usuarioActual());

// Cargar receta
$stmt = $pdo->prepare('SELECT * FROM recetas WHERE id = ?');
$stmt->execute([$id]);
$receta = $stmt->fetch();

// Cargar ingredientes
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

                <div class="form-actions">
                    <a href="detalle.php?id=<?= $receta['id'] ?>" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-brand-submit" id="btnGuardar">
                        Guardar cambios
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        let contadorIng = 0;

        function agregarIngrediente(nombre = '', cantidad = '') {
            const idx = contadorIng++;
            const div = document.createElement('div');
            div.className = 'ingredient-row';
            div.id = `ing-${idx}`;
            div.innerHTML = `
      <input type="text" name="ingredientes[${idx}][nombre]"   class="form-control"
             placeholder="Ingrediente" value="${nombre}">
      <input type="text" name="ingredientes[${idx}][cantidad]" class="form-control"
             placeholder="Cantidad"    value="${cantidad}">
      <button type="button" class="btn-remove-ing" onclick="document.getElementById('ing-${idx}').remove()">×</button>
    `;
            document.getElementById('ingredientesContainer').appendChild(div);
        }

        // Cargar ingredientes existentes desde PHP
        const ingredientesExistentes = <?= json_encode($ingredientes) ?>;
        if (ingredientesExistentes.length > 0) {
            ingredientesExistentes.forEach(ing => agregarIngrediente(ing.nombre, ing.cantidad));
        } else {
            agregarIngrediente();
            agregarIngrediente();
        }

        // ── Submit ──────────────────────────────────────────────────
        const form = document.getElementById('formEditar');
        const btnGuardar = document.getElementById('btnGuardar');
        const alertError = document.getElementById('alertError');
        const alertOk = document.getElementById('alertSuccess');

        function showError(msg) {
            if (Array.isArray(msg)) {
                alertError.innerHTML = `<ul>${msg.map(e => `<li>${e}</li>`).join('')}</ul>`;
            } else {
                alertError.textContent = msg;
            }
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
                const res = await fetch('../api/recetas/editar.php', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await res.json();

                if (res.ok) {
                    alertOk.textContent = '¡Receta actualizada! Redirigiendo...';
                    alertOk.style.display = 'block';
                    setTimeout(() => window.location.href = `detalle.php?id=<?= $receta['id'] ?>`, 1200);
                } else {
                    showError(data.errores || data.error || 'Error al guardar.');
                }
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