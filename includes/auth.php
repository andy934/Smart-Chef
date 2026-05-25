<?php
// ============================================================
//  SmartChef — Funciones de autenticación y autorización
//  Archivo: includes/auth.php
//  Usar con: require_once '../includes/auth.php';
// ============================================================

// Inicia la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── requireLogin() ────────────────────────────────────────────
// Llama esta función al inicio de cualquier página o endpoint
// que requiera sesión activa. Si no hay sesión, redirige al login.
function requireLogin(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        die(json_encode(['error' => 'Debes iniciar sesión para realizar esta acción.']));
    }
}

// ── requireLoginPage() ────────────────────────────────────────
// Igual que requireLogin() pero para páginas HTML (no APIs).
// Redirige al login en lugar de devolver JSON.
function requireLoginPage(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /smartchef/pages/login.php');
        exit;
    }
}

// ── usuarioActual() ───────────────────────────────────────────
// Devuelve el ID del usuario autenticado.
function usuarioActual(): int
{
    return (int) ($_SESSION['usuario_id'] ?? 0);
}

// ── checkOwner() ─────────────────────────────────────────────
// Verifica que la receta con $receta_id pertenezca al usuario
// con $usuario_id. Si no, termina la ejecución con error 403.

function checkOwner(PDO $pdo, int $receta_id, int $usuario_id): void
{
    $stmt = $pdo->prepare('SELECT usuario_id FROM recetas WHERE id = ?');
    $stmt->execute([$receta_id]);
    $receta = $stmt->fetch();

    if (!$receta) {
        http_response_code(404);
        die(json_encode(['error' => 'Receta no encontrada.']));
    }

    if ((int)$receta['usuario_id'] !== $usuario_id) {
        http_response_code(403);
        die(json_encode(['error' => 'No tienes permiso para modificar esta receta.']));
    }
}
