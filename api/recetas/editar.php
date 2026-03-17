<?php
// ============================================================
//  SmartChef — API: Editar receta
//  Archivo: api/recetas/editar.php
//  Método:  POST
//  Body:    receta_id, titulo, pasos, tiempo_min, ingredientes[]
// ============================================================

require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido.']));
}

$receta_id  = (int)($_POST['receta_id']  ?? 0);
$titulo     = trim($_POST['titulo']      ?? '');
$pasos      = trim($_POST['pasos']       ?? '');
$tiempo_min = (int)($_POST['tiempo_min'] ?? 0);
$ingredientes = $_POST['ingredientes']   ?? [];

// ── Validaciones ─────────────────────────────────────────────
$errores = [];

if ($receta_id <= 0)      $errores[] = 'Receta no válida.';
if (empty($titulo))       $errores[] = 'El título es obligatorio.';
if (empty($pasos))        $errores[] = 'Los pasos de preparación son obligatorios.';
if ($tiempo_min <= 0)     $errores[] = 'El tiempo de preparación debe ser mayor a 0.';
if (empty($ingredientes)) $errores[] = 'Debes agregar al menos un ingrediente.';

if (!empty($errores)) {
    http_response_code(422);
    die(json_encode(['errores' => $errores]));
}

// ── Verificar propiedad antes de editar (RF10) ────────────────
checkOwner($pdo, $receta_id, usuarioActual());

// ── Actualizar receta e ingredientes en transacción ──────────
try {
    $pdo->beginTransaction();

    // 1. Actualizar datos de la receta
    $stmt = $pdo->prepare(
        'UPDATE recetas SET titulo = ?, pasos = ?, tiempo_min = ?
         WHERE id = ?'
    );
    $stmt->execute([$titulo, $pasos, $tiempo_min, $receta_id]);

    // 2. Borrar ingredientes anteriores y reinsertar
    //    Es más simple que hacer diff y más seguro
    $pdo->prepare('DELETE FROM ingredientes WHERE receta_id = ?')
        ->execute([$receta_id]);

    $stmtIng = $pdo->prepare(
        'INSERT INTO ingredientes (receta_id, nombre, cantidad) VALUES (?, ?, ?)'
    );
    foreach ($ingredientes as $ing) {
        $nombre   = trim($ing['nombre']   ?? '');
        $cantidad = trim($ing['cantidad'] ?? '');
        if ($nombre !== '') {
            $stmtIng->execute([$receta_id, $nombre, $cantidad]);
        }
    }

    $pdo->commit();

    echo json_encode(['mensaje' => 'Receta actualizada exitosamente.']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la receta. Intenta de nuevo.']);
}
