<?php
// ============================================================
//  SmartChef — API: Crear receta
//  Archivo: api/recetas/crear.php
// ============================================================

require_once '../../includes/db.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido.']));
}

$titulo       = trim($_POST['titulo']      ?? '');
$pasos        = trim($_POST['pasos']       ?? '');
$tiempo_min   = (int)($_POST['tiempo_min'] ?? 0);
$ingredientes = $_POST['ingredientes']     ?? [];
$etiquetas    = $_POST['etiquetas']        ?? [];

$errores = [];
if (empty($titulo))       $errores[] = 'El título es obligatorio.';
if (empty($pasos))        $errores[] = 'Los pasos de preparación son obligatorios.';
if ($tiempo_min <= 0)     $errores[] = 'El tiempo de preparación debe ser mayor a 0.';
if (empty($ingredientes)) $errores[] = 'Debes agregar al menos un ingrediente.';

foreach ($ingredientes as $ing) {
    if (empty(trim($ing['nombre'] ?? ''))) {
        $errores[] = 'Todos los ingredientes deben tener nombre.';
        break;
    }
}

if (!empty($errores)) {
    http_response_code(422);
    die(json_encode(['errores' => $errores]));
}

try {
    $pdo->beginTransaction();

    // 1. Insertar receta
    $stmt = $pdo->prepare(
        'INSERT INTO recetas (usuario_id, titulo, pasos, tiempo_min) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([usuarioActual(), $titulo, $pasos, $tiempo_min]);
    $receta_id = $pdo->lastInsertId();

    // 2. Insertar ingredientes
    $stmtIng = $pdo->prepare(
        'INSERT INTO ingredientes (receta_id, nombre, cantidad) VALUES (?, ?, ?)'
    );
    foreach ($ingredientes as $ing) {
        $nombre   = trim($ing['nombre']   ?? '');
        $cantidad = trim($ing['cantidad'] ?? '');
        if ($nombre !== '') $stmtIng->execute([$receta_id, $nombre, $cantidad]);
    }

    // 3. ✅ Insertar etiquetas
    if (!empty($etiquetas)) {
        $stmtTag = $pdo->prepare(
            'INSERT IGNORE INTO receta_etiquetas (receta_id, etiqueta_id) VALUES (?, ?)'
        );
        foreach ($etiquetas as $eid) {
            $eid = (int)$eid;
            if ($eid > 0) $stmtTag->execute([$receta_id, $eid]);
        }
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode(['mensaje' => 'Receta creada exitosamente.', 'receta_id' => $receta_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la receta. Intenta de nuevo.']);
}
