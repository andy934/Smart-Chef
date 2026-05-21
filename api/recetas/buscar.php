<?php
// ============================================================
//  SmartChef — API: Buscador de recetas
//  Archivo: api/recetas/buscar.php
//  Método:  GET
//  Params:  q (texto a buscar)
// ============================================================

require_once '../../includes/db.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (empty($q) || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$termino = '%' . $q . '%';

// Busca por título de receta O por nombre de ingrediente
// DISTINCT evita duplicados cuando una receta tiene varios ingredientes que coinciden
$stmt = $pdo->prepare(
    'SELECT DISTINCT r.id, r.titulo, r.tiempo_min, r.imagen_ruta, u.nombre AS autor
     FROM recetas r
     JOIN usuarios u ON r.usuario_id = u.id
     LEFT JOIN ingredientes i ON i.receta_id = r.id
     WHERE r.titulo LIKE ?
        OR i.nombre LIKE ?
     ORDER BY r.titulo ASC
     LIMIT 30'
);
$stmt->execute([$termino, $termino]);
$recetas = $stmt->fetchAll();

echo json_encode($recetas);
