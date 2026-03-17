<?php
// ============================================================
//  SmartChef — API: Eliminar receta
//  Archivo: api/recetas/eliminar.php
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

$receta_id = (int)($_POST['receta_id'] ?? 0);

if ($receta_id <= 0) {
    http_response_code(422);
    die(json_encode(['error' => 'Receta no válida.']));
}

// ── Verificar propiedad antes de eliminar (RF10) ──────────────
checkOwner($pdo, $receta_id, usuarioActual());

// ── Eliminar imagen del servidor si existe ────────────────────
$stmt = $pdo->prepare('SELECT imagen_ruta FROM recetas WHERE id = ?');
$stmt->execute([$receta_id]);
$receta = $stmt->fetch();

if ($receta && !empty($receta['imagen_ruta'])) {
    $ruta = __DIR__ . '/../../' . $receta['imagen_ruta'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

// ── Eliminar receta (ingredientes se borran por CASCADE) ──────
$pdo->prepare('DELETE FROM recetas WHERE id = ?')->execute([$receta_id]);

echo json_encode(['mensaje' => 'Receta eliminada exitosamente.']);
