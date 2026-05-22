<?php
// ============================================================
//  SmartChef — API: Buscador de recetas
//  Archivo: api/recetas/buscar.php
//  Método:  GET
//  Params:  q (texto), etiquetas[] (ids)
// ============================================================

require_once '../../includes/db.php';

header('Content-Type: application/json');

$q          = trim($_GET['q'] ?? '');
$etiquetas  = $_GET['etiquetas'] ?? [];

// Limpiar ids de etiquetas
$etiquetas = array_map('intval', (array)$etiquetas);
$etiquetas = array_filter($etiquetas, fn($e) => $e > 0);

if (empty($q) && empty($etiquetas)) {
    echo json_encode([]);
    exit;
}

// ── Construir query dinámico ──────────────────────────────────
$where  = [];
$params = [];

if (!empty($q) && strlen($q) >= 2) {
    $termino  = '%' . $q . '%';
    $where[]  = '(r.titulo LIKE ? OR i.nombre LIKE ?)';
    $params[] = $termino;
    $params[] = $termino;
}

if (!empty($etiquetas)) {
    $placeholders = implode(',', array_fill(0, count($etiquetas), '?'));
    $where[] = "r.id IN (
        SELECT receta_id FROM receta_etiquetas
        WHERE etiqueta_id IN ($placeholders)
        GROUP BY receta_id
        HAVING COUNT(DISTINCT etiqueta_id) = " . count($etiquetas) . "
    )";
    foreach ($etiquetas as $eid) $params[] = $eid;
}

$whereSQL = implode(' AND ', $where);

$sql = "SELECT DISTINCT r.id, r.titulo, r.tiempo_min, r.imagen_ruta, u.nombre AS autor
        FROM recetas r
        JOIN usuarios u ON r.usuario_id = u.id
        LEFT JOIN ingredientes i ON i.receta_id = r.id
        WHERE $whereSQL
        ORDER BY r.titulo ASC
        LIMIT 30";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recetas = $stmt->fetchAll();

// Agregar etiquetas a cada receta para mostrarlas en las tarjetas
if (!empty($recetas)) {
    $ids = implode(',', array_column($recetas, 'id'));
    $tagStmt = $pdo->query(
        "SELECT re.receta_id, e.nombre, e.tipo
         FROM receta_etiquetas re
         JOIN etiquetas e ON e.id = re.etiqueta_id
         WHERE re.receta_id IN ($ids)"
    );
    $tags = $tagStmt->fetchAll();

    // Indexar por receta_id
    $tagMap = [];
    foreach ($tags as $t) $tagMap[$t['receta_id']][] = $t;

    foreach ($recetas as &$r) {
        $r['etiquetas'] = $tagMap[$r['id']] ?? [];
    }
}

echo json_encode($recetas);
