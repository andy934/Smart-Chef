<?php
// ============================================================
//  SmartChef — Navbar compartido
//  Archivo: includes/navbar.php
//  Usar con: require_once '../includes/navbar.php';
//  Requiere que auth.php ya haya sido incluido si la página
//  necesita sesión, o que session_start() esté activo.
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
$logueado     = !empty($_SESSION['usuario_id']);
$nombreUsuario = $logueado ? htmlspecialchars($_SESSION['usuario_nombre']) : '';
$paginaActual  = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar d-flex justify-content-between align-items-center">

    <a class="navbar-brand" href="<?= $logueado ? 'dashboard.php' : 'explorar.php' ?>">
        🍳 SmartChef
    </a>

    <div class="d-flex align-items-center gap-3">
        <a href="explorar.php"
            style="font-size:.9rem; text-decoration:none; color:<?= $paginaActual === 'explorar.php' ? 'var(--brand)' : 'var(--muted)' ?>">
            Explorar
        </a>

        <?php if ($logueado): ?>
            <a href="mis-recetas.php"
                style="font-size:.9rem; text-decoration:none; color:<?= $paginaActual === 'mis-recetas.php' ? 'var(--brand)' : 'var(--muted)' ?>">
                Mis recetas
            </a>
            <span style="font-size:.9rem; color:var(--muted)">
                Hola, <strong><?= $nombreUsuario ?></strong>
            </span>
            <a href="../api/auth/logout.php" class="btn-logout">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php"
                style="font-size:.9rem; text-decoration:none; color:var(--muted)">
                Iniciar sesión
            </a>
            <a href="register.php" class="btn-brand" style="padding:.4rem 1rem; font-size:.85rem;">
                Registrarse
            </a>
        <?php endif; ?>
    </div>

</nav>