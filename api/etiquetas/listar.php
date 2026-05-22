<?php
// ============================================================
//  SmartChef — API: Listar etiquetas
//  Archivo: api/etiquetas/listar.php
//  Método:  GET
// ============================================================

require_once '../../includes/db.php';

header('Content-Type: application/json');

$stmt = $pdo->query('SELECT id, nombre, tipo FROM etiquetas ORDER BY tipo, nombre');
echo json_encode($stmt->fetchAll());
