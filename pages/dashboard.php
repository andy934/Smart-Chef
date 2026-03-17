<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartChef — Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #E85D2F;
            --brand-dark: #C44A1F;
            --cream: #FDF8F3;
            --ink: #1A1208;
            --muted: #8C7B6B;
            --border: #EAE0D5;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--cream);
            color: var(--ink);
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .navbar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--ink) !important;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .nav-user {
            font-size: .9rem;
            color: var(--muted);
        }

        .btn-logout {
            background: none;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: .4rem .9rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            color: var(--muted);
            cursor: pointer;
            transition: border-color .2s, color .2s;
        }

        .btn-logout:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        /* ── Hero bienvenida ── */
        .hero {
            padding: 4rem 2rem 2rem;
            text-align: center;
        }

        .hero h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--ink);
            margin-bottom: .5rem;
        }

        .hero p {
            color: var(--muted);
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .btn-brand {
            display: inline-block;
            padding: .8rem 2rem;
            background: var(--brand);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-brand:hover {
            background: var(--brand-dark);
            color: #fff;
        }

        /* ── Cards de acceso rápido ── */
        .quick-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .qcard {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            text-align: center;
            text-decoration: none;
            color: var(--ink);
            transition: box-shadow .2s, transform .2s;
        }

        .qcard:hover {
            box-shadow: 0 6px 24px rgba(26, 18, 8, .09);
            transform: translateY(-2px);
            color: var(--ink);
        }

        .qcard-icon {
            font-size: 2rem;
            margin-bottom: .75rem;
            display: block;
        }

        .qcard h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            margin-bottom: .3rem;
        }

        .qcard p {
            font-size: .83rem;
            color: var(--muted);
        }

        /* ── Badge de sprint ── */
        .sprint-badge {
            display: inline-block;
            background: #fff3ee;
            color: var(--brand);
            border: 1px solid #fdd5c4;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 500;
            padding: .2rem .75rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <?php
    // Proteger esta página: redirige al login si no hay sesión
    require_once '../includes/auth.php';
    requireLoginPage();
    $nombre = htmlspecialchars($_SESSION['usuario_nombre']);
    ?>

    <!-- Navbar -->
    <nav class="navbar d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="dashboard.php">
            🍳 SmartChef
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="nav-user">Hola, <strong><?= $nombre ?></strong></span>
            <a href="../api/auth/logout.php" class="btn-logout">Cerrar sesión</a>
        </div>
    </nav>

    <!-- Hero -->
    <div class="hero">
        <span class="sprint-badge">Sprint 1 completado ✓</span>
        <h2>¡Bienvenido, <?= $nombre ?>!</h2>
        <p>¿Qué quieres hacer hoy?</p>
        <a href="#" class="btn-brand">+ Nueva receta</a>
    </div>

    <!-- Accesos rápidos -->
    <div class="quick-cards">
        <a href="#" class="qcard">
            <span class="qcard-icon">📖</span>
            <h3>Mis recetas</h3>
            <p>Administra las recetas que has creado</p>
        </a>
        <a href="#" class="qcard">
            <span class="qcard-icon">🔍</span>
            <h3>Explorar</h3>
            <p>Descubre recetas de otros usuarios</p>
        </a>
        <a href="#" class="qcard">
            <span class="qcard-icon">🔖</span>
            <h3>Guardadas</h3>
            <p>Recetas que guardaste para después</p>
        </a>
    </div>

</body>

</html>