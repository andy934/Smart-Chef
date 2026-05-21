<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
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

        /* Dropdown de sugerencias */
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

        /* Estado vacío de búsqueda */
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
    </div>

    <div class="results-header" id="resultsHeader" style="display:none"></div>

    <div class="recipes-grid" id="resultsGrid" style="margin-top:1rem;">
        <!-- Estado inicial -->
        <div class="empty-state" id="initialState">
            <span class="empty-state-icon">🔍</span>
            <h3>Escribe algo para buscar</h3>
            <p>Prueba con "pollo", "pasta" o el nombre de una receta</p>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const suggestionsBox = document.getElementById('suggestionsBox');
        const resultsGrid = document.getElementById('resultsGrid');
        const resultsHeader = document.getElementById('resultsHeader');
        const initialState = document.getElementById('initialState');

        let debounceTimer = null;

        // ── Sugerencias de ingredientes mientras escribe ────────────
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
            buscar(nombre);
        }

        // Cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', e => {
            if (!e.target.closest('.search-wrap')) {
                suggestionsBox.style.display = 'none';
            }
        });

        // ── Buscar al presionar Enter o el botón ───────────────────
        searchInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                suggestionsBox.style.display = 'none';
                buscar(searchInput.value.trim());
            }
        });

        searchBtn.addEventListener('click', () => {
            suggestionsBox.style.display = 'none';
            buscar(searchInput.value.trim());
        });

        // ── Función principal de búsqueda ──────────────────────────
        async function buscar(q) {
            if (!q) return;

            // Mostrar spinner
            initialState.style.display = 'none';
            resultsGrid.innerHTML = `
      <div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--muted);">
        <span class="spinner" style="border-color:rgba(26,18,8,.2); border-top-color:var(--brand); width:28px; height:28px;"></span>
        <p style="margin-top:1rem;">Buscando...</p>
      </div>`;
            resultsHeader.style.display = 'none';

            try {
                const res = await fetch(`../api/recetas/buscar.php?q=${encodeURIComponent(q)}`);
                const recetas = await res.json();

                // Header con conteo
                resultsHeader.innerHTML =
                    `Resultados para <strong>"${q}"</strong>: ${recetas.length} receta${recetas.length !== 1 ? 's' : ''}`;
                resultsHeader.style.display = 'block';

                if (!recetas.length) {
                    resultsGrid.innerHTML = `
          <div class="no-results">
            <span class="no-results-icon">😔</span>
            <h3>Sin resultados</h3>
            <p>No encontramos recetas con "${q}". Prueba con otro término.</p>
          </div>`;
                    return;
                }

                // Renderizar tarjetas
                resultsGrid.innerHTML = recetas.map(r => `
        <a href="detalle.php?id=${r.id}" class="recipe-card">
          ${r.imagen_ruta
            ? `<img src="../${r.imagen_ruta}" alt="${r.titulo}" class="recipe-card-img">`
            : `<div class="recipe-card-placeholder">🍳</div>`}
          <div class="recipe-card-body">
            <div class="recipe-card-title">${r.titulo}</div>
            <div class="recipe-card-meta">
              <span>👤 ${r.autor}</span>
              <span class="recipe-card-time">⏱ ${r.tiempo_min} min</span>
            </div>
          </div>
        </a>
      `).join('');

            } catch {
                resultsGrid.innerHTML = `
        <div class="no-results">
          <span class="no-results-icon">⚠️</span>
          <h3>Error de conexión</h3>
          <p>No se pudo realizar la búsqueda. Intenta de nuevo.</p>
        </div>`;
            }
        }

        // Si viene ?q= en la URL, ejecutar búsqueda automáticamente
        const params = new URLSearchParams(window.location.search);
        if (params.get('q')) {
            searchInput.value = params.get('q');
            buscar(params.get('q'));
        }
    </script>

</body>

</html>