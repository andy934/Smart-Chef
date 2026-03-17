<?php
// ============================================================
//  SmartChef — API: Eliminar imagen de receta
//  Archivo: api/recetas/eliminar-imagen.php
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

// Verificar propiedad
checkOwner($pdo, $receta_id, usuarioActual());

// Obtener ruta actual
$stmt = $pdo->prepare('SELECT imagen_ruta FROM recetas WHERE id = ?');
$stmt->execute([$receta_id]);
$receta = $stmt->fetch();

if (empty($receta['imagen_ruta'])) {
    http_response_code(422);
    die(json_encode(['error' => 'Esta receta no tiene imagen.']));
}

// Eliminar archivo físico
$rutaFisica = __DIR__ . '/../../' . $receta['imagen_ruta'];
if (file_exists($rutaFisica)) unlink($rutaFisica);

// Limpiar campo en BD
$pdo->prepare('UPDATE recetas SET imagen_ruta = NULL WHERE id = ?')
    ->execute([$receta_id]);

echo json_encode(['mensaje' => 'Imagen eliminada exitosamente.']);
