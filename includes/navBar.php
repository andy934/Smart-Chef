<?php
// ============================================================
//  SmartChef — Navbar compartido
//  Archivo: includes/navbar.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
$logueado      = !empty($_SESSION['usuario_id']);
$nombreUsuario = $logueado ? htmlspecialchars($_SESSION['usuario_nombre']) : '';
$paginaActual  = basename($_SERVER['PHP_SELF']);

function navLink($href, $label, $actual)
{
    $activo = $actual === $href
        ? 'color:var(--brand); font-weight:500;'
        : 'color:var(--muted);';
    return "<a href=\"{$href}\" style=\"font-size:.9rem; text-decoration:none; {$activo}\">{$label}</a>";
}
?>
<nav class="navbar d-flex justify-content-between align-items-center" style="flex-wrap:wrap; gap:.75rem;">

    <a class="navbar-brand" href="<?= $logueado ? 'dashboard.php' : 'explorar.php' ?>">
        🍳 SmartChef
    </a>

    <!-- Búsqueda rápida en navbar -->
    <?php if (basename($_SERVER['PHP_SELF']) !== 'buscar.php') { ?>
        <form action="buscar.php" method="GET"
            style="display:flex; align-items:center; gap:.4rem; flex:1; max-width:360px;">
            <input type="text" name="q" placeholder="Buscar recetas…"
                style="flex:1; border:1.5px solid var(--border); border-radius:50px;
                  padding:.4rem 1rem; font-family:'DM Sans',sans-serif; font-size:.85rem;
                  color:var(--ink); background:var(--cream); outline:none;
                  transition:border-color .2s;"
                onfocus="this.style.borderColor='var(--brand)'"
                onblur="this.style.borderColor='var(--border)'">
            <button type="submit"
                style="background:var(--brand); border:none; border-radius:50px;
                   width:32px; height:32px; cursor:pointer; font-size:.85rem;
                   display:flex; align-items:center; justify-content:center;">
                🔍
            </button>
        </form>
    <?php } ?>

    <div class="d-flex align-items-center gap-3">
        <?= navLink('explorar.php', 'Explorar', $paginaActual) ?>

        <?php if ($logueado): ?>
            <?= navLink('mis-recetas.php', 'Mis recetas', $paginaActual) ?>
            <span style="font-size:.9rem; color:var(--muted)">
                Hola, <strong><?= $nombreUsuario ?></strong>
            </span>
            <a href="../api/auth/logout.php" class="btn-logout">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php" style="font-size:.9rem; text-decoration:none; color:var(--muted)">Iniciar sesión</a>
            <a href="register.php" class="btn-brand" style="padding:.4rem 1rem; font-size:.85rem;">Registrarse</a>
        <?php endif; ?>
    </div>

</nav>