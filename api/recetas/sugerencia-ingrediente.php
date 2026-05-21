<?php
// ============================================================
//  SmartChef — API: Sugerencias de ingredientes (autocompletado)
//  Archivo: api/recetas/sugerir-ingredientes.php
//  Método:  GET
//  Params:  q (texto parcial del ingrediente)
// ============================================================

require_once '../../includes/db.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (empty($q) || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Devuelve los nombres de ingredientes únicos que coincidan
$stmt = $pdo->prepare(
    'SELECT DISTINCT nombre
     FROM ingredientes
     WHERE nombre LIKE ?
     ORDER BY nombre ASC
     LIMIT 8'
);
$stmt->execute(['%' . $q . '%']);

$sugerencias = array_column($stmt->fetchAll(), 'nombre');
echo json_encode($sugerencias);
