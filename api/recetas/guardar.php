<?php
// ============================================================
//  SmartChef — API: Guardar / desguardar receta (toggle)
//  Archivo: api/recetas/guardar.php
//  Método:  POST
//  Body:    receta_id
// ============================================================

require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido.']));
}

$receta_id  = (int)($_POST['receta_id'] ?? 0);
$usuario_id = usuarioActual();

if ($receta_id <= 0) {
    http_response_code(422);
    die(json_encode(['error' => 'Receta no válida.']));
}

// Verificar que la receta existe
$stmt = $pdo->prepare('SELECT id, usuario_id FROM recetas WHERE id = ?');
$stmt->execute([$receta_id]);
$receta = $stmt->fetch();

if (!$receta) {
    http_response_code(404);
    die(json_encode(['error' => 'Receta no encontrada.']));
}

// No puedes guardar tu propia receta
if ((int)$receta['usuario_id'] === $usuario_id) {
    http_response_code(400);
    die(json_encode(['error' => 'No puedes guardar tu propia receta.']));
}

// Toggle: si ya está guardada la elimina, si no la guarda
$stmtCheck = $pdo->prepare(
    'SELECT id FROM recetas_guardadas WHERE usuario_id = ? AND receta_id = ?'
);
$stmtCheck->execute([$usuario_id, $receta_id]);
$guardada = $stmtCheck->fetch();

if ($guardada) {
    $pdo->prepare('DELETE FROM recetas_guardadas WHERE usuario_id = ? AND receta_id = ?')
        ->execute([$usuario_id, $receta_id]);
    echo json_encode(['guardada' => false, 'mensaje' => 'Receta eliminada de tu colección.']);
} else {
    $pdo->prepare('INSERT INTO recetas_guardadas (usuario_id, receta_id) VALUES (?, ?)')
        ->execute([$usuario_id, $receta_id]);
    echo json_encode(['guardada' => true, 'mensaje' => 'Receta guardada en tu colección.']);
}
