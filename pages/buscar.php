<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Cargar etiquetas para los filtros
$etiquetas = $pdo->query('SELECT id, nombre, tipo FROM etiquetas ORDER BY tipo, nombre')->fetchAll();
$dietas    = array_filter($etiquetas, fn($e) => $e['tipo'] === 'dieta');
$alergenos = array_filter($etiquetas, fn($e) => $e['tipo'] === 'alergeno');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Buscar Recetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <link href="../assets/css/recetas.css" rel="stylesheet">
    <style>
        .search-hero {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .search-hero h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--ink);
            margin-bottom: 1.25rem;
        }

        .search-wrap {
            position: relative;
            max-width: 560px;
            margin: 0 auto;
        }

        .search-input {
            width: 100%;
            border: 2px solid var(--border);
            border-radius: 50px;
            padding: .85rem 3.5rem .85rem 1.5rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            color: var(--ink);
            background: var(--cream);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(232, 93, 47, .1);
            background: #fff;
        }

        .search-btn {
            position: absolute;
            right: .4rem;
            top: 50%;
            transform: translateY(-50%);
            background: var(--brand);
            border: none;
            border-radius: 50px;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: background .2s;
        }

        .search-btn:hover {
            background: var(--brand-dark);
        }

        /* Sugerencias */
        .suggestions-box {
            position: absolute;
            top: calc(100% + .4rem);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(26, 18, 8, .1);
            z-index: 50;
            display: none;
            overflow: hidden;
        }

        .suggestion-item {
            padding: .65rem 1.25rem;
            cursor: pointer;
            font-size: .9rem;
            color: var(--ink);
            transition: background .15s;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .suggestion-item:hover {
            background: var(--cream);
        }

        .suggestion-item .sug-icon {
            font-size: .8rem;
            color: var(--muted);
        }

        /* ── Filtros de etiquetas ── */
        .filtros-wrap {
            max-width: 860px;
            margin: 1.25rem auto 0;
            padding: 0 1rem;
        }

        .filtros-titulo {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .5rem;
        }

        .filtros-grupo {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: .75rem;
        }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .35rem .85rem;
            border-radius: 50px;
            border: 1.5px solid var(--border);
            background: #fff;
            font-size: .8rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--muted);
            cursor: pointer;
            transition: all .2s;
            user-select: none;
        }

        .tag-chip:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        .tag-chip.active-dieta {
            background: #fff7ed;
            border-color: var(--brand);
            color: var(--brand);
            font-weight: 500;
        }

        .tag-chip.active-alergeno {
            background: #fef2f2;
            border-color: #dc2626;
            color: #dc2626;
            font-weight: 500;
        }

        .filtros-clear {
            font-size: .8rem;
            color: var(--muted);
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
            display: none;
        }

        .filtros-clear:hover {
            color: var(--brand);
        }

        /* Resultados */
        .results-header {
            max-width: 1100px;
            margin: 1.5rem auto 0;
            padding: 0 2rem;
            font-size: .9rem;
            color: var(--muted);
        }

        .results-header strong {
            color: var(--ink);
        }

        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--muted);
            grid-column: 1 / -1;
        }

        .no-results-icon {
            font-size: 2.5rem;
            margin-bottom: .75rem;
            display: block;
        }

        /* Chips de etiquetas en tarjetas */
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

    <div class="search-hero">
        <h2>¿Qué quieres cocinar hoy?</h2>
        <div class="search-wrap">
            <input type="text" class="search-input" id="searchInput"
                placeholder="Busca por nombre o ingrediente…" autocomplete="off">
            <button class="search-btn" id="searchBtn" title="Buscar">🔍</button>
            <div class="suggestions-box" id="suggestionsBox"></div>
        </div>

        <!-- Filtros de etiquetas -->
        <div class="filtros-wrap">
            <?php if (!empty($dietas)): ?>
                <div class="filtros-titulo">🥗 Dieta</div>
                <div class="filtros-grupo" id="grupoDieta">
                    <?php foreach ($dietas as $e): ?>
                        <span class="tag-chip" data-id="<?= $e['id'] ?>" data-tipo="dieta"
                            onclick="toggleTag(this)">
                            <?= htmlspecialchars($e['nombre']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($alergenos)): ?>
                <div class="filtros-titulo">⚠️ Alérgenos</div>
                <div class="filtros-grupo" id="grupoAlergeno">
                    <?php foreach ($alergenos as $e): ?>
                        <span class="tag-chip" data-id="<?= $e['id'] ?>" data-tipo="alergeno"
                            onclick="toggleTag(this)">
                            <?= htmlspecialchars($e['nombre']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button class="filtros-clear" id="btnLimpiarFiltros" onclick="limpiarFiltros()">
                × Limpiar filtros
            </button>
        </div>
    </div>

    <div class="results-header" id="resultsHeader" style="display:none"></div>

    <div class="recipes-grid" id="resultsGrid" style="margin-top:1rem;">
        <div class="empty-state" id="initialState">
            <span class="empty-state-icon">🔍</span>
            <h3>Escribe algo para buscar</h3>
            <p>Prueba con "pollo", "pasta" o filtra por etiquetas</p>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const suggestionsBox = document.getElementById('suggestionsBox');
        const resultsGrid = document.getElementById('resultsGrid');
        const resultsHeader = document.getElementById('resultsHeader');
        const initialState = document.getElementById('initialState');
        const btnLimpiar = document.getElementById('btnLimpiarFiltros');

        let debounceTimer = null;
        let etiquetasActivas = new Set(); // ids seleccionados

        // ── Toggle etiqueta ──────────────────────────────────────
        function toggleTag(chip) {
            const id = chip.dataset.id;
            const tipo = chip.dataset.tipo;
            if (etiquetasActivas.has(id)) {
                etiquetasActivas.delete(id);
                chip.classList.remove(`active-${tipo}`);
            } else {
                etiquetasActivas.add(id);
                chip.classList.add(`active-${tipo}`);
            }
            btnLimpiar.style.display = etiquetasActivas.size ? 'inline' : 'none';
            buscar();
        }

        function limpiarFiltros() {
            etiquetasActivas.clear();
            document.querySelectorAll('.tag-chip').forEach(c =>
                c.classList.remove('active-dieta', 'active-alergeno'));
            btnLimpiar.style.display = 'none';
            buscar();
        }

        // ── Sugerencias ───────────────────────────────────────────
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim();
            clearTimeout(debounceTimer);
            if (q.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(() => cargarSugerencias(q), 250);
        });

        async function cargarSugerencias(q) {
            try {
                const res = await fetch(`../api/recetas/sugerir-ingredientes.php?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (!data.length) {
                    suggestionsBox.style.display = 'none';
                    return;
                }
                suggestionsBox.innerHTML = data.map(s =>
                    `<div class="suggestion-item" onclick="usarSugerencia('${s.replace(/'/g,"\\'")}')">
                        <span class="sug-icon">🥕</span> ${s}
                     </div>`
                ).join('');
                suggestionsBox.style.display = 'block';
            } catch {
                suggestionsBox.style.display = 'none';
            }
        }

        function usarSugerencia(nombre) {
            searchInput.value = nombre;
            suggestionsBox.style.display = 'none';
            buscar();
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('.search-wrap')) suggestionsBox.style.display = 'none';
        });

        searchInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                suggestionsBox.style.display = 'none';
                buscar();
            }
        });
        searchBtn.addEventListener('click', () => {
            suggestionsBox.style.display = 'none';
            buscar();
        });

        // ── Búsqueda principal ────────────────────────────────────
        async function buscar() {
            const q = searchInput.value.trim();
            if (!q && etiquetasActivas.size === 0) {
                resultsGrid.innerHTML = `<div class="empty-state" id="initialState">
                    <span class="empty-state-icon">🔍</span>
                    <h3>Escribe algo para buscar</h3>
                    <p>Prueba con "pollo", "pasta" o filtra por etiquetas</p>
                </div>`;
                resultsHeader.style.display = 'none';
                return;
            }

            initialState && (initialState.style.display = 'none');
            resultsGrid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted);">
                    <span class="spinner" style="border-color:rgba(26,18,8,.2);border-top-color:var(--brand);width:28px;height:28px;"></span>
                    <p style="margin-top:1rem;">Buscando...</p>
                </div>`;
            resultsHeader.style.display = 'none';

            try {
                // Construir URL con etiquetas
                let url = `../api/recetas/buscar.php?q=${encodeURIComponent(q)}`;
                etiquetasActivas.forEach(id => url += `&etiquetas[]=${id}`);

                const res = await fetch(url);
                const recetas = await res.json();

                // Header de resultados
                let headerTxt = '';
                if (q) headerTxt += `Resultados para <strong>"${q}"</strong>`;
                if (etiquetasActivas.size) {
                    headerTxt += (q ? ' · ' : '') + `${etiquetasActivas.size} filtro${etiquetasActivas.size > 1 ? 's' : ''} activo${etiquetasActivas.size > 1 ? 's' : ''}`;
                }
                headerTxt += `: ${recetas.length} receta${recetas.length !== 1 ? 's' : ''}`;
                resultsHeader.innerHTML = headerTxt;
                resultsHeader.style.display = 'block';

                if (!recetas.length) {
                    resultsGrid.innerHTML = `
                        <div class="no-results">
                            <span class="no-results-icon">😔</span>
                            <h3>Sin resultados</h3>
                            <p>Prueba con otro término o ajusta los filtros.</p>
                        </div>`;
                    return;
                }

                resultsGrid.innerHTML = recetas.map(r => {
                    const tags = (r.etiquetas || []).map(t =>
                        `<span class="card-tag card-tag-${t.tipo}">${t.tipo === 'dieta' ? '🥗' : '⚠️'} ${t.nombre}</span>`
                    ).join('');

                    return `
                        <a href="detalle.php?id=${r.id}" class="recipe-card">
                            ${r.imagen_ruta
                                ? `<img src="../${r.imagen_ruta}" alt="${r.titulo}" class="recipe-card-img">`
                                : `<div class="recipe-card-placeholder">🍳</div>`}
                            ${tags ? `<div class="card-tags">${tags}</div>` : ''}
                            <div class="recipe-card-body">
                                <div class="recipe-card-title">${r.titulo}</div>
                                <div class="recipe-card-meta">
                                    <span>👤 ${r.autor}</span>
                                    <span class="recipe-card-time">⏱ ${r.tiempo_min} min</span>
                                </div>
                            </div>
                        </a>`;
                }).join('');

            } catch {
                resultsGrid.innerHTML = `
                    <div class="no-results">
                        <span class="no-results-icon">⚠️</span>
                        <h3>Error de conexión</h3>
                        <p>No se pudo realizar la búsqueda. Intenta de nuevo.</p>
                    </div>`;
            }
        }

        // Si viene ?q= en la URL
        const params = new URLSearchParams(window.location.search);
        if (params.get('q')) {
            searchInput.value = params.get('q');
            buscar();
        }
    </script>

</body>

</html>