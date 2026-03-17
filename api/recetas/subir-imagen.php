<?php
// ============================================================
//  SmartChef — API: Subir imagen de receta
//  Archivo: api/recetas/subir-imagen.php
//  Método:  POST
//  Body:    receta_id, imagen (file)
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

// Verificar que se envió un archivo
if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(422);
    die(json_encode(['error' => 'No se recibió ninguna imagen.']));
}

$archivo = $_FILES['imagen'];

// Verificar errores de subida
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    http_response_code(422);
    die(json_encode(['error' => 'Error al subir el archivo.']));
}

// ── Validaciones de archivo ───────────────────────────────────
$maxTamano  = 2 * 1024 * 1024; // 2 MB
$formatosOk = ['image/jpeg', 'image/png', 'image/webp'];

if ($archivo['size'] > $maxTamano) {
    http_response_code(422);
    die(json_encode(['error' => 'La imagen no debe superar 2 MB.']));
}

// Verificar tipo MIME real (no confiar solo en la extensión)
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeReal = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeReal, $formatosOk)) {
    http_response_code(422);
    die(json_encode(['error' => 'Formato no válido. Solo se aceptan JPG, PNG o WEBP.']));
}

// ── Eliminar imagen anterior si existe ────────────────────────
$stmt = $pdo->prepare('SELECT imagen_ruta FROM recetas WHERE id = ?');
$stmt->execute([$receta_id]);
$receta = $stmt->fetch();

if (!empty($receta['imagen_ruta'])) {
    $rutaAnterior = __DIR__ . '/../../' . $receta['imagen_ruta'];
    if (file_exists($rutaAnterior)) unlink($rutaAnterior);
}

// ── Generar nombre único y mover archivo ─────────────────────
$extension  = $mimeReal === 'image/png' ? 'png' : ($mimeReal === 'image/webp' ? 'webp' : 'jpg');
$nombreFile = 'receta_' . $receta_id . '_' . time() . '.' . $extension;
$carpeta    = __DIR__ . '/../../uploads/recetas/';
$rutaFinal  = $carpeta . $nombreFile;

if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
    http_response_code(500);
    die(json_encode(['error' => 'No se pudo guardar la imagen en el servidor.']));
}

// ── Guardar ruta en BD ────────────────────────────────────────
$rutaBD = 'uploads/recetas/' . $nombreFile;
$pdo->prepare('UPDATE recetas SET imagen_ruta = ? WHERE id = ?')
    ->execute([$rutaBD, $receta_id]);

echo json_encode([
    'mensaje'     => 'Imagen subida exitosamente.',
    'imagen_ruta' => $rutaBD
]);
